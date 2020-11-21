<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 14.06.13
 * Time: 16:00
 * To change this template use File | Settings | File Templates.
 */

namespace App\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\SecurityAssignment;
use App\Model\CeModelEntityInterface;
use App\Model\CeModelInterface;
use App\Entity\User;

class CeModelManager
{
    protected $objectManager;
    protected $class;
    protected $repository;

    /**
     * @var SecurityAssignment[]
     */
    protected $clonedSecurityAssignments;

    /**
     * Constructor.
     *
     * @param ObjectManager $om
     * @param $class
     */
    public function __construct(ObjectManager $om, $class)
    {
        $this->objectManager = $om;
        $this->repository = $om->getRepository($class);

        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();

        $this->clonedSecurityAssignments = [];
    }

    /**
     * Copy model with children, model entities, asset classes and subclasses.
     *
     * @param CeModelInterface $model
     * @param User             $owner
     *
     * @return CeModelInterface
     */
    public function copyForOwner(CeModelInterface $model, User $owner)
    {
        $assetClasses = [];
        $subclasses = [];

        $children = $this->getChildModels($model);

        if (!count($children)) {
            $clone = $this->copyModelWithRelationsForOwner($model, $owner, $assetClasses, $subclasses);
        } else {
            $clone = $model->getCopy();
            $clone->setOwner($owner);

            foreach ($children as $childModel) {
                if ($childModel->getIsDeleted()) {
                    continue;
                }

                /** @var CeModelInterface $cloneChildModel */
                $cloneChildModel = $this->copyModelWithRelationsForOwner($childModel, $owner, $assetClasses, $subclasses);
                $cloneChildModel->setParent($clone);

                $clone->addChildren($cloneChildModel);
            }
        }

        foreach ($assetClasses as $asset) {
            $asset->setModel($clone);
        }

        return $clone;
    }

    /**
     * Copy model with model entities, asset classes and subclasses.
     *
     * @param CeModelInterface $model
     * @param User             $owner
     * @param array            $assetClasses
     * @param array            $subclasses
     *
     * @return CeModelInterface
     */
    private function copyModelWithRelationsForOwner(CeModelInterface $model, User $owner, array &$assetClasses, array &$subclasses)
    {
        $clone = $model->getCopy();
        $clone->setOwner($owner);

        if ($owner->hasRole('ROLE_CLIENT')) {
            $commissions = $this->objectManager->getRepository('App\Entity\SecurityAssignment')->findMinAndMaxTransactionFeeForModel($model->getParentId());
            $commissions = array_filter($commissions);

            $clone->setCommissionMin(isset($commissions['minimum']) ? $commissions['minimum'] : 0);
            $clone->setCommissionMax(isset($commissions['maximum']) ? $commissions['maximum'] : 0);
        }

        /** @var $modelEntity CeModelEntityInterface */
        foreach ($model->getModelEntities() as $modelEntity) {
            /** @var $cloneModelEntity CeModelEntityInterface */
            $cloneModelEntity = $modelEntity->getCopy();

            $subclass = $modelEntity->getSubclass();

            if (!isset($subclasses[$subclass->getId()])) {
                $cloneSubclass = $subclass->getCopy();
                $cloneSubclass->setOwner($owner);

                $subclasses[$subclass->getId()] = $cloneSubclass;

                $assetClass = $subclass->getAssetClass();
                if (isset($assetClasses[$assetClass->getId()])) {
                    $cloneAssetClass = $assetClasses[$assetClass->getId()];
                } else {
                    $cloneAssetClass = $assetClass->getCopy();
                    $assetClasses[$assetClass->getId()] = $cloneAssetClass;
                }

                $cloneAssetClass->addSubclasse($cloneSubclass);
                $cloneSubclass->setAssetClass($cloneAssetClass);
            } else {
                $cloneSubclass = $subclasses[$subclass->getId()];
            }

            if ($securityAssignment = $modelEntity->getSecurityAssignment()) {
                $found = null;

                //Searching one SA that has needed subclass and security.
                foreach ($this->clonedSecurityAssignments as $sa) {
                    if ($sa->getSubclass() === $cloneSubclass
                        && $sa->getSecurity() === $securityAssignment->getSecurity()
                    ) {
                        $found = $sa;
                        break;
                    }
                }

                if (!$found) {
                    $found = $this->objectManager->getRepository('App\Entity\SecurityAssignment')->findOneBy([
                            'security' => $securityAssignment->getSecurity(),
                            'subclass' => $cloneSubclass,
                        ]);
                }

                if (!$found) {
                    $newSecurityAssignment = new SecurityAssignment();

                    //create SecurityAssignment
                    $newSecurityAssignment->setIsPreferred($securityAssignment->getIsPreferred());
                    $newSecurityAssignment->setModel($clone);
                    $newSecurityAssignment->setMuniSubstitution($securityAssignment->getMuniSubstitution());
                    $newSecurityAssignment->setSecurity($securityAssignment->getSecurity());
                    $newSecurityAssignment->setSecurityTransaction($securityAssignment->getSecurityTransaction());
                    $newSecurityAssignment->setSubclass($cloneSubclass);

                    $newSecurityAssignment->setModelId($clone->getId());
                    $newSecurityAssignment->setSecurityId($securityAssignment->getSecurity()->getId());
                    if ($cloneSubclass) {
                        $newSecurityAssignment->setSubclassId($cloneSubclass->getId());
                    }

                    if ($cloneSubclass) {
                        $cloneSubclass->addSecurityAssignment($newSecurityAssignment);
                    }

                    $this->clonedSecurityAssignments[] = $newSecurityAssignment;
                    $found = $newSecurityAssignment;
                }

                if ($found) {
                    $cloneModelEntity->setSecurityAssignment($found);
                }
            }

            $cloneModelEntity->setModel($clone);
            $cloneModelEntity->setSubclass($subclasses[$subclass->getId()]);

            $clone->addModelEntity($cloneModelEntity);
        }

        return $clone;
    }

