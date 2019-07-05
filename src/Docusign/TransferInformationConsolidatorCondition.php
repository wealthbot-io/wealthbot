<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 04.09.13
 * Time: 18:31
 * To change this template use File | Settings | File Templates.
 */

namespace App\Docusign;

use App\Entity\TransferInformation;

class TransferInformationConsolidatorCondition extends AbstractTransferInformationCondition
{
    protected function checkObject(TransferInformation $object)
    {
        $account = $object->getClientAccount();
        $consolidator = $account->getConsolidator();

        if ($consolidator) {
            $primaryApplicant = $consolidator->getPrimaryApplicant();
            $accountTitle = $primaryApplicant->getFirstName().' '.
                $primaryApplicant->getMiddleName().' '.$primaryApplicant->getLastName();

            if ($accountTitle !== $object->getAccountTitle()) {
                return false;
            }

            if ($consolidator->isJointType()) {
                $secondaryApplicant = $consolidator->getSecondaryApplicant();
                $jointAccountTitle = $secondaryApplicant->getFirstName().' '.
                    $secondaryApplicant->getMiddleName().' '.$secondaryApplicant->getLastName();

                if ($jointAccountTitle !== $object->getAccountJointTitle()) {
                    return false;
                }
            }
        }

        return true;
    }
}
