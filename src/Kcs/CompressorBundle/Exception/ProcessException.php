<?php

namespace Kcs\CompressorBundle\Exception;

use Symfony\Component\Process\Process;

/**
 * Describes an exception that occurred within a filter
 */
class ProcessException extends \RuntimeException implements Exception
{
    private $originalMessage;
    private $input;

    public static function fromProcess(Process $proc)
    {
        $message = sprintf("An error occurred while running:\n%s", $proc->getCommandLine());

        $errorOutput = $proc->getErrorOutput();
        if (!empty($errorOutput)) {
            $message .= "\n\nError Output:\n".str_replace("\r", '', $errorOutput);
        }

        $output = $proc->getOutput();
        if (!empty($output)) {
            $message .= "\n\nOutput:\n".str_replace("\r", '', $output);
        }

        return new self($message);
    }

    public function __construct($message, $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->originalMessage = $message;
    }

    public function setInput($input)
    {
        $this->input = $input;
        $this->updateMessage();

        return $this;
    }

    public function getInput()
    {
        return $this->input;
    }

    private function updateMessage()
    {
        $message = $this->originalMessage;

        if (!empty($this->input)) {
            $message .= "\n\nInput:\n".$this->input;
        }

        $this->message = $message;
    }
}
