<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 19.09.13
 * Time: 14:02
 * To change this template use File | Settings | File Templates.
 */

namespace App\Model\Tab;

class TextTab extends AbstractTab
{
    public function __construct()
    {
        $this->type = self::TYPE_TEXT;
    }
}
