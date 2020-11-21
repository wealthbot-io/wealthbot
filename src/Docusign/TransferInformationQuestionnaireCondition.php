<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 03.09.13
 * Time: 18:50
 * To change this template use File | Settings | File Templates.
 */

namespace App\Docusign;

use App\Entity\TransferCustodianQuestionAnswer;
use App\Entity\TransferInformation;

class TransferInformationQuestionnaireCondition extends AbstractTransferInformationCondition
{
    protected function checkObject(TransferInformation $object)
    {
        /** @var TransferCustodianQuestionAnswer $questionnaireAnswer */
        foreach ($object->getQuestionnaireAnswers() as $questionnaireAnswer) {
            $question = $questionnaireAnswer->getQuestion();
            $value = (bool) $questionnaireAnswer->getValue();

            if ($value !== $question->getDocusignEligibleAnswer()) {
                return false;
            }
        }

        return true;
    }
}
