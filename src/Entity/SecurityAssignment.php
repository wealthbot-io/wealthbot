<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use App\Model\CeModelInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Class SecurityAssignment
 * @package App\Entity
 */
class SecurityAssignment
{
    public $fund_symbol;
    public $asset_class_id;

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $subclass_id;

    /**
     * @param \App\Entity\Subclass
     */
    private $subclass;

    /**
     * @var bool
     */
    private $is_preferred = false;

    /**
     * @var User
     */
    private $ria_user_id;

    /**
     * @var int
     */
    private $security_id;

    /**
     * @param \App\Entity\Security
     */
    private $security;

    /**
     * @param \App\Entity\AccountOutsideFund
     */
    private $accountAssociations;

    /**
     * @var int
     */
    private $model_id;

    /**
     * @param \App\Entity\CeModel
     */
    private $model;

    /**
     * @var bool
     */
    private $muni_substitution;

    /**
     * @var SecurityTransaction
     */
    private $securityTransaction;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->muni_substitution = false;
        $this->accountAssociations = new ArrayCollection();
        $this->ceModelEntity = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set subclass_id.
     *
     * @param int $subclassId
     *
     * @return SecurityAssignment
     */
    public function setSubclassId($subclassId)
    {
        $this->subclass_id = $subclassId;

        return $this;
    }

    /**
     * Get subclass_id.
     *
     * @return int
     */
    public function getSubclassId()
    {
        return $this->subclass_id;
    }

    /**
     * Set subclass.
     *
     * @param \App\Entity\Subclass $subclass
     *
     * @return SecurityAssignment
     */
    public function setSubclass(Subclass $subclass = null)
    {
        $this->subclass = $subclass;

        return $this;
    }

    /**
     * Get subclass.
     *
     * @return \App\Entity\Subclass
     */
    public function getSubclass()
    {
        return $this->subclass;
    }

    /**
     * Set is_preferred.
     *
     * @param bool $isPreferred
     *
     * @return SecurityAssignment
     */
    public function setIsPreferred($isPreferred)
    {
        $this->is_preferred = $isPreferred;

        return $this;
    }

    /**
     * Get is_preferred.
     *
     * @return bool
     */
    public function getIsPreferred()
    {
        return $this->is_preferred;
    }

    /**
     * Set security_id.
     *
     * @param int $securityId
     *
     * @return SecurityAssignment
     */
    public function setSecurityId($securityId)
    {
        $this->security_id = $securityId;

        return $this;
    }

    /**
     * Get security_id.
     *
     * @return int
     */
    public function getSecurityId()
    {
        return $this->security_id;
    }

    /**
     * Set security.
     *
     * @param \App\Entity\Security $security
     *
     * @return SecurityAssignment
     */
    public function setSecurity(Security $security = null)
    {
        $this->security = $security;

        return $this;
    }

    /**
     * Get security.
     *
     * @return \App\Entity\Security
     */
    public function getSecurity()
    {
        return $this->security;
    }

    /**
     * Set accountAssociations.
     *
     * @param \App\Entity\AccountOutsideFund $accountAssociations
     *
     * @return SecurityAssignment
     */
    public function setAccountAssociations(AccountOutsideFund $accountAssociations = null)
    {
        $this->accountAssociations = $accountAssociations;

        return $this;
    }

    /**
     * Get accountAssociations.
     *
     * @return \App\Entity\AccountOutsideFund
     */
    public function getAccountAssociations()
    {
        return $this->accountAssociations;
    }

    /**
     * Add accountAssociations.
     *
     * @param \App\Entity\AccountOutsideFund $accountAssociations
     *
     * @return SecurityAssignment
     */
    public function addAccountAssociation(AccountOutsideFund $accountAssociations)
    {
        $this->accountAssociations[] = $accountAssociations;

        return $this;
    }

    /**
     * Remove accountAssociations.
     *
     * @param \App\Entity\AccountOutsideFund $accountAssociations
     */
    public function removeAccountAssociation(AccountOutsideFund $accountAssociations)
    {
        $this->accountAssociations->removeElement($accountAssociations);
    }

    /**
     * Set model_id.
     *
     * @param int $modelId
     *
     * @return SecurityAssignment
     */
    public function setModelId($modelId)
    {
        $this->model_id = $modelId;

        return $this;
    }

    /**
     * Get model_id.
     *
     * @return int
     */
    public function getModelId()
    {
        return $this->model_id;
    }

    /**
     * Set model.
     *
     * @param CeModelInterface $model
     *
     * @return SecurityAssignment
     */
    public function setModel(CeModelInterface $model = null)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Get model.
     *
     * @return \App\Entity\CeModel
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set muni_substitution.
     *
     * @param bool $muniSubstitution
     *
     * @return SecurityAssignment
     */
    public function setMuniSubstitution($muniSubstitution)
    {
        $this->muni_substitution = $muniSubstitution;

        return $this;
    }

    /**
     * Get muni_substitution.
     *
     * @return bool
     */
    public function getMuniSubstitution()
    {
        return $this->muni_substitution;
    }

    /**
     * Get securityTransaction.
     *
     * @return SecurityTransaction
     */
    public function getSecurityTransaction()
    {
        return $this->securityTransaction;
    }

    /**
     * Set securityTransaction.
     *
     * @param \App\Entity\SecurityTransaction $securityTransaction
     *
     * @return SecurityAssignment
     */
    public function setSecurityTransaction(SecurityTransaction $securityTransaction = null)
    {
        $this->securityTransaction = $securityTransaction;

        return $this;
    }

    /**
     * Get security expense_ratio.
     *
     * @return float
     */
    public function getExpenseRatio()
    {
        return $this->getSecurity() ? $this->getSecurity()->getExpenseRatio() : 0;
    }

    /**
     * Get clone.
     *
     * @return SecurityAssignment
     */
    public function getCopy()
    {
        $clone = clone $this;

        $clone->id = null;
        $clone->accountAssociations = new ArrayCollection();

        return $clone;
    }

    /**
     * @var ArrayCollection
     */
    private $ceModelEntity;

    /**
     * Add ceModelEntity.
     *
     * @param \App\Entity\CeModelEntity $ceModelEntity
     *
     * @return SecurityAssignment
     */
    public function addCeModelEntity(CeModelEntity $ceModelEntity)
    {
        $this->ceModelEntity[] = $ceModelEntity;

        return $this;
    }

    /**
     * Remove ceModelEntity.
     *
     * @param \App\Entity\CeModelEntity $ceModelEntity
     */
    public function removeCeModelEntity(CeModelEntity $ceModelEntity)
    {
        $this->ceModelEntity->removeElement($ceModelEntity);
    }

    /**
     * Get ceModelEntity.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCeModelEntity()
    {
        return $this->ceModelEntity;
    }


    public function __toString()
    {
        return (string) $this->getSecurity();
    }

    /**
     * @return User
     */
    public function getRiaUserId(): User
    {
        return $this->ria_user_id;
    }

    /**
     * @param User $ria_user_id
     */
    public function setRiaUserId(User $ria_user_id): void
    {
        $this->ria_user_id = $ria_user_id;
    }
}
