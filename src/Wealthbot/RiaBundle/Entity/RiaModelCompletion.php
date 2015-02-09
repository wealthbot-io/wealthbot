<?php

namespace Wealthbot\RiaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Wealthbot\UserBundle\Entity\User;

/**
 * Wealthbot\RiaBundle\Entity\RiaModelCompletion
 */
class RiaModelCompletion
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var integer $ria_user_id
     */
    private $ria_user_id;

    /**
     * @var boolean $models_created
     */
    private $models_created;

    /**
     * @var User
     */
    private $ria;

    /**
     * @var boolean
     */
    private $users_and_user_groups;

    /**
     * @var boolean $billingComplete
     */
    private $billingComplete;

    /**
     * @var boolean
     */
    private $select_custodians;

    /**
     * @var boolean
     */
    private $rebalancing_settings;

    /**
     * @var boolean
     */
    private $customize_proposals;

    /**
     * @var boolean
     */
    private $create_securities;

    /**
     * @var boolean
     */
    private $assign_securities;

    /**
     * @var boolean
     */
    private $proposalDocuments;

    /**
     * Class constructor
     *
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set ria_user_id
     *
     * @param integer $riaUserId
     * @return RiaModelCompletion
     */
    public function setRiaUserId($riaUserId)
    {
        $this->ria_user_id = $riaUserId;

        return $this;
    }

    /**
     * Get ria_user_id
     *
     * @return integer
     */
    public function getRiaUserId()
    {
        return $this->ria_user_id;
    }

    /**
     * Set models_created
     *
     * @param boolean $modelsCreated
     * @return RiaModelCompletion
     */
    public function setModelsCreated($modelsCreated)
    {
        $this->models_created = $modelsCreated;

        return $this;
    }

    /**
     * Get models_created
     *
     * @return boolean
     */
    public function getModelsCreated()
    {
        return $this->models_created;
    }

    /**
     * Set ria
     *
     * @param Wealthbot\UserBundle\Entity\User $ria
     * @return RiaModelCompletion
     */
    public function setRia(\Wealthbot\UserBundle\Entity\User $ria = null)
    {
        $this->ria = $ria;

        return $this;
    }

    /**
     * Get ria
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

            $progress += 1;

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
        return $this->getProgress() == 100;
    }

    /**
     * Set users_and_user_groups
     *
     * @param boolean $usersAndUserGroups
     * @return RiaModelCompletion
     */
    public function setUsersAndUserGroups($usersAndUserGroups)
    {
        $this->users_and_user_groups = $usersAndUserGroups;

        return $this;
    }

    /**
     * Get users_and_user_groups
     *
     * @return boolean
     */
    public function getUsersAndUserGroups()
    {
        return $this->users_and_user_groups;
    }

    /**
     * @param boolean $billingComplete
     */
    public function setBillingComplete($billingComplete)
    {
        $this->billingComplete = $billingComplete;
    }

    /**
     * @return boolean
     */
    public function isBillingComplete()
    {
        return (bool) $this->billingComplete;
    }

    /**
     * Get billingComplete
     *
     * @return boolean
     */
    public function getBillingComplete()
    {
        return $this->billingComplete;
    }


    /**
     * Set select_custodians
     *
     * @param boolean $selectCustodians
     * @return RiaModelCompletion
     */
    public function setSelectCustodians($selectCustodians)
    {
        $this->select_custodians = $selectCustodians;

        return $this;
    }

    /**
     * Get select_custodians
     *
     * @return boolean
     */
    public function getSelectCustodians()
    {
        return $this->select_custodians;
    }

    /**
     * Set rebalancing_settings
     *
     * @param boolean $rebalancingSettings
     * @return RiaModelCompletion
     */
    public function setRebalancingSettings($rebalancingSettings)
    {
        $this->rebalancing_settings = $rebalancingSettings;

        return $this;
    }

    /**
     * Get rebalancing_settings
     *
     * @return boolean
     */
    public function getRebalancingSettings()
    {
        return $this->rebalancing_settings;
    }

    /**
     * Set customize_proposals
     *
     * @param boolean $customizeProposals
     * @return RiaModelCompletion
     */
    public function setCustomizeProposals($customizeProposals)
    {
        $this->customize_proposals = $customizeProposals;

        return $this;
    }

    /**
     * Get customize_proposals
     *
     * @return boolean
     */
    public function getCustomizeProposals()
    {
        return $this->customize_proposals;
    }

    /**
     * Set create_securities
     *
     * @param boolean $createSecurities
     * @return RiaModelCompletion
     */
    public function setCreateSecurities($createSecurities)
    {
        $this->create_securities = $createSecurities;

        return $this;
    }

    /**
     * Get create_securities
     *
     * @return boolean
     */
    public function getCreateSecurities()
    {
        return $this->create_securities;
    }

    /**
     * Set assign_securities
     *
     * @param boolean $assignSecurities
     * @return RiaModelCompletion
     */
    public function setAssignSecurities($assignSecurities)
    {
        $this->assign_securities = $assignSecurities;

        return $this;
    }

    /**
     * Get assign_securities
     *
     * @return boolean
     */
    public function getAssignSecurities()
    {
        return $this->assign_securities;
    }

    /**
     * Set proposalDocuments
     *
     * @param boolean $proposalDocuments
     * @return RiaModelCompletion
     */
    public function setProposalDocuments($proposalDocuments)
    {
        $this->proposalDocuments = $proposalDocuments;

        return $this;
    }

    /**
     * Get proposalDocuments
     *
     * @return boolean 
     */
    public function getProposalDocuments()
    {
        return $this->proposalDocuments;
    }
}
