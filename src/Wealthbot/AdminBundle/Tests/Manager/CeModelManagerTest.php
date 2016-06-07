<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 24.06.13
 * Time: 16:45
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Tests\Manager;

use Doctrine\ORM\Mapping\ClassMetadata;
use Wealthbot\AdminBundle\Entity\AssetClass;
use Wealthbot\AdminBundle\Entity\Subclass;
use Wealthbot\AdminBundle\Manager\CeModelManager;
use Wealthbot\AdminBundle\Model\CeModel;
use Wealthbot\AdminBundle\Model\CeModelEntity;

class CeModelManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  CeModelManager */
    private $manager;

    /** @var  array */
    private $modelObjects;

    public function setUp()
    {
        $class = '\Wealthbot\AdminBundle\Entity\CeModel';
        $this->manager = new CeModelManager($this->getMockEntityManager($class), $class);
        $this->modelObjects = $this->getCeModelObjects();
    }

    public function testCopyForOwner()
    {
        $mockOwner2 = $this->getMockUser(2);

        $model = $this->modelObjects[2];
        $childModel = $this->modelObjects[3];

        $entity = new CeModelEntity();
        $assetClass = new AssetClass();
        $subclass = new Subclass();

        $assetClass->setModel($model);
        $assetClass->addSubclasse($subclass);
        $subclass->setAssetClass($assetClass);
        $entity->setSubclass($subclass);
        $childModel->addModelEntity($entity);

        $copiedModel = $this->manager->copyForOwner($model, $mockOwner2);

        $this->assertSame(5, $copiedModel->getRiskRating());
        $this->assertSame(2, $copiedModel->getOwner()->getId());
        $this->assertCount(1, $copiedModel->getChildren());

        foreach ($copiedModel->getChildren() as $child) {
            $this->assertSame(2, $child->getOwner()->getId());
            $this->assertCount(1, $child->getModelEntities());

            foreach ($child->getModelEntities() as $entity) {
                $this->assertNotNull($entity->getSubclass());
                $this->assertNotNull($entity->getSubclass()->getAssetClass());
            }
        }
    }

    public function testCreateCustomModel()
    {
        $mockOwner = $this->getMockUser(5);
        $customModel = $this->manager->createCustomModel($mockOwner);

        $this->assertSame(CeModel::TYPE_CUSTOM, $customModel->getType());
        $this->assertSame(5, $customModel->getOwner()->getId());
        $this->assertSame('RIA_5', $customModel->getName());
    }

    public function testCreateStrategyModel()
    {
        $strategyModel = $this->manager->createStrategyModel();

        $this->assertSame(CeModel::TYPE_STRATEGY, $strategyModel->getType());
    }

    public function testCreateChild()
    {
        $mockOwner = $this->getMockUser(1);
        $customModel = $this->manager->createCustomModel($mockOwner);

        $child = $this->manager->createChild($customModel);

        $this->assertSame($customModel->getType(), $child->getType());
        $this->assertSame('RIA_1', $child->getParent()->getName());
        $this->assertSame($customModel->getOwner()->getId(), $child->getOwner()->getId());
        $this->assertSame($customModel->getAssumption(), $child->getAssumption());
    }

    public function testFindCeModelBy()
    {
        $model = $this->manager->findCeModelBy(['name' => 'Model1', 'owner_id' => 1]);

        $this->assertSame('Model1', $model->getName());
    }

    public function testFindCeModelsBy()
    {
        $models = $this->manager->findCeModelsBy(['owner_id' => 1]);

        $this->assertCount(3, $models);
    }

    public function testFindCeModelBySlugAndOwnerId()
    {
        $model = $this->manager->findCeModelBySlugAndOwnerId('model1', 1);
        $adminModel = $this->manager->findCeModelBySlugAndOwnerId('admin_model_1');
        if ($model) {
            $this->assertSame('Model1', $model->getName());
        }

        if ($adminModel) {
            $this->assertSame('Admin model 1', $adminModel->getName());
        }
    }

    public function testGetChildModelsByParentId()
    {
        $childModels = $this->manager->getChildModelsByParentId(31);

        $this->assertCount(1, $childModels);
        $this->assertSame('Child model 1', $childModels[0]->getName());
    }

    public function testGetAdminStrategyParentModels()
    {
        $models = $this->manager->getAdminStrategyParentModels();

        $this->assertCount(2, $models);
    }

    public function testDeleteModel()
    {
        $models = $this->manager->getChildModelsByParentId(31);
        if (count($models) > 0) {
            $model = $models[0];

            $this->manager->deleteModel($model);

            $updatedModels = $this->manager->getChildModelsByParentId(31);
            $this->assertCount(0, $updatedModels);
        }
    }

    public function findBy(array $criteria)
    {
        $objects = $this->modelObjects;

        foreach ($criteria as $field => $value) {
            $tmp = explode('_', $field);
            $tmp = array_map(function ($item) {
                    return ucfirst($item);
                }, $tmp);

            $method = 'get'.implode('', $tmp);

            foreach ($objects as $key => $object) {
                if (true === method_exists($object, $method)) {
                    $methodResult = $object->$method();

                    if ($methodResult !== $value) {
                        unset($objects[$key]);
                    }
                }
            }
        }

        return array_values($objects);
    }

    public function findOneBy(array $criteria)
    {
        $objects = $this->findBy($criteria);

        if (empty($objects)) {
            return;
        }

        return $objects[0];
    }

    private function getMockEntityManager($class)
    {
        $mockMetadata = new ClassMetadata($class);

        $mockEm = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->setMethods(['getClassMetadata', 'getRepository', 'persist', 'flush'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockEm->expects($this->once())
            ->method('getClassMetadata')
            ->will($this->returnValue($mockMetadata));

        $mockEm->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->getMockRepository()));

        return $mockEm;
    }

    private function getMockRepository()
    {
        $mockRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->setMethods(['findBy', 'findOneBy'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockRepository->expects($this->any())
            ->method('findBy')
            ->will($this->returnCallback([$this, 'findBy']));

        $mockRepository->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnCallback([$this, 'findOneBy']));

        return $mockRepository;
    }

    private function getCeModelObjects()
    {
        $adminModel1 = new CeModel();
        $adminModel1->setName('Admin model 1');
        $adminModel1->setSlug('admin_model_1');
        $adminModel1->setRiskRating(1);
        $adminModel1->setType(CeModel::TYPE_STRATEGY);

        $adminModel2 = new CeModel();
        $adminModel2->setName('Admin model 2');
        $adminModel2->setSlug('admin_model_2');
        $adminModel2->setRiskRating(2);
        $adminModel2->setType(CeModel::TYPE_STRATEGY);

        $model1 = $this->getMock('Wealthbot\AdminBundle\Entity\CeModel', ['getId']);
        $model1->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(31));

        $model1->setName('Model1');
        $model1->setSlug('model1');
        $model1->setOwnerId(1);
        $model1->setRiskRating(5);
        $model1->setType(CeModel::TYPE_STRATEGY);

        $child1 = new CeModel();
        $child1->setName('Child model 1');
        $child1->setSlug('child_model_1');
        $child1->setOwnerId(1);
        $child1->setParentId(31);

        $model2 = new CeModel();
        $model2->setName('Model2');
        $model2->setSlug('model2');
        $model2->setOwnerId(1);
        $model2->setRiskRating(7);

        $model3 = new CeModel();
        $model3->setName('Model3');
        $model3->setSlug('model3');
        $model3->setOwnerId(6);
        $model3->setRiskRating(2);

        $objects = [];
        $objects[] = $adminModel1;
        $objects[] = $adminModel2;
        $objects[] = $model1;
        $objects[] = $child1;
        $objects[] = $model2;
        $objects[] = $model3;

        return $objects;
    }

    private function getMockUser($id)
    {
        $owner = $this->getMock('Wealthbot\UserBundle\Entity\User', ['getId']);

        $owner->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $owner;
    }
}
