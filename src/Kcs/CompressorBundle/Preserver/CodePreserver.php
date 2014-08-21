<?php

namespace Kcs\CompressorBundle\Preserver;

/**
 * Compression preserver for <code> tag
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class CodePreserver extends AbstractTagPreserver
{
    /**
     * Returns the block regex
     */
    protected function getPattern()
    {
        return '#(<code[^>]*?>(?:.*?)</code>)#usi';
    }

    /**
     * Returns the block temp replacement format for sprintf
     */
    protected function getReplacementFormat()
    {
        return '%%%%%%~COMPRESS~CODE~%u~%%%%%%';
    }

    /**
     * Returns the block replacement regex
     */
    protected function getReplacementPattern()
    {
        return '#%%%~COMPRESS~CODE~(\d+?)~%%%#u';
    }
}
