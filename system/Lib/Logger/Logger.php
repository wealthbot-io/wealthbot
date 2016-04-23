<?php

namespace Lib\Logger;


use Lib\Logger\Writer\AbstractWriter;

class Logger
{
    /** @var AbstractWriter[] */
    private $writers;


    /**
     * @param AbstractWriter[]|AbstractWriter $writers
     */
    public function __construct($writers)
    {
        if (is_array($writers)) {
            foreach ($writers as $writer) {
                $this->addWriter($writer);
            }
        } else {
            $this->addWriter($writers);
        }
    }

    /**
     * Add writer
     *
     * @param AbstractWriter $writer
     */
    public function addWriter(AbstractWriter $writer)
    {
        $this->writers[] = $writer;
    }

    /**
     * Write to log error message
     *
     * @param $message
     */
    public function logError($message)
    {
        $this->log($message, AbstractWriter::PRIORITY_ERROR);
    }

    /**
     * Write to log warning message
     *
     * @param $message
     */
    public function logWarning($message)
    {
        $this->log($message, AbstractWriter::PRIORITY_WARNING);
    }

    /**
     * Write to log notice message
     *
     * @param $message
     */
    public function logNotice($message)
    {
        $this->log($message, AbstractWriter::PRIORITY_NOTICE);
    }

    /**
     * Write to log info message
     *
     * @param $message
     */
    public function logInfo($message)
    {
        $this->log($message, AbstractWriter::PRIORITY_INFO);
    }

    /**
     * Write to log message for debug
     *
     * @param $message
     */
    public function logDebug($message)
    {
        $this->log($message, AbstractWriter::PRIORITY_DEBUG);
    }

    /**
     * Write to log
     *
     * @param $message
     * @param integer $priority
     */
    public function log($message, $priority = AbstractWriter::PRIORITY_INFO)
    {
        foreach ($this->writers as $writer) {
            $writer->write($message, $priority);
        }
    }
} 