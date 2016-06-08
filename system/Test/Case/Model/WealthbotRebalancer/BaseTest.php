<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 13.02.14
 * Time: 12:33
 */

namespace Test\Model\WealthbotRebalancer;

use Model\WealthbotRebalancer\Base;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /** @var  Base */
    private $object;

    public function setUp()
    {
        $this->object = $this->getMockBuilder('Model\WealthbotRebalancer\Base')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
    }

    public function testGetId()
    {
        $this->assertNull($this->object->getId());
    }

    public function testSetId()
    {
        $this->object->setId(1);
        $this->assertEquals(1, $this->object->getId());
    }

//    public function testLoadFromArray()
//    {
//        $this->object->loadFromArray(array('id' => 13));
//        $this->assertEquals(13, $this->object->getId());
//    }

    public function testLoadFromArrayException()
    {
        $object = $this->getMockBaseObject(array('getRelations'));

        $object->expects($this->any())
            ->method('getRelations')
            ->will($this->returnValue(array('securityRepository' => 'Model\WealthbotRebalancer\Repository\SecurityRepository')));

        $this->setExpectedException('Exception', 'Invalid relation object. Relation object must be instance of Model\WealthbotRebalancer\Base, Model\WealthbotRebalancer\Repository\SecurityRepository given.');
        $object->loadFromArray(array(
            'securityRepository' => array('id' => 13)
        ));

    }

    /**
     * @param array $methods
     * @return Base
     */
    private function getMockBaseObject(array $methods = array())
    {
        /** @var Base $object */
        $object = $this->getMockBuilder('Model\WealthbotRebalancer\Base')
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        return $object;
    }

}
 