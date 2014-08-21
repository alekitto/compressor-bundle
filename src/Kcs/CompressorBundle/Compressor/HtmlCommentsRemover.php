<?php

namespace Kcs\CompressorBundle\Compressor;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Kcs\CompressorBundle\Event\CompressionEvents;
use Kcs\CompressorBundle\Event\CompressionEvent;

/**
 * Html comments remover
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class HtmlCommentsRemover implements EventSubscriberInterface
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
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return array(
            CompressionEvents::COMPRESS => 'onCompress'
        );
    }

    /**
     * The html comments pattern for mb_eregi_replace
     */
    protected function getPattern()
    {
        return '<!---->|<!--[^\[].*?-->';
    }

    public function onCompress(CompressionEvent $event)
    {
        if (!$event->isSafeToContinue()) {
            return;
        }

        $event->setContent(mb_eregi_replace($this->getPattern(), '', $event->getContent()));
    }
}
