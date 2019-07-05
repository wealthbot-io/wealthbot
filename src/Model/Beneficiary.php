<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 05.02.13
 * Time: 18:14
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model;

class Beneficiary implements WorkflowableInterface
{
    /**
     * @var string
     */
    protected $type;

    // ENUM values type column
    const TYPE_PRIMARY = 'Primary';
    const TYPE_CONTINGENT = 'Contingent';

    private static $_typeValues = null;

    /**
     * Get array ENUM values type column.
     *
     * @static
     *
     * @return array
     */
    public static function getTypeChoices()
    {
        // Build $_typeValues if this is the first call
        if (null === self::$_typeValues) {
            self::$_typeValues = [];
            $oClass = new \ReflectionClass('\App\Model\Beneficiary');
            $classConstants = $oClass->getConstants();
            $constantPrefix = 'TYPE_';
            foreach ($classConstants as $key => $val) {
                if (substr($key, 0, strlen($constantPrefix)) === $constantPrefix) {
                    self::$_typeValues[$val] = $val;
                }
            }
        }

        return self::$_typeValues;
    }

    /**
     * Set type.
     *
     * @param $type
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setType($type)
    {
        if (!in_array($type, self::getTypeChoices())) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for client_beneficiaries.type : %s.', $type)
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

    /**
     * Is primary type.
     *
     * @return bool
     */
    public function isPrimary()
    {
        return self::TYPE_PRIMARY === $this->getType();
    }

    /**
     * Is contingent type.
     *
     * @return bool
     */
    public function isContingent()
    {
        return self::TYPE_CONTINGENT === $this->getType();
    }

    /**
     * Get workflow message code.
     *
     * @return string
     */
    public function getWorkflowMessageCode()
    {
        return Workflow::MESSAGE_CODE_PAPERWORK_UPDATE_BENEFICIARY;
    }
}
