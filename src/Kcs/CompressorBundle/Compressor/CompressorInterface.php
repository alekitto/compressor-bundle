<?php

namespace Kcs\CompressorBundle\Compressor;

use Symfony\Component\HttpFoundation\Response;

/**
 * Abstract Compressor interface.
 * All the compressors in this bundle must implement this interface
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
interface CompressorInterface {

    /**
     * Manipulate and compress the content of $html argument
     * @return string The compressed content
     */
    public function process(Response $response);
}
