<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 03.09.13
 * Time: 18:50
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Docusign;

use Wealthbot\ClientBundle\Entity\TransferCustodianQuestionAnswer;
use Wealthbot\ClientBundle\Entity\TransferInformation;

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
