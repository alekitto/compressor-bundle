<?php

namespace Kcs\CompressorBundle\Preserver;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Kcs\CompressorBundle\Event\CompressionEvents;
use Kcs\CompressorBundle\Event\CompressionEvent;

/**
 * Compression text area content preserver
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class TextAreaPreserver implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents() {
        return array(
            CompressionEvents::PRE_PROCESS => 'onPreProcess',
            CompressionEvents::POST_PROCESS => 'onPostProcess'
        );
    }

    protected $blocks = array();

    /**
     * Returns the block regex
     */
    protected function getPattern() {
        return '#(<textarea[^>]*?>(?:.*?)</textarea>)#usi';
    }

    /**
     * Returns the block temp replacement format for sprintf
     */
    protected function getReplacementFormat() {
        return '%%%%%%~COMPRESS~TXAREA~%u~%%%%%%';
    }

    /**
     * Returns the block replacement regex
     */
    protected function getReplacementPattern() {
        return '#%%%~COMPRESS~TXAREA~(\d+?)~%%%#u';
    }

    public function onPreProcess(CompressionEvent $event) {
        $html = $event->getOriginalContent();

        // Find all occourrences of block pattern on response content
        if (preg_match_all($this->getPattern(), $html, $matches)) {
            foreach($matches[0] as $k => $content) {
                // Save found block
                $this->blocks[$k] = $matches[1][$k];

                // Insert replacements
                $html = mb_ereg_replace($content, sprintf($this->getReplacementFormat(), $k), $html);
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
