<?php

namespace Kcs\CompressorBundle\Preserver;

/**
 * Compression text area content preserver
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class TextAreaPreserver extends AbstractTagPreserver
{
    /**
     * Returns the block regex
     */
    protected function getPattern()
    {
        return '#(<textarea[^>]*?>(?:.*?)</textarea>)#usi';
    }

    /**
     * Returns the block temp replacement format for sprintf
     */
    protected function getReplacementFormat()
    {
        return '%%%%%%~COMPRESS~TXAREA~%u~%%%%%%';
    }

    /**
     * Returns the block replacement regex
     */
    protected function getReplacementPattern()
    {
        return '#%%%~COMPRESS~TXAREA~(\d+?)~%%%#u';
    }
}
