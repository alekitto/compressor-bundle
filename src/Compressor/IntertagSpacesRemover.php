<?php

namespace Kcs\CompressorBundle\Compressor;

use Kcs\CompressorBundle\Event\CompressionEvent;
use Kcs\CompressorBundle\Event\CompressionEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Removes extra spaces between tags
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class IntertagSpacesRemover implements EventSubscriberInterface
{
    /**
     * Config enabled value
     * @var bool
     */
    protected $enabled;

    public function __construct($enabled)
    {
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
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CompressionEvents::COMPRESS => 'onCompress',
        ];
    }

    /**
     * The pattern for mb_eregi_replace
     */
    protected function getPattern()
    {
        return '(>|~%%%)\s+(<|%%%~)';
    }

    public function onCompress(CompressionEvent $event)
    {
        if (!$event->isSafeToContinue()) {
            return;
        }

        $event->setContent(mb_eregi_replace($this->getPattern(), '\1\2', $event->getContent()));
    }
}
