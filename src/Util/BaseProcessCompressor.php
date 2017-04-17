<?php

namespace Kcs\CompressorBundle\Util;

use Kcs\CompressorBundle\Compressor\InlineCompressorInterface;
use Symfony\Component\Process\ProcessBuilder;

abstract class BaseProcessCompressor implements InlineCompressorInterface
{
    private $timeout;

    /**
     * Set the process timeout.
     *
     * @param int $timeout The timeout for the process
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * Creates a new process builder.
     *
     * @param array $arguments An optional array of arguments
     *
     * @return ProcessBuilder A new process builder
     */
    protected function createProcessBuilder(array $arguments = [])
    {
        $pb = new ProcessBuilder($arguments);

        if (null !== $this->timeout) {
            $pb->setTimeout($this->timeout);
        }

        return $pb;
    }
}
