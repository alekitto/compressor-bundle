<?php

namespace Kcs\CompressorBundle\Compressor;

/**
 * Null inline compressor.
 * Simply returns the input block unmodified
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class NoneCompressor implements InlineCompressorInterface {
    public function compress($block) {
        return $block;
    }
}
