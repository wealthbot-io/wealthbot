<?php
//
//namespace Test\Model\WealthbotRebalancer;
//
//use Model\WealthbotRebalancer\AssetClass;
//use Model\WealthbotRebalancer\AssetClassCollection;
//
//require_once(__DIR__ . '/../../../../AutoLoader.php');
//\AutoLoader::registerAutoloader();
//
//
//class AssetClassCollectionTest extends \PHPUnit_Framework_TestCase
//{
//    /** @var  AssetClassCollection */
//    private $assetClassCollection;
//
//    public function setUp()
//    {
//        $assetClass1 = new AssetClass();
//        $assetClass1->setId(1);
//        $assetClass1->setCurrentAllocation(10.1);
//        $assetClass1->setTargetAllocation(40.5);
//        $assetClass1->setToleranceBand(14.2);
//
//        $assetClass2 = new AssetClass();
//        $assetClass2->setId(2);
//        $assetClass2->setCurrentAllocation(20.2);
//        $assetClass2->setTargetAllocation(30.5);
//        $assetClass2->setToleranceBand(34.2);
//
//        $this->assetClassCollection = $this->getMockBuilder('Model\WealthbotRebalancer\AssetClassCollection')
//            ->disableOriginalConstructor()
//            ->setMethods(null)
//            ->getMock();
//
//        $this->assetClassCollection->add($assetClass1);
//        $this->assetClassCollection->add($assetClass2);
//    }
//
//    public function testIsOutOfBalance()
//    {
//        $this->assertTrue($this->assetClassCollection->isOutOfBalance());
//
//        $this->assetClassCollection->remove(1);
//
//        $newAssetClass = new AssetClass();
//        $newAssetClass->setId(1);
//        $newAssetClass->setCurrentAllocation(7.5);
//        $newAssetClass->setTargetAllocation(8.3);
//        $newAssetClass->setToleranceBand(10.2);
//
//        $this->assetClassCollection->add($newAssetClass);
//
//        $this->assertFalse($this->assetClassCollection->isOutOfBalance());
//    }
//
//}