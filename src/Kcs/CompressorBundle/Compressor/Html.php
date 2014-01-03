<?php

namespace Kcs\CompressorBundle\Compressor;

use Symfony\Component\HttpFoundation\Response;

use Kcs\CompressorBundle\Event\CompressionEvents;
use Kcs\CompressorBundle\Event\CompressionEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * HtmlCompressor
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class Html implements CompressorInterface
{
    /**
     * Is HTML compressor enabled?
     * @var bool
     */
    protected $enabled = true;

    /**
     * The current event dispatcher service
     * @var EventDispatcherInterface
     */
    protected $dispatcher = null;

    public function __construct(EventDispatcherInterface $dispatcher, $enabled) {
        $this->setEventDispatcher($dispatcher);
        $this->setEnabled($enabled);
    }

    protected function setEventDispatcher(EventDispatcherInterface $dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Return the current event dispatcher interface
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher() {
        return $this->dispatcher;
    }

    protected function setEnabled($v) {
        $this->enabled = $v;
    }

    public function isEnabled() {
        return $this->enabled;
    }

    /**
     * Process an HTML page and compress it preserving critical blocks
     *
     * @param string $html The uncompressed page
     */
    public function process(Response $response) {
        // Skipped blocks should be processed before all other blocks
        $response->setContent($this->preserveSkipBlocks($response->getContent()));

        // Create the event
        $ev = new CompressionEvent($response);

        // Dispatch the pre processing phase event
        $this->getEventDispatcher()->dispatch(CompressionEvents::PRE_PROCESS, $ev);

        // Compress
        $this->getEventDispatcher()->dispatch(CompressionEvents::COMPRESS, $ev);

        // Post compression event
        $this->getEventDispatcher()->dispatch(CompressionEvents::POST_PROCESS, $ev);

        // Revert skipped blocks content
        $response->setContent($this->processPreservedSkipBlocks($response->getContent()));
    }

    // SKIP BLOCK PROCESSING
    protected $skipBlocks = array();

    /**
     * Returns the skip block regex
     */
    protected function getSkipBlockPattern() {
        return '#<!--\s*\{\{\{\s*-->(.*?)<!--\s*\}\}\}\s*-->#ui';
    }

    /**
     * Returns the skip block temp replacement format for sprintf
     */
    protected function getSkipBlockReplacementFormat() {
        return '%%%%%%~COMPRESS~SKIP~%u~%%%%%%';
    }

    /**
     * Returns the skip block replacement regex
     */
    protected function getSkipBlockReplacementPattern() {
        return '#%%%~COMPRESS~SKIP~(\d+?)~%%%#u';
    }

    /**
     * Replace the blocks with a temp replacement
     */
    public function preserveSkipBlocks($html) {
        if (preg_match_all($this->getSkipBlockPattern(), $html, $matches)) {
            foreach($matches[1] as $k => $content) {
                $this->skipBlocks[$k] = $content;
                $html = mb_ereg_replace($content, sprintf($this->getSkipBlockReplacementFormat(), $k), $html);
            }
        }
        return $html;
    }

    /**
     * Remove the temp replacement for preserved skip blocks
     */
    public function processPreservedSkipBlocks($html) {
        if (preg_match_all($this->getSkipBlockReplacementPattern(), $html, $matches)) {
            foreach($matches[0] as $k => $content) {
                $html = mb_ereg_replace($content, $this->skipBlocks[$k], $html);
            }
        }

        return $html;
    }
}