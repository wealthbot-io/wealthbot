<?php
namespace Lib\Logger\Writer;


abstract class AbstractWriter
{
    const PRIORITY_ERROR   = 1;
    const PRIORITY_WARNING = 2;
    const PRIORITY_NOTICE  = 3;
    const PRIORITY_INFO    = 4;
    const PRIORITY_DEBUG   = 5;

    /**
     * Write message
     *
     * @param $message
     * @param integer $priority
     */
    abstract public function write($message, $priority);

    /**
     * Get datetime
     *
     * @param string $format
     * @return string
     */
    protected function getDateTime($format = 'd/m/Y H:i:s')
    {
        return date($format);
    }

    /**
     * Get priority as string
     *
     * @param integer $priority
     * @return string
     */
    protected function getPriorityAsString($priority)
    {
        switch ($priority) {
            case self::PRIORITY_ERROR:
                $str = 'ERROR';
                break;
            case self::PRIORITY_WARNING:
                $str = 'WARNING';
                break;
            case self::PRIORITY_NOTICE:
                $str = 'NOTICE';
                break;
            case self::PRIORITY_INFO:
                $str = 'INFO';
                break;
            case self::PRIORITY_DEBUG:
                $str = 'DEBUG';
                break;
            default:
                $str = '';
                break;
        }

        return $str;
    }
} 