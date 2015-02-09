<?php

namespace Test\Model\WealthbotRebalancer;

use Model\WealthbotRebalancer\AssetClass;
use Model\WealthbotRebalancer\Subclass;
use Model\WealthbotRebalancer\SubclassCollection;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();


class AssetClassTest extends \PHPUnit_Framework_TestCase
{
    /** @var  AssetClass */
    private $assetClass;

    public function setUp()
    {
        $data = array(
            'id' => 5,
            'currentAllocation' => 10.5,
            'targetAllocation' => 20.2,
            'toleranceBand' => 39.7,
            'subclasses' => array(
                array(
                    'id' => 79
                ),
                array(
                    'id' => 100
                )
            )
        );

        $this->assetClass = $this->getMockBuilder('Model\WealthbotRebalancer\AssetClass')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $this->assetClass->loadFromArray($data);
    }

    public function testLoadFromArray()
    {
        /** @var AssetClass $assetClass */
        $assetClass = $this->assetClass;

        /** @var SubclassCollection $subclasses */
        $subclasses = $assetClass->getSubclasses();

        $this->assertEquals(10.5, $assetClass->getCurrentAllocation());
        $this->assertEquals(20.2, $assetClass->getTargetAllocation());
        $this->assertEquals(39.7, $assetClass->getToleranceBand());

        $this->assertCount(2, $subclasses);
        $this->assertEquals(79, $subclasses[79]->getId());
        $this->assertEquals(100, $subclasses[100]->getId());
    }


    public function testGetCurrentAllocation()
    {
        $this->assertEquals(10.5, $this->assetClass->getCurrentAllocation());
    }

    public function testSetCurrentAllocation()
    {
        $this->assetClass->setCurrentAllocation(78.5);
        $this->assertEquals(78.5, $this->assetClass->getCurrentAllocation());
    }

    public function testGetTargetAllocation()
    {
        $this->assertEquals(20.2, $this->assetClass->getTargetAllocation());
    }

    public function testSetTargetAllocation()
    {
        $this->assetClass->setTargetAllocation(79.3);
        $this->assertEquals(79.3, $this->assetClass->getTargetAllocation());
    }

    public function testGetToleranceBand()
    {
        $this->assertEquals(39.7, $this->assetClass->getToleranceBand());
    }

    public function testSetToleranceBand()
    {
        $this->assetClass->setToleranceBand(46.2);
        $this->assertEquals(46.2, $this->assetClass->getToleranceBand());
    }

    public function testGetSubclasses()
    {
        $subclasses = $this->assetClass->getSubclasses();

        $this->assertCount(2, $subclasses);
        $this->assertEquals(79, $subclasses[79]->getId());
        $this->assertEquals(100, $subclasses[100]->getId());
    }

    public function testSetSubclasses()
    {
        $subclassCollection = new SubclassCollection();

        $subclass1 = new Subclass();
        $subclass1->setId(45);
        $subclassCollection->add($subclass1);

        $subclass2 = new Subclass();
        $subclass2->setId(50);
        $subclassCollection->add($subclass2);

        $subclass3 = new Subclass();
        $subclass3->setId(55);
        $subclassCollection->add($subclass3);

        $this->assetClass->setSubclasses($subclassCollection);

        $subclasses = $this->assetClass->getSubclasses();

        $this->assertCount(3, $subclasses);
        $this->assertEquals(45, $subclasses[45]->getId());
        $this->assertEquals(50, $subclasses[50]->getId());
        $this->assertEquals(55, $subclasses[55]->getId());
    }

    public function testAddSubclass()
    {
        $newSubclass = new Subclass();
        $newSubclass->setId(56);

        $this->assetClass->addSubclass($newSubclass);

        $subclasses = $this->assetClass->getSubclasses();

        $this->assertCount(3, $subclasses);
        $this->assertEquals(79, $subclasses[79]->getId());
        $this->assertEquals(100, $subclasses[100]->getId());
        $this->assertEquals(56, $subclasses[56]->getId());
    }

    public function testCalcOOB()
    {
        $this->assertEquals(-9.7, $this->assetClass->calcOOB());

        $this->assetClass->setCurrentAllocation(70.5);
        $this->assetClass->setTargetAllocation(50.5);

        $this->assertEquals(20.0, $this->assetClass->calcOOB());
    }


}