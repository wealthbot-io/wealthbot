<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 04.09.13
 * Time: 14:34
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Tests\Docusign;

use Wealthbot\ClientBundle\Docusign\TransferInformationConsolidatorCondition;
use Wealthbot\ClientBundle\Docusign\TransferInformationCustodianCondition;
use Wealthbot\ClientBundle\Docusign\TransferInformationPolicyCondition;
use Wealthbot\ClientBundle\Docusign\TransferInformationQuestionnaireCondition;
use Wealthbot\SignatureBundle\Docusign\DocusignChecker;

class DocusignCheckerStrategyTest extends \PHPUnit_Framework_TestCase
{
    private $transferInformation;
    private $custodian;
    private $question;
    private $answer;

    /** @var  DocusignChecker */
    private $checker;

    public function setUp()
    {
        $this->transferInformation = $this->getMock('Wealthbot\ClientBundle\Entity\TransferInformation', null);
        $this->custodian = $this->getMock('Wealthbot\ClientBundle\Entity\TransferCustodian', null);
        $this->question = $this->getMock('Wealthbot\ClientBundle\Entity\TransferCustodianQuestion', null);
        $this->answer = $this->getMock('Wealthbot\ClientBundle\Entity\TransferCustodianQuestionAnswer', null);

        $this->answer->setQuestion($this->question);

        $accountMock = $this->getMock('Wealthbot\ClientBundle\Entity\ClientAccount');
        $this->transferInformation->setClientAccount($accountMock);

        $conditions = [
            new TransferInformationCustodianCondition(),
            new TransferInformationPolicyCondition(),
            new TransferInformationQuestionnaireCondition(),
            new TransferInformationConsolidatorCondition(),
        ];

        $this->checker = new DocusignChecker($conditions);
    }

    public function testConditionsValid()
    {
        $this->question->setDocusignEligibleAnswer(true);
        $this->answer->setValue(true);

        $this->transferInformation->setIsIncludePolicy(false);
        $this->transferInformation->setTransferCustodian($this->custodian);
        $this->transferInformation->addQuestionnaireAnswer($this->answer);

        $this->assertTrue($this->checker->checkConditions($this->transferInformation));
    }

    public function testCustodianConditionInvalid()
    {
        $this->question->setDocusignEligibleAnswer(true);
        $this->answer->setValue(true);

        $this->transferInformation->setIsIncludePolicy(false);
        $this->transferInformation->addQuestionnaireAnswer($this->answer);

        $this->assertFalse($this->checker->checkConditions($this->transferInformation));
    }

    public function testPolicyConditionInvalid()
    {
        $this->question->setDocusignEligibleAnswer(true);
        $this->answer->setValue(true);

        $this->transferInformation->setIsIncludePolicy(true);
        $this->transferInformation->setTransferCustodian($this->custodian);
        $this->transferInformation->addQuestionnaireAnswer($this->answer);

        $this->assertFalse($this->checker->checkConditions($this->transferInformation));
    }

    public function testQuestionnaireConditionInvalid()
    {
        $this->question->setDocusignEligibleAnswer(true);
        $this->answer->setValue(false);

        $this->transferInformation->setIsIncludePolicy(false);
        $this->transferInformation->setTransferCustodian($this->custodian);
        $this->transferInformation->addQuestionnaireAnswer($this->answer);

        $this->assertFalse($this->checker->checkConditions($this->transferInformation));
    }
}