    /**
     * Create custom model.
     *
     * @param User $owner
     *
     * @return CeModelInterface
     */
    public function createCustomModel(User $owner)
    {
        /** @var CeModelInterface $model */
        $model = new $this->class();

        $model->setType(CeModelInterface::TYPE_CUSTOM);
        $model->setOwner($owner);
        $model->setName('RIA_'.$owner->getId());

        return $model;
    }

    /**
     * Create strategy model.
     *
     * @return CeModelInterface
     */
    public function createStrategyModel()
    {
        /** @var CeModelInterface $model */
        $model = new $this->class();

        $model->setType(CeModelInterface::TYPE_STRATEGY);

        return $model;
    }

    /**
     * Create child for model.
     *
     * @param CeModelInterface $parent
     *
     * @return CeModelInterface
     */
    public function createChild(CeModelInterface $parent)
    {
        /** @var CeModelInterface $model */
        $model = new $this->class();

        $model->setType($parent->getType());
        $model->setParent($parent);
        $model->setOwner($parent->getOwner());
        $model->setAssumption($parent->getAssumption());

        return $model;
    }

    /**
     * Find one model by criteria.
     *
     * @param array $criteria
     *
     * @return CeModelInterface
     */
    public function findCeModelBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Find models by criteria.
     *
     * @param array $criteria
     *
     * @return mixed
     */
    public function findCeModelsBy(array $criteria)
    {
        return $this->repository->findBy($criteria);
    }

    /**
     * Find model by slug and id of owner.
     *
     * @param string $slug
     * @param int    $ownerId
     *
     * @return CeModelInterface
     */
    public function findCeModelBySlugAndOwnerId($slug, $ownerId = null)
    {
        if (!$ownerId) {
            return $this->findCeModelBy(
                [
                    'slug' => $slug,
                    'type' => CeModelInterface::TYPE_STRATEGY,
                    'ownerId' => null,
                    'isDeleted' => 0,
                ]
            );
        }

        return $this->findCeModelBy(['slug' => $slug, 'ownerId' => $ownerId, 'isDeleted' => 0]);
    }

    /**
     * Get child models by parent model.
     *
     * @param CeModelInterface $parent
     *
     * @return mixed
     */
    public function getChildModels(CeModelInterface $parent)
    {
        $ownerId = $parent->getOwnerId() ? $parent->getOwnerId() : ($parent->getOwner() ? $parent->getOwner()->getId() : null);

        return $this->getChildModelsByParentIdAndOwnerId($parent->getId(), $ownerId);
    }

    /**
     * Find child models by id of parent model.
     *
     * @param int $parentId
     *
     * @return mixed
     */
    public function getChildModelsByParentId($parentId)
    {
        return $this->findCeModelsBy(['parentId' => $parentId, 'isDeleted' => 0]);
    }

    /**
     * Find child models by id of parent model and id of owner.
     *
     * @param int $parentId
     * @param int $ownerId
     *
     * @return mixed
     */
    public function getChildModelsByParentIdAndOwnerId($parentId, $ownerId)
    {
        return $this->findCeModelsBy(['parentId' => $parentId, 'ownerId' => $ownerId, 'isDeleted' => 0]);
    }

    /**
     * Find strategy models without id of parent and id of owner.
     *
     * @return mixed
     */
    public function getAdminStrategyParentModels()
    {
        return $this->findCeModelsBy(
            [
                'type' => CeModelInterface::TYPE_STRATEGY,
                'parentId' => null,
                'ownerId' => null,
                'isDeleted' => 0,
            ]
        );
    }

    /**
     * Mark model as deleted.
     *
     * @param CeModelInterface $model
     */
    public function deleteModel(CeModelInterface $model)
    {
        $model->setIsDeleted(true);
        $this->objectManager->persist($model);

        $childModels = $this->getChildModels($model);

        /** @var CeModelInterface $child */
        foreach ($childModels as $child) {
            $riskRating = $child->getRiskRating();

            if ($riskRating > $model->getRiskRating()) {
                $child->setRiskRating($riskRating - 1);
                $this->objectManager->persist($child);
            }
        }

        $this->objectManager->flush();
    }
}
