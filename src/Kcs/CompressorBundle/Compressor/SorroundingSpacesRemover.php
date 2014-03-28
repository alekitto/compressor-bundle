<?php

namespace Kcs\CompressorBundle\Compressor;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Kcs\CompressorBundle\Event\CompressionEvents;
use Kcs\CompressorBundle\Event\CompressionEvent;

/**
 * Removes spaces sorrounding tags
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class SorroundingSpacesRemover implements EventSubscriberInterface
{
    const TAGS = "html|head|body|br|p|h1|h2|h3|h4|h5|h6|blockquote|center|dl|fieldset|form|frame|frameset|hr|noframes|ol|table|tbody|tr|td|th|tfoot|thead|ul";

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
            CompressionEvents::COMPRESS => 'onCompress'
        );
    }

    /**
     * The pattern for mb_eregi_replace
     */
    protected function getPattern() {
        return '\s*(</?(?:' . self::TAGS . ')(?:>|[\s/][^>]*>))\s*';
    }

    public function onCompress(CompressionEvent $event) {
        $event->setContent(mb_eregi_replace($this->getPattern(), '\1', $event->getContent()));
    }
}
