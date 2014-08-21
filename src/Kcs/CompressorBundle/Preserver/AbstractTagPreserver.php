<?php

namespace Kcs\CompressorBundle\Preserver;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Kcs\CompressorBundle\Event\CompressionEvents;
use Kcs\CompressorBundle\Event\CompressionEvent;

/**
 * Compression abstract preserver for generic tag
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
abstract class AbstractTagPreserver implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            CompressionEvents::PRE_PROCESS => 'onPreProcess',
            CompressionEvents::POST_PROCESS => 'onPostProcess'
        );
    }

    protected $blocks = array();

    /**
     * Returns the block regex
     */
    abstract protected function getPattern();

    /**
     * Returns the block temp replacement format for sprintf
     */
    abstract protected function getReplacementFormat();

    /**
     * Returns the block replacement regex
     */
    abstract protected function getReplacementPattern();

    public function onPreProcess(CompressionEvent $event)
    {
        $html = $event->getContent();

        // Find all occourrences of block pattern on response content
        if (preg_match_all($this->getPattern(), $html, $matches)) {
            foreach($matches[0] as $k => $content) {
                // Save found block
                $this->blocks[$k] = $matches[1][$k];

                // Insert replacements
                $html = preg_replace('/' . preg_quote($content, '/') . '/usi',
                        sprintf($this->getReplacementFormat(), $k), $html);
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
                $html = mb_ereg_replace($content, $this->blocks[$k], $html);
            }
        }

        $event->setContent($html);
    }
}
