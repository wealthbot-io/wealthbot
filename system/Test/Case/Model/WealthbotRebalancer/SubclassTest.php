<?php

namespace Test\Model\WealthbotRebalancer;

use Model\WealthbotRebalancer\Security;
use Model\WealthbotRebalancer\Subclass;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();


class SubclassTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Subclass */
    private $subclass;

    public function setUp()
    {
        $data = array(
            'id' => 5,
            'currentAllocation' => 10.5,
            'targetAllocation' => 20.2,
            'toleranceBand' => 39.7,
            'priority' => 2,
            'security' => array(
                'id' => 10,
                'amount' => 1000
            ),
            'tax_loss_harvesting' => array(
                'id' => 5
            ),
            'muni_security' => array(
                'id' => 30,
                'amount' => 500
            ),
            'account_type' => Subclass::ACCOUNT_TYPE_ROTH_IRA
        );

        $this->subclass = $this->getMockBuilder('Model\WealthbotRebalancer\Subclass')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->subclass->loadFromArray($data);
    }

    public function testLoadFromArray()
    {
        /** @var Subclass $subclass */
        $subclass = $this->subclass;

        $this->assertEquals(10.5, $subclass->getCurrentAllocation());
        $this->assertEquals(20.2, $subclass->getTargetAllocation());
        $this->assertEquals(39.7, $subclass->getToleranceBand());
        $this->assertEquals(2, $subclass->getPriority());
        $this->assertEquals(10, $subclass->getSecurity()->getId());
        $this->assertEquals(30, $subclass->getMuniSecurity()->getId());
    }

    public function testGetCurrentAllocation()
    {
        $this->assertEquals(10.5, $this->subclass->getCurrentAllocation());
    }

    public function testSetCurrentAllocation()
    {
        $this->subclass->setCurrentAllocation(78.5);
        $this->assertEquals(78.5, $this->subclass->getCurrentAllocation());
    }

    public function testGetTargetAllocation()
    {
        $this->assertEquals(20.2, $this->subclass->getTargetAllocation());
    }

    public function testSetTargetAllocation()
    {
        $this->subclass->setTargetAllocation(79.3);
        $this->assertEquals(79.3, $this->subclass->getTargetAllocation());
    }

    public function testGetToleranceBand()
    {
        $this->assertEquals(39.7, $this->subclass->getToleranceBand());
    }

    public function testSetToleranceBand()
    {
        $this->subclass->setToleranceBand(46.2);
        $this->assertEquals(46.2, $this->subclass->getToleranceBand());
    }

    public function testGetPriority()
    {
        $this->assertEquals(2, $this->subclass->getPriority());
    }

    public function testSetPriority()
    {
        $this->subclass->setPriority(3);
        $this->assertEquals(3, $this->subclass->getPriority());
    }

    public function testGetSecurity()
    {
        $this->assertEquals(10, $this->subclass->getSecurity()->getId());
    }

    public function testSetSecurity()
    {
        $security = new Security();
        $security->setId(11);

        $this->subclass->setSecurity($security);

        $this->assertEquals(11, $this->subclass->getSecurity()->getId());
    }

    public function testGetMuniSecurity()
    {
        $this->assertEquals(30, $this->subclass->getMuniSecurity()->getId());
    }

    public function testSetMuniSecurity()
    {
        $security = new Security();
        $security->setId(45);

        $this->subclass->setMuniSecurity($security);

        $this->assertEquals(45, $this->subclass->getMuniSecurity()->getId());
    }

    public function testCalcOOB()
    {
        $this->assertEquals(-9.7, $this->subclass->calcOOB());

        $this->subclass->setCurrentAllocation(70.5);
        $this->subclass->setTargetAllocation(50.5);

        $this->assertEquals(20.0, $this->subclass->calcOOB());
    }

    public function testGetTaxLossHarvesting()
    {
        $taxLossHarvesting = new Security();
        $taxLossHarvesting->setId(5);

        $this->assertEquals($taxLossHarvesting, $this->subclass->getTaxLossHarvesting());
    }

    public function testSetTaxLossHarvesting()
    {
        $taxLossHarvesting = new Security();
        $taxLossHarvesting->setId(12);

        $this->subclass->setTaxLossHarvesting($taxLossHarvesting);
        $this->assertEquals(12, $this->subclass->getTaxLossHarvesting()->getId());
    }

    public function testHasTlhFund()
    {
        $this->assertTrue($this->subclass->hasTlhFund());

        $this->subclass->setTaxLossHarvesting(null);
        $this->assertFalse($this->subclass->hasTlhFund());
    }

    public function testGetAccountType()
    {
        $this->assertEquals(Subclass::ACCOUNT_TYPE_ROTH_IRA, $this->subclass->getAccountType());
    }

    public function testSetAccountType()
    {
        $this->subclass->setAccountType(Subclass::ACCOUNT_TYPE_TAXABLE);
        $this->assertEquals(Subclass::ACCOUNT_TYPE_TAXABLE, $this->subclass->getAccountType());
    }

    public function testHasMuniFund()
    {
        $this->assertTrue($this->subclass->hasMuniFund());

        $newSubclass = new Subclass();
        $this->assertFalse($newSubclass->hasMuniFund());
    }

    public function testGetTotalAmount()
    {
        $this->assertEquals(1500, $this->subclass->getTotalAmount());

        /** @var Subclass $emptySubclass */
        $emptySubclass = $this->getMock('Model\WealthbotRebalancer\Subclass', null);
        $this->assertEquals(0, $emptySubclass->getTotalAmount());

        /** @var Subclass $subclassWithSecurity */
        $subclassWithSecurity = $this->getMock('Model\WealthbotRebalancer\Subclass', null);
        $security = new Security();
        $security->setAmount(200);
        $subclassWithSecurity->setSecurity($security);
        $this->assertEquals(200, $subclassWithSecurity->getTotalAmount());

        /** @var Subclass $subclassWithMuni */
        $subclassWithMuni = $this->getMock('Model\WealthbotRebalancer\Subclass', null);
        $muni = new Security();
        $muni->setAmount(722);
        $subclassWithMuni->setMuniSecurity($muni);
        $this->assertEquals(722, $subclassWithMuni->getTotalAmount());
    }
}