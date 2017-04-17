<?php

namespace Kcs\CompressorBundle\Preserver;

use Kcs\CompressorBundle\Event\CompressionEvent;
use Kcs\CompressorBundle\Event\CompressionEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Compression conditional comments preserver
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class ConditionalCommentsPreserver implements EventSubscriberInterface
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
    protected function getPattern()
    {
        return '#(<!(?:--)?\[[^\]]+?]>)(.*?)(<!\[[^\]]+]-->)#usi';
    }

    /**
     * Returns the block temp replacement format for sprintf
     */
    protected function getReplacementFormat()
    {
        return '%%%%%%~COMPRESS~COND~%u~%%%%%%';
    }

    /**
     * Returns the block replacement regex
     */
    protected function getReplacementPattern()
    {
        return '#%%%~COMPRESS~COND~(\d+?)~%%%#u';
    }

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
                $html = mb_ereg_replace($content, sprintf($this->getReplacementFormat(), $k), $html);
                if ($html === false) {
                    $event->markFailed();
                    break;
                }
            }
        }

        // Set response content
        if ($html !== false) {
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
