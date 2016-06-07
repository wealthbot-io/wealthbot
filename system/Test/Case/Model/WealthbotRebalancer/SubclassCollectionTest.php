<?php

namespace Test\Model\WealthbotRebalancer;

use Model\WealthbotRebalancer\Security;
use Model\WealthbotRebalancer\Subclass;
use Model\WealthbotRebalancer\SubclassCollection;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();


class SubclassCollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  SubclassCollection */
    private $subclassCollection;

    public function setUp()
    {
        $this->subclassCollection = $this->getMockSubclassCollection(array(
            array(
                'id' => 1,
                'current_allocation' => 10,
                'target_allocation' => 40,
                'tolerance_band' => 14.2,
                'priority' => 2,
                'security' => array(
                    'id' => 11,
                    'amount' => 400.0
                ),
                'muni_security' => array(
                    'id' => 111,
                    'amount' => 600.0
                )
            ),
            array(
                'id' => 2,
                'current_allocation' => 90,
                'target_allocation' => 60,
                'tolerance_band' => 34.2,
                'priority' => 1,
                'security' => array(
                    'id' => 22,
                    'amount' => 8000.0,
                ),
                'muni_security' => array(
                    'id' => 222,
                    'amount' => 1000.0
                ),
            )
        ));
    }

    public function testIsOutOfBalance()
    {
        $this->assertTrue($this->subclassCollection->isOutOfBalance());

        $this->subclassCollection->get(1)->setCurrentAllocation(30);
        $this->subclassCollection->get(2)->setCurrentAllocation(70);

        $this->assertFalse($this->subclassCollection->isOutOfBalance());
    }

    public function testRebuildAllocations()
    {
        $this->subclassCollection->rebuildAllocations();

        /** @var Subclass $subclass1 */
        $subclass1 = $this->subclassCollection->get(1);
        /** @var Subclass $subclass2 */
        $subclass2 = $this->subclassCollection->get(2);

        $this->assertEquals(10, $subclass1->getCurrentAllocation());
        $this->assertEquals(90, $subclass2->getCurrentAllocation());

        /*-----------------------------------------*/
        $subclass1->getSecurity()->setAmount(180);
        $subclass1->getMuniSecurity()->setAmount(120);
        $subclass2->getSecurity()->setAmount(300);
        $subclass2->getMuniSecurity()->setAmount(400);

        $this->subclassCollection->rebuildAllocations();

        $this->assertEquals(30, $subclass1->getCurrentAllocation());
        $this->assertEquals(70, $subclass2->getCurrentAllocation());

        /*-----------------------------------------*/
        $subclass1->getSecurity()->setAmount(0);
        $subclass1->getMuniSecurity()->setAmount(0);
        $subclass2->getSecurity()->setAmount(0);
        $subclass2->getMuniSecurity()->setAmount(0);

        $this->subclassCollection->rebuildAllocations();

        $this->assertEquals(0, $subclass1->getCurrentAllocation());
        $this->assertEquals(0, $subclass2->getCurrentAllocation());
    }

    public function testFindMinAndMaxOob()
    {
        /** @var Subclass $subclass1 */
        $subclass1 = $this->subclassCollection->get(1);
        /** @var Subclass $subclass2 */
        $subclass2 = $this->subclassCollection->get(2);

        $minAndMaxData = array(
            'max' => array(
                'percent' => 30,
                'subclass' => $subclass2
            ),
            'min' => array(
                'percent' => -30,
                'subclass' => $subclass1
            )
        );

        $minAndMax = $this->subclassCollection->findMinAndMaxOob();

        $this->assertEquals($minAndMax, $minAndMaxData);

        //----------------------------------------------

        $subclass1->setCurrentAllocation(20);
        $subclass2->setCurrentAllocation(80);

        $minAndMax = $this->subclassCollection->findMinAndMaxOob();

        $minAndMaxData = array(
            'max' => array(
                'percent' => 20,
                'subclass' => $subclass2
            ),
            'min' => array(
                'percent' => -20,
                'subclass' => $subclass1
            )
        );

        $this->assertEquals($minAndMax, $minAndMaxData);
    }

    public function testGetMaxOobSubclass()
    {
        $expected = array(
            'percent' => 30,
            'subclass' => $this->subclassCollection->get(2)
        );

        $this->assertEquals($expected, $this->subclassCollection->getMaxOobSubclass());
    }

    public function testSortByOob()
    {
        $collection = $this->getMockSubclassCollection(array(
            array(
                'id' => 1,
                'current_allocation' => 20,
                'target_allocation' => 20,
            ),
            array(
                'id' => 2,
                'current_allocation' => 30,
                'target_allocation' => 45,
            ),
            array(
                'id' => 3,
                'current_allocation' => 50,
                'target_allocation' => 35,
            ),
        ));


        $sortedColledtion = $collection->sortByOob();
        $expectedIds = [3, 1, 2];
        $ids = [];

        foreach ($sortedColledtion as $item) {
            $ids[] = $item->getId();
        }

        $this->assertEquals($expectedIds, $ids);
    }

    public function testSortByPriority()
    {
        $subclass1 = $this->subclassCollection->get(1);
        $subclass2 = $this->subclassCollection->get(2);

        $this->subclassCollection->sortByPriority();

        $this->assertEquals(array(2 => $subclass2, 1 => $subclass1), $this->subclassCollection->toArray());
    }

    public function testDiff()
    {
        $diffCollection = $this->subclassCollection->diff($this->subclassCollection);
        $this->assertTrue($diffCollection->isEmpty());

        $diffCollection = $this->subclassCollection->diff($this->getMockSubclassCollection());
        $this->assertEquals($this->subclassCollection->get(1), $diffCollection->get(1));
        $this->assertEquals($this->subclassCollection->get(2), $diffCollection->get(2));

        $subclassCollection = $this->getMockSubclassCollection();
        $subclassCollection->add($this->subclassCollection->get(1));

        $diffCollection = $this->subclassCollection->diff($subclassCollection);

        $this->assertCount(1, $diffCollection);
        $this->assertEquals($this->subclassCollection->get(2), $diffCollection->first());
    }

    public function testGetOnePercentAmount()
    {
        $this->assertEquals(150, $this->subclassCollection->getOnePercentAmount());
    }

    /**
     * @param array $data
     * @param array $methods
     * @return SubclassCollection
     */
    private function getMockSubclassCollection(array $data = array(), array $methods = null)
    {
        /** @var SubclassCollection $subclassCollection */
        $subclassCollection = $this->getMockBuilder('Model\WealthbotRebalancer\SubclassCollection')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        if (empty($data)) {
            $subclassCollection->clear();
        } else {
            foreach ($data as $item) {
                $subclass = $this->getMockSubclass($item);
                $subclassCollection->add($subclass);
            }
        }

        return $subclassCollection;
    }

    /**
     * @param array $data
     * @return Subclass
     */
    private function getMockSubclass(array $data = array())
    {
        /** @var Subclass $mockSubclass */
        $mockSubclass = $this->getMock('Model\WealthbotRebalancer\Subclass', null);
        $mockSubclass->loadFromArray($data);

        return $mockSubclass;
    }
}