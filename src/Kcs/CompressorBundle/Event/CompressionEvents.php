<?php

namespace Kcs\CompressorBundle\Event;

/**
 * Compression Events helper class
 * Only contains constants used in compressor processing
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
final class CompressionEvents
{
    /**
     * This event will be fired on the pre-compression phase
     * Should be used by preservers in order to replace the content
     * that must be retained with temporary replacements
     */
    const PRE_PROCESS = 'kcs_compressor.pre_process';

    /**
     * Event fired on compression phase
     * Used by compressors to remove unnecessary blocks
     */
    const COMPRESS = 'kcs_compressor.compress';

    /**
     * Event fired when compression phase has finished
     * Should be used by preservers to revert the modifications made in the
     * pre-processing phase
     */
    const POST_PROCESS = 'kcs_compressor.post_process';
}
