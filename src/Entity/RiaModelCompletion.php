<?php

namespace App\Entity;

use App\Entity\User;

/**
 * Class RiaModelCompletion
 * @package App\Entity
 */
class RiaModelCompletion
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $ria_user_id;

    /**
     * @var bool
     */
    private $models_created;

    /**
     * @var User
     */
    private $ria;

    /**
     * @var bool
     */
    private $users_and_user_groups;

    /**
     * @var bool
     */
    private $billingComplete;

    /**
     * @var bool
     */
    private $select_custodians;

    /**
     * @var bool
     */
    private $rebalancing_settings;

    /**
     * @var bool
     */
    private $customize_proposals;

    /**
     * @var bool
     */
    private $create_securities;

    /**
     * @var bool
     */
    private $assign_securities;

    /**
     * @var bool
     */
    private $proposalDocuments;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->users_and_user_groups = false;
        $this->select_custodians = false;
        $this->rebalancing_settings = false;
        $this->create_securities = false;
        $this->assign_securities = false;
        $this->models_created = false;
        $this->customize_proposals = false;
        $this->billingComplete = false;
        $this->proposalDocuments = false;
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
     * Set ria_user_id.
     *
     * @param int $riaUserId
     *
     * @return RiaModelCompletion
     */
    public function setRiaUserId($riaUserId)
    {
        $this->ria_user_id = $riaUserId;

        return $this;
    }

    /**
     * Get ria_user_id.
     *
     * @return int
     */
    public function getRiaUserId()
    {
        return $this->ria_user_id;
    }

    /**
     * Set models_created.
     *
     * @param bool $modelsCreated
     *
     * @return RiaModelCompletion
     */
    public function setModelsCreated($modelsCreated)
    {
        $this->models_created = $modelsCreated;

        return $this;
    }

    /**
     * Get models_created.
     *
     * @return bool
     */
    public function getModelsCreated()
    {
        return $this->models_created;
    }

    /**
     * Set ria.
     *
     * @param Entity\User $ria
     *
     * @return RiaModelCompletion
     */
    public function setRia(User $ria = null)
    {
        $this->ria = $ria;

        return $this;
    }

    /**
     * Get ria.
     *
     * @return User
     */
    public function getRia()
    {
        return $this->ria;
    }

    public function getProgress()
    {
        $progress = 0;

        if ($this->getRia()->getRiaCompanyInformation()->getPortfolioModel()->isCustom()) {
            $step = 11;

            if ($this->getUsersAndUserGroups()) {
                $progress += $step;
            }

            if ($this->getSelectCustodians()) {
                $progress += $step;
            }

            if ($this->getRebalancingSettings()) {
                $progress += $step;
            }

            if ($this->getCreateSecurities()) {
                $progress += $step;
            }

            if ($this->getAssignSecurities()) {
                $progress += $step;
            }

            if ($this->getProposalDocuments()) {
                $progress += $step;
            }

            if ($this->getModelsCreated()) {
                $progress += $step;
            }

            if ($this->getCustomizeProposals()) {
                $progress += $step;
            }

            if ($this->isBillingComplete()) {
                $progress += $step;
            }

            ++$progress;
        } else {
            $step = 16;

            if ($this->getUsersAndUserGroups()) {
                $progress += $step;
            }

            if ($this->getSelectCustodians()) {
                $progress += $step;
            }

            if ($this->getRebalancingSettings()) {
                $progress += $step;
            }

            if ($this->getCustomizeProposals()) {
                $progress += $step;
            }

            if ($this->isBillingComplete()) {
                $progress += $step;
            }

            if ($this->getProposalDocuments()) {
                $progress += $step;
            }

            $progress += 4;
        }

        return $progress;
    }

    public function isComplete()
    {
        return 100 === $this->getProgress();
    }

    /**
     * Set users_and_user_groups.
     *
     * @param bool $usersAndUserGroups
     *
     * @return RiaModelCompletion
     */
    public function setUsersAndUserGroups($usersAndUserGroups)
    {
        $this->users_and_user_groups = $usersAndUserGroups;

        return $this;
    }

    /**
     * Get users_and_user_groups.
     *
     * @return bool
     */
    public function getUsersAndUserGroups()
    {
        return $this->users_and_user_groups;
    }

    /**
     * @param bool $billingComplete
     */
    public function setBillingComplete($billingComplete)
    {
        $this->billingComplete = $billingComplete;
    }

    /**
     * @return bool
     */
    public function isBillingComplete()
    {
        return (bool) $this->billingComplete;
    }

    /**
     * Get billingComplete.
     *
     * @return bool
     */
    public function getBillingComplete()
    {
        return $this->billingComplete;
    }

    /**
     * Set select_custodians.
     *
     * @param bool $selectCustodians
     *
     * @return RiaModelCompletion
     */
    public function setSelectCustodians($selectCustodians)
    {
        $this->select_custodians = $selectCustodians;

        return $this;
    }

    /**
     * Get select_custodians.
     *
     * @return bool
     */
    public function getSelectCustodians()
    {
        return $this->select_custodians;
    }

    /**
     * Set rebalancing_settings.
     *
     * @param bool $rebalancingSettings
     *
     * @return RiaModelCompletion
     */
    public function setRebalancingSettings($rebalancingSettings)
    {
        $this->rebalancing_settings = $rebalancingSettings;

        return $this;
    }

    /**
     * Get rebalancing_settings.
     *
     * @return bool
     */
    public function getRebalancingSettings()
    {
        return $this->rebalancing_settings;
    }

    /**
     * Set customize_proposals.
     *
     * @param bool $customizeProposals
     *
     * @return RiaModelCompletion
     */
    public function setCustomizeProposals($customizeProposals)
    {
        $this->customize_proposals = $customizeProposals;

        return $this;
    }

    /**
     * Get customize_proposals.
     *
     * @return bool
     */
    public function getCustomizeProposals()
    {
        return $this->customize_proposals;
    }

    /**
     * Set create_securities.
     *
     * @param bool $createSecurities
     *
     * @return RiaModelCompletion
     */
    public function setCreateSecurities($createSecurities)
    {
        $this->create_securities = $createSecurities;

        return $this;
    }

    /**
     * Get create_securities.
     *
     * @return bool
     */
    public function getCreateSecurities()
    {
        return $this->create_securities;
    }

    /**
     * Set assign_securities.
     *
     * @param bool $assignSecurities
     *
     * @return RiaModelCompletion
     */
    public function setAssignSecurities($assignSecurities)
    {
        $this->assign_securities = $assignSecurities;

        return $this;
    }

    /**
     * Get assign_securities.
     *
     * @return bool
     */
    public function getAssignSecurities()
    {
        return $this->assign_securities;
    }

    /**
     * Set proposalDocuments.
     *
     * @param bool $proposalDocuments
     *
     * @return RiaModelCompletion
     */
    public function setProposalDocuments($proposalDocuments)
    {
        $this->proposalDocuments = $proposalDocuments;

        return $this;
    }

    /**
     * Get proposalDocuments.
     *
     * @return bool
     */
    public function getProposalDocuments()
    {
        return $this->proposalDocuments;
    }
}
