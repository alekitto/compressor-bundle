<?php

namespace Kcs\CompressorBundle\Preserver;

/**
 * Compression preserver for <pre> tag
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class PrePreserver extends AbstractTagPreserver
{
    /**
     * Returns the block regex
     */
    protected function getPattern() {
        return '#(<pre[^>]*?>(?:.*?)</pre>)#usi';
    }

    /**
     * Returns the block temp replacement format for sprintf
     */
    protected function getReplacementFormat() {
        return '%%%%%%~COMPRESS~PRE~%u~%%%%%%';
    }

    /**
     * Returns the block replacement regex
     */
    protected function getReplacementPattern() {
        return '#%%%~COMPRESS~PRE~(\d+?)~%%%#u';
    }
}
