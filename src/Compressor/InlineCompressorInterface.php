<?php

namespace Kcs\CompressorBundle\Compressor;

/**
 * Abstract inline compressor interface.
 * The compressors for inline js, css, etc. must implement this interface
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
interface InlineCompressorInterface
{
    /**
     * This function should compress an inline block of code
     * and return the compressed block as a string
     *
     * @param string $block The inline block to be compressed
     * @return string The compressed block
     */
    public function compress($block);
}
