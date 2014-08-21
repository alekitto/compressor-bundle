<?php

namespace Kcs\CompressorBundle\Compressor;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Kcs\CompressorBundle\Event\CompressionEvents;
use Kcs\CompressorBundle\Event\CompressionEvent;

/**
 * <script> tag preserver and javascript processor
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class JavascriptCompressor implements EventSubscriberInterface
{
    /**
     * Config enabled value
     * @var bool
     */
    protected $enabled;

    /**
     * The js inline compressor
     * @var InlineCompressorInterface
     */
    protected $compressor;

    public function __construct(InlineCompressorInterface $compressor, $enabled)
    {
        $this->compressor = $compressor;
        $this->setEnabled($enabled);
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($v)
    {
        $this->enabled = $v;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            CompressionEvents::PRE_PROCESS => 'onPreProcess',
            CompressionEvents::COMPRESS => 'onCompress',
            CompressionEvents::POST_PROCESS => 'onPostProcess'
        );
    }

    /**
     * The <script> tag regex pattern
     */
    protected function getPattern()
    {
        return '#(<script[^>]*?>)(.*?)(</script>)#usi';
    }

    /**
     * Returns the block temp replacement format for sprintf
     */
    protected function getReplacementFormat()
    {
        return '%%%%%%~COMPRESS~SCRIPT~%u~%%%%%%';
    }

    /**
     * Returns the block replacement regex
     */
    protected function getReplacementPattern()
    {
        return '#%%%~COMPRESS~SCRIPT~(\d+?)~%%%#u';
    }

    protected $blocks = array();

    /**
     * Returns the content of the type attribute,
     * null if the type attribute is not present
     *
     * @param string $tag The script tag to be parsed
     * @return string|null
     */
    protected function getTypeAttr($tag)
    {
        if (preg_match('#type\s*=\s*(["\']*)(.+?)\1#usi', $tag, $types) === 1)
            return $types[2];
        return null;
    }

    /**
     * Returns the content of the language attribute,
     * null if the language attribute is not present
     *
     * @param string $tag The script tag to be parsed
     * @return string|null
     */
    protected function getLanguageAttr($tag)
    {
        if (preg_match('#language\s*=\s*(["\']*)(.+?)\1#usi', $tag, $langs) === 1)
            return $langs[2];
        return null;
    }

    /**
     * Returns TRUE if the tag is a javascript opening tag, FALSE otherwise
     * @param string $openingTag
     * @return bool
     */
    protected function isJavascript($openingTag)
    {
        $type = $this->getTypeAttr($openingTag);
        $lang = $this->getLanguageAttr($openingTag);
        return 
            $type === 'text/javascript' || $type === 'application/javascript' ||
            $lang === 'javascript' ||
        // If type and language attribute are not present default type is "text/javascript"
            ($type === null && $lang === null);
    }

    /**
     * Compress the javascript blocks
     */
    public function onCompress(CompressionEvent $event)
    {
        foreach($this->blocks as $k => $content) {
            // Extract the script code
            if (preg_match($this->getPattern(), $content, $matches) !== 1) {
                continue;
            }

            // Can't call compressor if not js code block
            if (!$this->isJavascript($matches[1])) {
                continue;
            }

            // Check if CDATA attribute is present
            $cdataWrapper = false;
            $script = $matches[2];
            if (preg_match('#\s*<!\[CDATA\[(?:\s*\*/)(.*?)(?:/\*\s*)\]\]>\s*#usi', $script, $cdataMatches)) {
                $script = $cdataMatches[1];
                $cdataWrapper = true;
            }

            // Call the inline compressor
            if (($script = trim($script))) {
                $script = $this->compressor->compress($script);
            }

            if ($cdataWrapper) {
                // Rewrap the compressed script into CDATA tag
                $script = "/*<![CDATA[*/" . $script . "/*]]>*/";
            }

            // Replace the block into the saved array
            $this->blocks[$k] = $matches[1] . $script . $matches[3];
        }
    }

    public function onPreProcess(CompressionEvent $event)
    {
        $html = $event->getContent();

        // Find all occourrences of block pattern on response content
        if (preg_match_all($this->getPattern(), $html, $matches)) {
            foreach($matches[0] as $k => $content) {
                $type = $this->getTypeAttr($matches[1][$k]);

                // Ignore jQuery template. Should be compressed with the rest of html.
                if ($type !== "text/x-jquery-tmpl") {
                    // Save found block
                    $this->blocks[$k] = $content;

                    // Insert replacements
                    $html = str_replace($content, sprintf($this->getReplacementFormat(), $k), $html);
                }
            }
        }

        // Set response content
        $event->setContent($html);
    }

    public function onPostProcess(CompressionEvent $event)
    {
        $html = $event->getContent();

        // Revert modifications made in pre-process phase
        if (preg_match_all($this->getReplacementPattern(), $html, $matches)) {
            foreach($matches[0] as $k => $content) {
                $html = mb_ereg_replace($content, $this->blocks[$matches[1][$k]], $html);
            }
        }

        $event->setContent($html);
    }
}
