<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 19.09.13
 * Time: 19:21
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model\Tab;

class AbstractCheckableTab extends AbstractTab
{
    /**
     * @var bool
     */
    protected $selected;

    public function __construct()
    {
        $this->selected = false;
    }

    /**
     * Set selected.
     *
     * @param bool $selected
     *
     * @return $this
     */
    public function setSelected($selected)
    {
        $this->selected = $selected;

        return $this;
    }

    /**
     * Get selected.
     *
     * @return bool
     */
    public function getSelected()
    {
        return $this->selected;
    }
}
