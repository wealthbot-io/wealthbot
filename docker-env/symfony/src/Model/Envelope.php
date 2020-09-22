<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 18.09.13
 * Time: 13:22
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

use App\Exception\InvalidEnvelopeStatusException;

class Envelope
{
    /**
     * @var string
     */
    private $emailSubject;

    /**
     * @var string
     */
    private $emailBlurb;

    /**
     * @var array
     */
    private $recipients;

    /**
     * @var string
     */
    private $status;

    const STATUS_CREATED = 'created';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_SIGNED = 'signed';
    const STATUS_COMPLETED = 'completed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_DECLINED = 'declined';
    const STATUS_TIMED_OUT = 'timedOut';
    const STATUS_VOIDED = 'voided';
    const STATUS_DELETED = 'deleted';

    private static $_statuses = null;

    public function __construct()
    {
        $this->recipients = [];
    }

    /**
     * Set email blurb.
     *
     * @param string $emailBlurb
     */
    public function setEmailBlurb($emailBlurb)
    {
        $this->emailBlurb = $emailBlurb;
    }

    /**
     * Get email blurb.
     *
     * @return string
     */
    public function getEmailBlurb()
    {
        return $this->emailBlurb;
    }

    /**
     * Set email subject.
     *
     * @param string $emailSubject
     */
    public function setEmailSubject($emailSubject)
    {
        $this->emailSubject = $emailSubject;
    }

    /**
     * Get email subject.
     *
     * @return string
     */
    public function getEmailSubject()
    {
        return $this->emailSubject;
    }

    /**
     * Set recipients.
     *
     * @param array $recipients
     */
    public function setRecipients(array $recipients)
    {
        $this->recipients = $recipients;
    }

    /**
     * Add recipient.
     *
     * @param RecipientInterface $recipient
     */
    public function addRecipient(RecipientInterface $recipient)
    {
        $this->recipients[] = $recipient;
    }

    /**
     * Get recipients.
     *
     * @return array
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Get status choices.
     *
     * @return array
     */
    public static function getStatusChoices()
    {
        if (null === self::$_statuses) {
            self::$_statuses = [];

            $rClass = new \ReflectionClass('App\Model\Envelope');
            $prefix = 'STATUS_';

            foreach ($rClass->getConstants() as $key => $value) {
                if (substr($key, 0, strlen($prefix)) === $prefix) {
                    self::$_statuses[$value] = $value;
                }
            }
        }

        return self::$_statuses;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @throws \App\Exception\InvalidEnvelopeStatusException
     */
    public function setStatus($status)
    {
        if (!in_array($status, self::getStatusChoices())) {
            throw new InvalidEnvelopeStatusException(sprintf('Invalid envelope status: %s', $status));
        }

        $this->status = $status;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}
