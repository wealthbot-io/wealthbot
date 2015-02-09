<?php

namespace Test\Model\WealthbotRebalancer;

use Model\WealthbotRebalancer\ArrayCollection;
use Model\WealthbotRebalancer\Base;

require_once(__DIR__ . '/../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class ArrayCollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ArrayCollection */
    private $arrayCollection;

    private $data = array();

    public function setUp()
    {
        $obj1 = new Base();
        $obj1->setId(10);

        $obj2 = new Base();
        $obj2->setId(20);

        $this->data = array($obj1, $obj2);

        $this->arrayCollection = $this->getMockBuilder('Model\WealthbotRebalancer\ArrayCollection')
            ->setConstructorArgs(array($this->data))
            ->setMethods(null)
            ->getMock();
    }

    public function testToArray()
    {
        $this->assertEquals($this->data, $this->arrayCollection->toArray());
    }

    public function testFirst()
    {
        $this->assertEquals($this->data[0], $this->arrayCollection->first());
    }

    public function testLast()
    {
        $this->assertEquals($this->data[1], $this->arrayCollection->last());
    }

    public function testKey()
    {
        $this->assertEquals(0, $this->arrayCollection->key());

        $this->arrayCollection->next();

        $this->assertEquals(1, $this->arrayCollection->key());
    }

    public function testNext()
    {
        $this->assertEquals($this->data[1], $this->arrayCollection->next());

        $this->arrayCollection->next();
        $this->assertNull($this->arrayCollection->key());
    }

    public function testPrev()
    {
        $this->arrayCollection->next();
        $this->assertEquals(1, $this->arrayCollection->key());

        $this->arrayCollection->prev();
        $this->assertEquals(0, $this->arrayCollection->key());

        $this->arrayCollection->prev();
        $this->assertNull($this->arrayCollection->key());
    }

    public function testCurrent()
    {
        $this->assertEquals($this->data[0], $this->arrayCollection->current());
        $this->arrayCollection->next();
        $this->assertEquals($this->data[1], $this->arrayCollection->current());
    }

    public function testRemove()
    {
        $this->assertNull($this->arrayCollection->remove(3000));

        $removedElement = $this->data[0];
        unset($this->data[0]);
        $this->assertEquals($removedElement, $this->arrayCollection->remove(0));
    }

    public function testRemoveElement()
    {
        $notExistObj = new Base();
        $notExistObj->setId(78979);

        $this->assertFalse($this->arrayCollection->removeElement($notExistObj));

        $this->arrayCollection->removeElement($this->data[1]);

        $this->assertEquals(array($this->data[0]), $this->arrayCollection->toArray());
    }

    public function testOffsetExist()
    {
        $this->assertTrue($this->arrayCollection->offsetExists(1));

        $this->assertFalse($this->arrayCollection->offsetExists('not_exists_key'));
    }

    public function testOffsetGet()
    {
        $this->assertNull($this->arrayCollection->offsetGet('not_exists_key'));

        $this->assertEquals($this->data[1], $this->arrayCollection->offsetGet(1));
    }

    public function testOffsetSet()
    {
        $obj = new Base();
        $obj->setId(500);

        $this->data[1] = $obj;

        $this->arrayCollection->offsetSet(1, $obj);
        $this->assertEquals($this->data, $this->arrayCollection->toArray());

        $this->data[50] = $obj;
        $this->arrayCollection->offsetSet(50, $obj);
        $this->assertEquals($this->data, $this->arrayCollection->toArray());
    }

    public function testOffsetUnset()
    {
        $this->assertNull($this->arrayCollection->offsetUnset(3000));

        $removedElement = $this->data[0];
        unset($this->data[0]);
        $this->assertEquals($removedElement, $this->arrayCollection->remove(0));
    }

    public function testContainsKey()
    {
        $this->assertTrue($this->arrayCollection->containsKey(1));

        $this->assertFalse($this->arrayCollection->containsKey('not_exists_key'));
    }

    public function testContains()
    {
        $this->assertTrue($this->arrayCollection->contains($this->data[0]));

        $notExistObj = new Base();
        $notExistObj->setId(78979);
        $this->assertFalse($this->arrayCollection->contains($notExistObj));
    }

    public function testExists()
    {
        $existClosure = function ($key, $value) {
            if ($value instanceof Base && $key === 1) {
                return true;
            } else {
                return false;
            }
        };

        $this->assertTrue($this->arrayCollection->exists($existClosure));

        $notExistClosure = function ($key, $value) {
            if ($value instanceof Base && $key === 67) {
                return true;
            } else {
                return false;
            }
        };

        $this->assertFalse($this->arrayCollection->exists($notExistClosure));
    }

    public function testIndexOf()
    {
        $notExistObj = new Base();
        $notExistObj->setId(78979);

        $this->assertFalse($this->arrayCollection->indexOf($notExistObj));

        $this->assertEquals(0, $this->arrayCollection->indexOf($this->data[0]));
    }

    public function testGet()
    {
        $this->assertNull($this->arrayCollection->offsetGet('not_exists_key'));

        $this->assertEquals($this->data[1], $this->arrayCollection->offsetGet(1));
    }

    public function testGetKeys()
    {
        $this->assertEquals(array(0, 1), $this->arrayCollection->getKeys());
    }

    public function testGetValues()
    {
        $obj1 = new Base();
        $obj1->setId(10);

        $obj2 = new Base();
        $obj2->setId(20);

        $this->assertEquals(array($obj1, $obj2), $this->arrayCollection->getValues());
    }

    public function testCount()
    {
        $this->assertEquals(2, $this->arrayCollection->count());

        $newObj = new Base();
        $newObj->setId(65);

        $this->arrayCollection->add($newObj);
        $this->assertEquals(3, $this->arrayCollection->count());
    }

    public function testSet()
    {
        $obj = new Base();
        $obj->setId(500);

        $this->data[1] = $obj;

        $this->arrayCollection->offsetSet(1, $obj);
        $this->assertEquals($this->data, $this->arrayCollection->toArray());

        $this->data[50] = $obj;
        $this->arrayCollection->offsetSet(50, $obj);
        $this->assertEquals($this->data, $this->arrayCollection->toArray());
    }

    public function testAdd()
    {
        $newObj = new Base();

        $this->arrayCollection->add($newObj);
        $this->assertEquals($newObj, $this->arrayCollection->get(2));

        $newObj->setId(65);
        $this->arrayCollection->add($newObj);
        $this->assertEquals($newObj, $this->arrayCollection->get(65));

        $this->arrayCollection->add($newObj, 500);
        $this->assertEquals($newObj, $this->arrayCollection->get(500));
    }

    public function testIsEmpty()
    {
        $this->assertFalse($this->arrayCollection->isEmpty());

        $this->arrayCollection->clear();

        $this->assertTrue($this->arrayCollection->isEmpty());
    }

    public function testGetIterator()
    {
        $iterator = new \ArrayIterator($this->data);

        $this->assertEquals($iterator, $this->arrayCollection->getIterator());
    }

    public function testToString()
    {
        $this->assertEquals("Model\\WealthbotRebalancer\\ArrayCollection@".spl_object_hash($this->arrayCollection), (string) $this->arrayCollection);
    }

    public function testClear()
    {
        $this->arrayCollection->clear();

        $this->assertEmpty($this->arrayCollection->toArray());
    }

    public function testSlice()
    {
        $this->assertEquals(array(), $this->arrayCollection->slice(0, 0));
        $this->assertEquals($this->data, $this->arrayCollection->slice(0));
        $this->assertEquals(array($this->data[0]), $this->arrayCollection->slice(0, 1));
        $this->assertEquals(array($this->data[0]), $this->arrayCollection->slice(0, -1));
        $this->assertEquals($this->data, $this->arrayCollection->slice(0, 2));

        $this->assertEquals(array(), $this->arrayCollection->slice(1, 0));
        $this->assertEquals(array(1 => $this->data[1]), $this->arrayCollection->slice(1));
        $this->assertEquals(array(1 => $this->data[1]), $this->arrayCollection->slice(1, 1));
        $this->assertEquals(array(1 => $this->data[1]), $this->arrayCollection->slice(1, 2));
        $this->assertEquals(array(), $this->arrayCollection->slice(1, -1));

        $this->assertEquals(array(1 => $this->data[1]), $this->arrayCollection->slice(-1));
        $this->assertEquals(array(1 => $this->data[1]), $this->arrayCollection->slice(-1, 1));

        $this->assertEquals(array(), $this->arrayCollection->slice(2));
    }
}