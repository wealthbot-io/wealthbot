<?php

namespace App\Entity;

/**
 * Class CustodianMessage
 * @package App\Entity
 */
class CustodianMessage
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $custodian_id;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $type;

    // Constants for type attribute
    const TYPE_MAIL_CHECK = 'mail_check';
    const TYPE_WIRE_TRANSFER = 'wire_transfer';
    const TYPE_ROLLOVER = 'rollover';

    /**
     * @var null
     */
    protected static $_types = null;

    /**
     * @param \App\Entity\Custodian
     */
    private $custodian;

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
     * Set custodian_id.
     *
     * @param int $custodianId
     *
     * @return CustodianMessage
     */
    public function setCustodianId($custodianId)
    {
        $this->custodian_id = $custodianId;

        return $this;
    }

    /**
     * Get custodian_id.
     *
     * @return int
     */
    public function getCustodianId()
    {
        return $this->custodian_id;
    }

    /**
     * Set message.
     *
     * @param string $message
     *
     * @return CustodianMessage
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

    /**
     * Get type choices.
     *
     * @return array|null
     */
    public static function getTypeChoices()
    {
        if (null === self::$_types) {
            self::$_types = [];

            $oClass = new \ReflectionClass('\App\Entity\CustodianMessage');
            $classConstants = $oClass->getConstants();
            $constantPrefix = 'TYPE_';

            foreach ($classConstants as $key => $value) {
                if (substr($key, 0, strlen($constantPrefix)) === $constantPrefix) {
                    self::$_types[$value] = $value;
                }
            }
        }

        return self::$_types;
    }

    /**
     * * Set type.
     *
     * @param string $type
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setType($type)
    {
        if (!in_array($type, self::getTypeChoices())) {
            throw new \InvalidArgumentException(sprintf('Invalid value for type column: %s', $type));
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set custodian.
     *
     * @param \App\Entity\Custodian $custodian
     *
     * @return CustodianMessage
     */
    public function setCustodian(Custodian $custodian = null)
    {
        $this->custodian = $custodian;

        return $this;
    }

    /**
     * Get custodian.
     *
     * @return \App\Entity\Custodian
     */
    public function getCustodian()
    {
        return $this->custodian;
    }
}
