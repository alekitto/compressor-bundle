<?php

namespace Kcs\CompressorBundle\Compressor;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Kcs\CompressorBundle\Event\CompressionEvents;
use Kcs\CompressorBundle\Event\CompressionEvent;

/**
 * <style> tag preserver and css compressor
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class CssCompressor implements EventSubscriberInterface
{
    /**
     * Config enabled value
     * @var bool
     */
    protected $enabled;

    /**
     * The css inline compressor
     * @var InlineCompressorInterface
     */
    protected $compressor;

    public function __construct(InlineCompressorInterface $compressor, $enabled) {
        $this->compressor = $compressor;
        $this->setEnabled($enabled);
    }

    public function isEnabled() {
        return $this->enabled;
    }

    public function setEnabled($v) {
        $this->enabled = $v;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents() {
        return array(
            CompressionEvents::PRE_PROCESS => 'onPreProcess',
            CompressionEvents::COMPRESS => 'onCompress',
            CompressionEvents::POST_PROCESS => 'onPostProcess'
        );
    }

    /**
     * The <style> tag regex pattern
     */
    protected function getPattern() {
        return '#(<style[^>]*?>)(.*?)(</style>)#usi';
    }

    /**
     * Returns the block temp replacement format for sprintf
     */
    protected function getReplacementFormat() {
        return '%%%%%%~COMPRESS~STYLE~%u~%%%%%%';
    }

    /**
     * Returns the block replacement regex
     */
    protected function getReplacementPattern() {
        return '#%%%~COMPRESS~STYLE~(\d+?)~%%%#u';
    }

    protected $blocks = array();

    /**
     * Returns the content of the type attribute,
     * null if the type attribute is not present
     * @return string|null
     */
    protected function getTypeAttr($tag) {
        if (preg_match('#type\s*=\s*(["\']*)(.+?)\1#usi', $tag, $types) === 1)
            return $types[2];
        return null;
    }

    /**
     * Returns TRUE if the tag is a css opening tag, FALSE otherwise
     * @param string $openingTag
     * @return bool
     */
    protected function isCss($openingTag) {
        $type = $this->getTypeAttr($openingTag);

        // HTML5 does not require the type attribute.
        // The default value is "text/css"
        return $type === 'text/css' || $type === null;
    }

    /**
     * Compress the javascript blocks
     */
    public function onCompress(CompressionEvent $event) {
        foreach($this->blocks as $k => $content) {
            // Extract the script code
            if (preg_match($this->getPattern(), $content, $matches) !== 1) {
                continue;
            }

            // Can't call compressor if not css code block
            if (!$this->isCss($matches[1])) {
                continue;
            }

            // Check if CDATA attribute is present
            $cdataWrapper = false;
            $style = $matches[2];
            if (preg_match('#\s*<!\[CDATA\[(.*?)\]\]>\s*#usi', $style, $cdataMatches)) {
                $style = $cdataMatches[1];
                $cdataWrapper = true;
            }

            // Call the inline compressor
            $style = $this->compressor->compress($style);

            if ($cdataWrapper) {
                // Rewrap the compressed script into CDATA tag
                $style = "/*<![CDATA[*/" . $style . "/*]]>*/";
            }

            // Replace the block into the saved array
            $this->blocks[$k] = $matches[1] . $style . $matches[3];
        }
    }

    public function onPreProcess(CompressionEvent $event) {
        $html = $event->getContent();

        // Find all occourrences of block pattern on response content
        if (preg_match_all($this->getPattern(), $html, $matches)) {
            foreach($matches[0] as $k => $content) {
                // Save found block
                $this->blocks[$k] = $content;

                // Insert replacements
                $html = str_replace($content, sprintf($this->getReplacementFormat(), $k), $html);
            }
        }

        // Set response content
        $event->setContent($html);
    }

    public function onPostProcess(CompressionEvent $event) {
        $html = $event->getContent();

        // Revert modifications made in pre-process phase
        if (preg_match_all($this->getReplacementPattern(), $html, $matches)) {
            foreach($matches[0] as $k => $content) {
                $html = mb_ereg_replace($content, $this->blocks[$k], $html);
            }
        }

        $event->setContent($html);
    }
}