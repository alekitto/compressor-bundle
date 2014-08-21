<?php

namespace Kcs\CompressorBundle\Response;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAware;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Event listener for kernel.response event
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class ResponseListener extends ContainerAware implements EventSubscriberInterface
{
    /**
     * Config enabled value
     * @var bool
     */
    protected $enabled;

    public function __construct(Container $container, $enabled)
    {
        $this->setContainer($container);
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
            KernelEvents::RESPONSE => 'onKernelResponse'
        );
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        // This filter is not enabled. Skip...
        if (!$this->isEnabled()) return;

        $response = $event->getResponse();
        $this->processResponse($response);
    }

    public function processResponse(Response $response)
    {
        // Call the compressors
        $this->container->get('kcs_compressor.html_compressor')->process($response);
    }
}
