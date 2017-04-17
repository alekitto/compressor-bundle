<?php

namespace Kcs\CompressorBundle\Preserver;

use Kcs\CompressorBundle\Event\CompressionEvent;
use Kcs\CompressorBundle\Event\CompressionEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Compression abstract preserver for generic tag
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
abstract class AbstractTagPreserver implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CompressionEvents::PRE_PROCESS => 'onPreProcess',
            CompressionEvents::POST_PROCESS => 'onPostProcess',
        ];
    }

    protected $blocks = [];
    protected $executed = false;

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

        if (!$event->isSafeToContinue()) {
            return;
        }

        if (preg_match($this->getReplacementPattern(), $html)) {
            $event->markFailed();

            return;
        }

        // Find all occourrences of block pattern on response content
        if (preg_match_all($this->getPattern(), $html, $matches)) {
            foreach ($matches[0] as $k => $content) {
                // Save found block
                $this->blocks[$k] = $matches[1][$k];

                // Insert replacements
                $html = preg_replace('/'.preg_quote($content, '/').'/usi',
                        sprintf($this->getReplacementFormat(), $k), $html);

                if ($html === null) {
                    $event->markFailed();
                    break;
                }
            }
        }

        // Set response content
        if ($html !== null) {
            $event->setContent($html);
        }
        $this->executed = true;
    }

    public function onPostProcess(CompressionEvent $event)
    {
        if (!$this->executed) {
            return;
        }

        $html = $event->getContent();

        // Revert modifications made in pre-process phase
        if (preg_match_all($this->getReplacementPattern(), $html, $matches)) {
            foreach ($matches[0] as $k => $content) {
                $html = mb_ereg_replace($content, $this->blocks[$k], $html);
                if ($html === false) {
                    $event->markFailed();
                    break;
                }
            }
        }

        if ($html !== false) {
            $event->setContent($html);
        }
        $this->executed = false;
    }
}
