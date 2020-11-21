<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 18.09.13
 * Time: 18:51
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model\Tab;

abstract class AbstractTab
{
    /**
     * @var string
     */
    protected $tabLabel;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $type;

    const TYPE_TEXT = 'Text';
    const TYPE_CHECKBOX = 'Checkbox';
    const TYPE_RADIO_GROUP = 'Radio Group';

    /**
     * Set tab label.
     *
     * @param string $tabLabel
     *
     * @return $this
     */
    public function setTabLabel($tabLabel)
    {
        $this->tabLabel = $tabLabel;

        return $this;
    }

    /**
     * Get tab label.
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->tabLabel;
    }

    /**
     * Set value.
     *
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
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
