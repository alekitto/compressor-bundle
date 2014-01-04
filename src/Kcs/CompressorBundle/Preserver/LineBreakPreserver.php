<?php

namespace Kcs\CompressorBundle\Preserver;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Kcs\CompressorBundle\Event\CompressionEvents;
use Kcs\CompressorBundle\Event\CompressionEvent;

/**
 * Compression line break preserver
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class LineBreakPreserver implements EventSubscriberInterface
{
    /**
     * Config enabled value
     * @var bool
     */
    protected $enabled;

    public function __construct($enabled) {
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
            CompressionEvents::POST_PROCESS => 'onPostProcess'
        );
    }

    protected $blocks = array();

    /**
     * Returns the block regex
     */
    protected function getPattern() {
        return '#(?:[ \t]*(\r?\n)[ \t]*)+#u';
    }

    /**
     * Returns the block temp replacement format for sprintf
     */
    protected function getReplacementFormat() {
        return '%%%%%%~COMPRESS~LB~%u~%%%%%%';
    }

    /**
     * Returns the block replacement regex
     */
    protected function getReplacementPattern() {
        return '#%%%~COMPRESS~LB~(\d+?)~%%%#u';
    }

    public function onPreProcess(CompressionEvent $event) {
        if (!$this->isEnabled()) return;
        $html = $event->getContent();

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
        if (!$this->isEnabled()) return;
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
