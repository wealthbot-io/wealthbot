<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 03.09.13
 * Time: 19:04
 * To change this template use File | Settings | File Templates.
 */

namespace App\Docusign;

use App\Entity\TransferInformation;

class TransferInformationPolicyCondition extends AbstractTransferInformationCondition
{
    protected function checkObject(TransferInformation $object)
    {
        if (!$object->getIsIncludePolicy()) {
            return true;
        }

        return false;
    }
}
