<?php
namespace Lib\Logger\Writer;


class ConsoleWriter extends AbstractWriter
{
    /**
     * Write message
     *
     * @param $message
     * @param integer $priority
     */
    public function write($message, $priority)
    {
        echo "[{$this->getDateTime()}] {$this->getPriorityColorCode($priority)}{$this->getPriorityAsString($priority)}: ";

        if ($message instanceof \Exception) {
            echo "{$message->getMessage()} in {$message->getFile()}:{$message->getLine()}";
        } elseif (is_array($message) || is_object($message)) {
            print_r($message, true);
        } else {
            echo $message;
        }

        echo "{$this->getPriorityColorCode()} \n";
    }

    /**
     * Get color code of the priority
     *
     * @param integer $priority
     * @return string
     */
    private function getPriorityColorCode($priority = null)
    {
        switch ($priority) {
            case self::PRIORITY_ERROR:
                $color = "\033[0;31m";
                break;
            case self::PRIORITY_WARNING:
                $color = "\033[0;35m";
                break;
            case self::PRIORITY_NOTICE:
                $color = "\033[0;33m";
                break;
            case self::PRIORITY_INFO:
                $color = "\033[0;34m";
                break;
            case self::PRIORITY_DEBUG:
                $color = "\033[0;30m\033[46m";
                break;
            default:
                $color = "\033[0m";
                break;
        }

        return $color;
    }
}