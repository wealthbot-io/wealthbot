<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 04.09.13
 * Time: 19:06
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Tests\Docusign;

use Wealthbot\ClientBundle\Docusign\TransferInformationConsolidatorCondition;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\ClientBundle\Entity\ClientAccountOwner;
use Wealthbot\ClientBundle\Entity\SystemAccount;

class TransferInformationConsolidatorConditionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  TransferInformationConsolidatorCondition */
    private $condition;

    private $consolidatorMock;
    private $accountMock;
    private $transferInformationMock;

    public function setUp()
    {
        $this->condition = new TransferInformationConsolidatorCondition();
        $this->consolidatorMock = $this->getMock('Wealthbot\ClientBundle\Entity\ClientAccount', null);
        $this->accountMock = $this->getMock('Wealthbot\ClientBundle\Entity\ClientAccount', null);
        $this->transferInformationMock = $this->getMock('Wealthbot\ClientBundle\Entity\TransferInformation', null);

        $userMock = $this->getUserMock();
        $this->accountMock->setClient($userMock);
        $this->consolidatorMock->setClient($userMock);

        $primaryOwner = new ClientAccountOwner();
        $primaryOwner->setOwnerType(ClientAccountOwner::OWNER_TYPE_SELF);
        $primaryOwner->setClient($userMock);

        $secondaryOwner = new ClientAccountOwner();
        $secondaryOwner->setOwnerType(ClientAccount::OWNER_SPOUSE);
        $secondaryOwner->setContact($this->getAdditionalContactMock());

        $this->consolidatorMock->addAccountOwner($primaryOwner)->addAccountOwner($secondaryOwner);

        $this->accountMock->setConsolidator($this->consolidatorMock);
        $this->transferInformationMock->setClientAccount($this->accountMock);
    }

    public function testDocusignNotAllowed()
    {
        $this->consolidatorMock->setSystemType(SystemAccount::TYPE_PERSONAL_INVESTMENT);
        $this->accountMock->setSystemType(SystemAccount::TYPE_PERSONAL_INVESTMENT);
        $this->transferInformationMock->setTitleFirst('TestF')->setTitleMiddle('TestM')->setTitleLast('TestL');

        $this->assertFalse($this->condition->check($this->transferInformationMock));

        $this->consolidatorMock->setSystemType(SystemAccount::TYPE_JOINT_INVESTMENT);
        $this->accountMock->setSystemType(SystemAccount::TYPE_JOINT_INVESTMENT);
        $this->transferInformationMock
            ->setTitleFirst('PrimaryFirstName')
            ->setTitleMiddle('PrimaryMiddleName')
            ->setTitleLast('PrimaryLastName');
        $this->transferInformationMock
            ->setJointTitleFirst('TestF')
            ->setJointTitleMiddle('TestM')
            ->setJointTitleLast('TestL');

        $this->assertFalse($this->condition->check($this->transferInformationMock));
    }

    public function testDocusignAllowed()
    {
        $this->consolidatorMock->setSystemType(SystemAccount::TYPE_PERSONAL_INVESTMENT);
        $this->accountMock->setSystemType(SystemAccount::TYPE_PERSONAL_INVESTMENT);
        $this->transferInformationMock
            ->setTitleFirst('PrimaryFirstName')
            ->setTitleMiddle('PrimaryMiddleName')
            ->setTitleLast('PrimaryLastName');

        $this->assertTrue($this->condition->check($this->transferInformationMock));

        $this->consolidatorMock->setSystemType(SystemAccount::TYPE_JOINT_INVESTMENT);
        $this->accountMock->setSystemType(SystemAccount::TYPE_JOINT_INVESTMENT);
        $this->transferInformationMock
            ->setJointTitleFirst('SecondaryFirstName')
            ->setJointTitleMiddle('SecondaryMiddleName')
            ->setJointTitleLast('SecondaryLastName');

        $this->assertTrue($this->condition->check($this->transferInformationMock));
    }

    private function getUserMock()
    {
        $userMock = $this->getMock(
            'Wealthbot\UserBundle\Entity\User',
            ['getFirstName', 'getLastName', 'getMiddleName', 'isMarried']
        );

        $userMock->expects($this->any())
            ->method('getFirstName')
            ->will($this->returnValue('PrimaryFirstName'));
        $userMock->expects($this->any())
            ->method('getMiddleName')
            ->will($this->returnValue('PrimaryMiddleName'));
        $userMock->expects($this->any())
            ->method('getLastName')
            ->will($this->returnValue('PrimaryLastName'));
        $userMock->expects($this->any())
            ->method('isMarried')
            ->will($this->returnValue(true));

        return $userMock;
    }

    private function getAdditionalContactMock()
    {
        $contactMock = $this->getMock(
            'Wealthbot\ClientBundle\Entity\ClientAdditionalContact',
            ['getFirstName', 'getLastName', 'getMiddleName']
        );

        $contactMock->expects($this->any())
            ->method('getFirstName')
            ->will($this->returnValue('SecondaryFirstName'));
        $contactMock->expects($this->any())
            ->method('getMiddleName')
            ->will($this->returnValue('SecondaryMiddleName'));
        $contactMock->expects($this->any())
            ->method('getLastName')
            ->will($this->returnValue('SecondaryLastName'));

        return $contactMock;
    }
}
