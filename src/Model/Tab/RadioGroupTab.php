<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 19.09.13
 * Time: 15:18
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model\Tab;

class RadioGroupTab extends AbstractCheckableTab
{
    /**
     * @var string
     */
    private $groupName;

    public function __construct()
    {
        $this->type = self::TYPE_RADIO_GROUP;

        parent::__construct();
    }

    /**
     * Set group name.
     *
     * @param string $groupName
     *
     * @return $this
     */
    public function setGroupName($groupName)
    {
        $this->groupName = $groupName;

        return $this;
    }

    /**
     * Get group name.
     *
     * @return string
     */
    public function getGroupName()
    {
        return $this->groupName;
    }
}
