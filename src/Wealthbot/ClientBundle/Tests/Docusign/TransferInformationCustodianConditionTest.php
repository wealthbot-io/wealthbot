<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 04.09.13
 * Time: 13:03
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Tests\Docusign;

use Wealthbot\ClientBundle\Docusign\TransferInformationCustodianCondition;
use Wealthbot\ClientBundle\Entity\TransferCustodian;
use Wealthbot\ClientBundle\Entity\TransferInformation;

class TransferInformationCustodianConditionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  TransferInformationCustodianCondition */
    private $condition;

    public function setUp()
    {
        $this->condition = new TransferInformationCustodianCondition();
    }

    public function testDocusignNotAllowed()
    {
        $transferInformation = new TransferInformation();

        $this->assertFalse($this->condition->check($transferInformation));
    }

    public function testDocusignAllowed()
    {
        $transferInformation = new TransferInformation();
        $transferCustodian = new TransferCustodian();

        $transferInformation->setTransferCustodian($transferCustodian);

        $this->assertTrue($this->condition->check($transferInformation));
    }
}
