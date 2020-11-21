<?php

namespace App\Entity;

/**
 * Class ClosingAccountMessage
 * @package App\Entity
 */
class ClosingAccountMessage
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $message;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set message.
     *
     * @param string $message
     *
     * @return ClosingAccountMessage
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
