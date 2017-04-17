<?php

namespace Kcs\CompressorBundle\Response;

use Kcs\CompressorBundle\Compressor\CompressorInterface;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event listener for kernel.response event
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class ResponseListener implements EventSubscriberInterface
{
    /**
     * Config enabled value
     * @var bool
     */
    protected $enabled;

    /**
     * The response compressor
     * @var CompressorInterface
     */
    protected $compressor;

    public function __construct(CompressorInterface $compressor, $enabled)
    {
        $this->setEnabled($enabled);
        $this->compressor = $compressor;
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
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        // This filter is not enabled. Skip...
        if (!$this->isEnabled()) {
            return;
        }

        $response = $event->getResponse();
        $this->processResponse($response);
    }

    public function processResponse(Response $response)
    {
        // Call the compressors
        $this->compressor->process($response);
    }
}
