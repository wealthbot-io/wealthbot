<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 04.09.13
 * Time: 13:59
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Tests\Docusign;

use Wealthbot\ClientBundle\Docusign\TransferInformationQuestionnaireCondition;
use Wealthbot\ClientBundle\Entity\TransferInformation;

class TransferInformationQuestionnaireConditionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  TransferInformationQuestionnaireCondition */
    private $condition;

    public function setUp()
    {
        $this->condition = new TransferInformationQuestionnaireCondition();
    }

    public function testDocusignAllowed()
    {
        $transferInformation = new TransferInformation();

        $this->assertTrue($this->condition->check($transferInformation));

        $questionMock = $this->getQuestionMock();
        $questionMock->setDocusignEligibleAnswer(true);

        $answerMock = $this->getAnswerMock();
        $answerMock->setQuestion($questionMock);
        $answerMock->setValue(true);

        $transferInformation->addQuestionnaireAnswer($answerMock);

        $this->assertTrue($this->condition->check($transferInformation));
    }

    public function testDocusignNotAllowed()
    {
        $transferInformation = new TransferInformation();

        $questionMock1 = $this->getQuestionMock();
        $questionMock1->setDocusignEligibleAnswer(true);

        $questionMock2 = $this->getQuestionMock();
        $questionMock2->setDocusignEligibleAnswer(true);

        $answerMock1 = $this->getAnswerMock();
        $answerMock1->setQuestion($questionMock1);
        $answerMock1->setValue(true);

        $answerMock2 = $this->getAnswerMock();
        $answerMock2->setQuestion($questionMock1);
        $answerMock2->setValue(false);

        $transferInformation->addQuestionnaireAnswer($answerMock1);
        $transferInformation->addQuestionnaireAnswer($answerMock2);

        $this->assertFalse($this->condition->check($transferInformation));
    }

    private function getQuestionMock()
    {
        $mock = $this->getMock('Wealthbot\ClientBundle\Entity\TransferCustodianQuestion', null);

        return $mock;
    }

    private function getAnswerMock()
    {
        $mock = $this->getMock('Wealthbot\ClientBundle\Entity\TransferCustodianQuestionAnswer', null);

        return $mock;
    }
}
