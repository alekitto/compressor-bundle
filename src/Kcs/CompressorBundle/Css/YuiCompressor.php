<?php

namespace Kcs\CompressorBundle\Css;

use Kcs\CompressorBundle\Util\BaseProcessCompressor;
use Kcs\CompressorBundle\Exception\ProcessException;

/**
 * Use YUI compressor for inline css minifization
 *
 * @author Alessandro Chitolina <alekitto@gmail.com>
 */
class YuiCompressor extends BaseProcessCompressor
{
    private $javaPath;
    private $jarPath;

    public function __construct($jarPath, $javaPath = '/usr/bin/java') {
        $this->setJarPath($jarPath);
        $this->setJavaPath($javaPath);
    }

    public function setJavaPath($javaPath) {
        $this->javaPath = $javaPath;
    }

    public function setJarPath($jarPath) {
        $this->jarPath = $jarPath;
    }

    public function compress($block) {
        $pb = $this->createProcessBuilder(array($this->javaPath));

        $pb->add('-jar')->add($this->jarPath);

        // input and output files
        $tempDir = realpath(sys_get_temp_dir());
        $input = tempnam($tempDir, 'YUI-IN-');
        $output = tempnam($tempDir, 'YUI-OUT-');
        file_put_contents($input, $block);
        $pb->add('-o')->add($output)->add('--type')->add('css')->add($input);

        $proc = $pb->getProcess();
        $code = $proc->run();
        unlink($input);

        if (0 !== $code) {
            if (file_exists($output)) {
                unlink($output);
            }

            throw ProcessException::fromProcess($proc)->setInput($block);
        }

        if (!file_exists($output)) {
            throw new \RuntimeException('Error creating output file.');
        }

        $retval = file_get_contents($output);
        unlink($output);

        return $retval;
    }
}
