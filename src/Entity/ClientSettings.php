<?php

namespace App\Entity;

/**
 * Class ClientSettings
 * @package App\Entity
 */
class ClientSettings
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $client_id;

    /**
     * @var float
     */
    private $stop_tlh_value;

    /**
     * @param \App\Entity\User
     */
    private $client;

    /**
     * @var string
     */
    private $client_type;

    const CLIENT_TYPE_NEW = 'new';
    const CLIENT_TYPE_CURRENT = 'current';

    private static $_clientTypes = null;

    public function __construct()
    {
        $this->client_type = self::CLIENT_TYPE_NEW;
    }

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
     * Set client_id.
     *
     * @param int $clientId
     *
     * @return ClientSettings
     */
    public function setClientId($clientId)
    {
        $this->client_id = $clientId;

        return $this;
    }

    /**
     * Get client_id.
     *
     * @return int
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Set stop_tlh_value.
     *
     * @param float $stopTlhValue
     *
     * @return ClientSettings
     */
    public function setStopTlhValue($stopTlhValue)
    {
        $this->stop_tlh_value = $stopTlhValue;

        return $this;
    }

    /**
     * Get stop_tlh_value.
     *
     * @return float
     */
    public function getStopTlhValue()
    {
        return $this->stop_tlh_value;
    }

    /**
     * Set client.
     *
     * @param \App\Entity\User $client
     *
     * @return ClientSettings
     */
    public function setClient(User $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return \App\Entity\User
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get client type choices.
     *
     * @static
     *
     * @return array
     */
    public static function getClientTypeChoices()
    {
        if (null === self::$_clientTypes) {
            self::$_clientTypes = [];

            $rClass = new \ReflectionClass('App\Entity\ClientSettings');
            $prefix = 'CLIENT_TYPE_';
            foreach ($rClass->getConstants() as $key => $value) {
                if (substr($key, 0, strlen($prefix)) === $prefix) {
                    self::$_clientTypes[$value] = $value;
                }
            }
        }

        return self::$_clientTypes;
    }

    /**
     * Set client_type.
     *
     * @param $clientType
     *
     * @return ClientSettings
     *
     * @throws \InvalidArgumentException
     */
    public function setClientType($clientType)
    {
        if (!in_array($clientType, self::getClientTypeChoices())) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid value for client_settings.client_type column: %s',
                $clientType
            ));
        }

        $this->client_type = $clientType;

        return $this;
    }

    /**
     * Set client type value to 'new'.
     *
     * @return ClientSettings
     */
    public function setClientTypeNew()
    {
        return $this->setClientType(self::CLIENT_TYPE_NEW);
    }

    /**
     * Set client type value to 'current'.
     *
     * @return ClientSettings
     */
    public function setClientTypeCurrent()
    {
        return $this->setClientType(self::CLIENT_TYPE_CURRENT);
    }

    /**
     * Get client_type.
     *
     * @return string
     */
    public function getClientType()
    {
        return $this->client_type;
    }
}
