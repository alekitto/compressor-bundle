<?php

namespace Kcs\CompressorBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

/**
 * The compression event passed to event listeners
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class CompressionEvent extends Event
{
    /**
     * The current response object
     * @var Response
     */
    protected $response = null;

    /**
     * The original response content
     * @var string
     */
    protected $originalContent = null;

    public function __construct(Response $response) {
        $this->response = $response;
        $this->originalContent = $response->getContent();
    }

    /**
     * @return Response
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * Get the response charset
     * @return string
     */
    public function getCharset() {
        return $this->response->getCharset();
    }

    /**
     * Get the original response content
     * @return string
     */
    public function getOriginalContent() {
        return $this->originalContent;
    }

    /**
     * Get the current content of the response
     * @return string
     */
    public function getContent() {
        return $this->response->getContent();
    }

    /**
     * Replace the current response content
     * @param string $content
     * @return CompressionEvent
     */
    public function setContent($content) {
        $this->response->setContent($content);
        return $this;
    }
}
