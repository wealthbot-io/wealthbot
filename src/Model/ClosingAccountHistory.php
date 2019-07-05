<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 05.04.13
 * Time: 15:23
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

class ClosingAccountHistory implements WorkflowableInterface
{
    /**
     * @var array
     */
    protected $messages;

    /**
     * Add new message.
     *
     * @param $message
     *
     * @return ClosingAccountHistory
     */
    public function addMessage($message)
    {
        if (!in_array($message, $this->messages, true)) {
            $this->messages[] = $message;
        }

        return $this;
    }

    /**
     * Set messages.
     *
     * @param array $messages
     *
     * @return $this
     */
    public function setMessages(array $messages)
    {
        $this->messages = [];

        foreach ($messages as $message) {
            $this->addMessage($message);
        }

        return $this;
    }

    /**
     * Get messages.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Get workflow message code.
     *
     * @return string
     */
    public function getWorkflowMessageCode()
    {
        return Workflow::MESSAGE_CODE_ALERT_CLOSED_ACCOUNT;
    }
}
