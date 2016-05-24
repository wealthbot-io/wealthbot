<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 04.09.13
 * Time: 13:54
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Tests\Docusign;

use Wealthbot\ClientBundle\Docusign\TransferInformationPolicyCondition;
use Wealthbot\ClientBundle\Entity\TransferInformation;

class TransferInformationPolicyConditionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  TransferInformationPolicyCondition */
    private $condition;

    protected function setUp()
    {
        $this->condition = new TransferInformationPolicyCondition();
    }

    public function testDocusignAllowed()
    {
        $transferInformation = new TransferInformation();
        $transferInformation->setIsIncludePolicy(true);

        $this->assertFalse($this->condition->check($transferInformation));
    }

    public function testDocusignNotAllowed()
    {
        $transferInformation = new TransferInformation();

        $this->assertTrue($this->condition->check($transferInformation));

        $transferInformation->setIsIncludePolicy(false);
        $this->assertTrue($this->condition->check($transferInformation));
    }
}
