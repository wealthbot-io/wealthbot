<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.05.13
 * Time: 15:59
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

class ClientAccountOwner
{
    /**
     * @var string
     */
    protected $owner_type;

    const OWNER_TYPE_SELF = 'self';
    const OWNER_TYPE_SPOUSE = 'spouse';
    const OWNER_TYPE_OTHER = 'other';

    private static $_ownerTypes = null;

    /**
     * Get choices for owner_type column.
     *
     * @return array|null
     */
    public static function getOwnerTypeChoices()
    {
        if (null === self::$_ownerTypes) {
            self::$_ownerTypes = [];
            $oClass = new \ReflectionClass('App\Model\ClientAccountOwner');
            $classConstants = $oClass->getConstants();
            $constantPrefix = 'OWNER_TYPE_';
            foreach ($classConstants as $key => $val) {
                if (substr($key, 0, strlen($constantPrefix)) === $constantPrefix) {
                    self::$_ownerTypes[$val] = $val;
                }
            }
        }

        return self::$_ownerTypes;
    }

    /**
     * Set owner_type.
     *
     * @param $ownerType
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setOwnerType($ownerType)
    {
        if (!in_array($ownerType, self::getOwnerTypeChoices())) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid value for client_account_owners.owner_type : %s',
                $ownerType
            ));
        }

        $this->owner_type = $ownerType;

        return $this;
    }

    /**
     * Get owner_type.
     *
     * @return string
     */
    public function getOwnerType()
    {
        return $this->owner_type;
    }
}
