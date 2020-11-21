<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 03.09.13
 * Time: 19:31
 * To change this template use File | Settings | File Templates.
 */

namespace App\Docusign;

use App\Entity\TransferInformation;

class TransferInformationCustodianCondition extends AbstractTransferInformationCondition
{
    protected function checkObject(TransferInformation $object)
    {
        if (!is_object($object->getTransferCustodian())) {
            return false;
        }

        return true;
    }
}
