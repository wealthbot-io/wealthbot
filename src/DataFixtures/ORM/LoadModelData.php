<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 04.07.13
 * Time: 17:56
 * To change this template use File | Settings | File Templates.
 */

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\CeModel;
use App\Entity\CeModelEntity;
use App\Entity\SecurityAssignment;

class LoadModelData extends AbstractFixture implements OrderedFixtureInterface
{
    private $models = [
        [
            'name' => 'Model 1',
            'parent_index' => 1,
            'risk_rating' => 1,
            'entities' => [
                [
                    'security_assignment_index' => 1,
                    'percent' => 30,
                ],
                [
                    'security_assignment_index' => 2,
                    'percent' => 70,
                ],
            ],
        ],
        [
            'name' => 'Model 2',
            'parent_index' => 1,
            'risk_rating' => 2,
            'entities' => [
                [
                    'security_assignment_index' => 1,
                    'percent' => 47,
                ],
                [
                    'security_assignment_index' => 2,
                    'percent' => 53,
                ],
            ],
        ],
        [
            'name' => 'Model 3',
            'parent_index' => 2,
            'risk_rating' => 1,
            'entities' => [
                [
                    'security_assignment_index' => 9,
                    'percent' => 55,
                ],
                [
                    'security_assignment_index' => 10,
                    'percent' => 45,
                ],
            ],
        ],
        [
            'name' => 'Model 4',
            'parent_index' => 2,
            'risk_rating' => 2,
            'entities' => [
                [
                    'security_assignment_index' => 9,
                    'percent' => 30,
                ],
                [
                    'security_assignment_index' => 10,
                    'percent' => 45,
                ],
                [
                    'security_assignment_index' => 11,
                    'percent' => 45,
                ],
            ],
        ],
        [
            'name' => 'Model 5',
            'parent_index' => 3,
            'risk_rating' => 1,
            'entities' => [
                [
                    'security_assignment_index' => 15,
                    'percent' => 10,
                ],
                [
                    'security_assignment_index' => 16,
                    'percent' => 35,
                ],
                [
                    'security_assignment_index' => 17,
                    'percent' => 55,
                ],
            ],
        ],
        [
            'name' => 'Model 6',
            'parent_index' => 3,
            'risk_rating' => 2,
            'entities' => [
                [
                    'security_assignment_index' => 15,
                    'percent' => 50,
                ],
                [
                    'security_assignment_index' => 16,
                    'percent' => 20,
                ],
                [
                    'security_assignment_index' => 17,
                    'percent' => 30,
                ],
            ],
        ],
    ];

    public function load(ObjectManager $manager)
    {
        foreach ($this->models as $index => $item) {
            /** @var CeModel $strategy */
            $strategy = $this->getReference('strategy-'.$item['parent_index']);

            $model = new CeModel();
            $model->setName($item['name']);
            $model->setParent($strategy);
            $model->setRiskRating($item['risk_rating']);
            $model->setType($strategy->getType());

            $manager->persist($model);
            $this->addReference('model-'.($index + 1), $model);

            foreach ($item['entities'] as $entityItem) {
                /** @var SecurityAssignment $securityAssignment */
                $securityAssignment = $this->getReference('model-security-assignment-'.$entityItem['security_assignment_index']);

                $entity = new CeModelEntity();
                $entity->setModel($model);
                $entity->setSubclass($securityAssignment->getSubclass());
                $entity->setAssetClass($securityAssignment->getSubclass()->getAssetClass());
                $entity->setSecurityAssignment($securityAssignment);
                $entity->setPercent($entityItem['percent']);

                $model->addModelEntity($entity);
            }
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 5;
    }
}
