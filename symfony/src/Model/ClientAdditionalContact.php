<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 30.04.13
 * Time: 15:27
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

class ClientAdditionalContact
{
    /**
     * @var string
     */
    protected $type;

    const TYPE_SPOUSE = 'spouse';
    const TYPE_OTHER = 'other';

    private static $_types = null;

    /**
     * Get choices for type column.
     *
     * @return array|null
     */
    public static function getTypeChoices()
    {
        if (null === self::$_types) {
            self::$_types = [];
            $oClass = new \ReflectionClass('App\Model\ClientAdditionalContact');
            $classConstants = $oClass->getConstants();
            $constantPrefix = 'TYPE_';
            foreach ($classConstants as $key => $val) {
                if (substr($key, 0, strlen($constantPrefix)) === $constantPrefix) {
                    self::$_types[$val] = $val;
                }
            }
        }

        return self::$_types;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return string $this
     *
     * @throws \InvalidArgumentException
     */
    public function setType($type)
    {
        if (!in_array($type, self::getTypeChoices())) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for client_additional_contact.type : %s.', $type)
            );
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
}
