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

    private $listenerSorted = false;

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

        $this->sortPostProcessListeners();

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

    private function sortPostProcessListeners() {
        if ($this->listenerSorted) return;

        // Post processing filters must be executed in the reverse order
        $dispatcher = $this->getEventDispatcher();
        $listeners = $dispatcher->getListeners(CompressionEvents::POST_PROCESS);

        foreach ($listeners as $listener) {
            $dispatcher->removeListener(CompressionEvents::POST_PROCESS, $listener);
        }
        foreach (array_reverse($listeners) as $listener) {
            $dispatcher->addListener(CompressionEvents::POST_PROCESS, $listener);
        }

        $this->listenerSorted = true;
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
                $html = preg_replace('/' . preg_quote($content, '/') . '/usi',
                    sprintf($this->getSkipBlockReplacementFormat(), $k), $html);
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
