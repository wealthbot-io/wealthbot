<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 19.09.13
 * Time: 14:13
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model\Tab;

class CheckboxTab extends AbstractCheckableTab
{
    public function __construct()
    {
        $this->type = self::TYPE_CHECKBOX;

        parent::__construct();
    }
}
