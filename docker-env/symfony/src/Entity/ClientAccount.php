<?php

namespace App\Entity;

use App\Manager\ClientToSystemAccountTypeAdapter;
use App\Model\ClientAccount as BaseClientAccount;
use App\Entity\AccountOutsideFund;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class ClientAccount
 * @package App\Entity
 */
class ClientAccount extends BaseClientAccount
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $client_id;

    /**
     * @var string
     */
    protected $financial_institution;

    /**
     * @var float
     */
    private $value;

    /**
     * @var float
     */
    protected $monthly_contributions;

    /**
     * @var float
     */
    protected $monthly_distributions;

    /**
     * @param \App\Entity\User
     */
    protected $client;

    /**
     * @var float
     */
    private $sas_cash;

    /**
     * @var int
     */
    private $group_type_id;

    /**
     * @param \App\Entity\AccountGroupType
     */
    protected $groupType;

    /**
     * @var int
     */
    protected $process_step;

    /**
     * @var string
     */
    protected $step_action;

    /**
     * @var bool
     */
    private $is_pre_saved;

    /**
     * @param \App\Entity\SystemAccount
     */
    protected $systemAccount;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $beneficiaries;

    /**
     * @param \App\Entity\RetirementPlanInformation
     */
    private $retirementPlanInfo;

    /**
     * @param \App\Entity\TransferInformation
     */
    protected $transferInformation;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $accountOutsideFunds;

    /**
     * @param \App\Entity\AccountContribution
     */
    protected $accountContribution;

    /**
     * @var int
     */
    protected $consolidator_id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $consolidatedAccounts;

    /**
     * @param \App\Entity\ClientAccount
     */
    protected $consolidator;

    /**
     * @var int
     */
    protected $system_type;

    /**
     * @var bool
     */
    private $unconsolidated;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $accountOwners;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var bool
     */
    private $is_init_rebalanced;

    /**
     * @var \DateTime
     */
    private $modified;

    /**
     * @var string
     */
    private $modified_by;


    /**
     * @var
     */
    public $type;



    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->process_step = 0;
        $this->is_pre_saved = false;
        $this->unconsolidated = false;
        $this->is_init_rebalanced = false;

        parent::__construct();

        $this->accountOutsideFunds = new ArrayCollection();
        $this->beneficiaries = new ArrayCollection();
        $this->consolidatedAccounts = new ArrayCollection();
        $this->accountOwners = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getId();
    }

    /**
     * Set client_id.
     *
     * @param int $clientId
     *
     * @return ClientAccount
     */
    public function setClientId($clientId)
    {
        parent::setClientId($clientId);

        return $this;
    }

    /**
     * Get client_id.
     *
     * @return int
     */
    public function getClientId()
    {
        return parent::getClientId();
    }

    /**
     * Set financial_institution.
     *
     * @param string $financialInstitution
     *
     * @return ClientAccount
     */
    public function setFinancialInstitution($financialInstitution)
    {
        parent::setFinancialInstitution($financialInstitution);

        return $this;
    }

    /**
     * Get financial_institution.
     *
     * @return string
     */
    public function getFinancialInstitution()
    {
        return parent::getFinancialInstitution();
    }

    /**
     * Set value.
     *
     * @param int $value
     *
     * @return ClientAccount
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set monthly_contributions.
     *
     * @param float $monthlyContributions
     *
     * @return ClientAccount
     */
    public function setMonthlyContributions($monthlyContributions)
    {
        parent::setMonthlyContributions($monthlyContributions);

        return $this;
    }

    /**
     * Get monthly_contributions.
     *
     * @return float
     */
    public function getMonthlyContributions()
    {
        return parent::getMonthlyContributions();
    }

    /**
     * Set monthly_distributions.
     *
     * @param float $monthlyDistributions
     *
     * @return ClientAccount
     */
    public function setMonthlyDistributions($monthlyDistributions)
    {
        parent::setMonthlyDistributions($monthlyDistributions);

        return $this;
    }

    /**
     * Get monthly_distributions.
     *
     * @return float
     */
    public function getMonthlyDistributions()
    {
        return parent::getMonthlyDistributions();
    }

    /**
     * Set client.
     *
     * @param \App\Entity\User $client
     *
     * @return ClientAccount
     */
    public function setClient(User $client = null)
    {
        parent::setClient($client);

        return $this;
    }

    /**
     * Get client.
     *
     * @return \App\Entity\User
     */
    public function getClient()
    {
        return parent::getClient();
    }

    /**
     * Set sas_cash.
     *
     * @param float $sasCash
     *
     * @return $this
     */
    public function setSasCash($sasCash)
    {
        $this->sas_cash = $sasCash;

        return $this;
    }

    /**
     * Get sas_cash.
     *
     * @return float
     */
    public function getSasCash()
    {
        return $this->sas_cash;
    }

    /**
     * Set group_type_id.
     *
     * @param int $groupTypeId
     *
     * @return ClientAccount
     */
    public function setGroupTypeId($groupTypeId)
    {
        $this->group_type_id = $groupTypeId;

        return $this;
    }

    /**
     * Get group_type_id.
     *
     * @return int
     */
    public function getGroupTypeId()
    {
        return $this->group_type_id;
    }

    /**
     * Set groupType.
     *
     * @param mixed $groupType
     *
     * @return ClientAccount
     */
    public function setGroupType($groupType = null)
    {
        if (is_numeric($groupType)) {
            return $this;
        }

        parent::setGroupType($groupType);

        // Update system_account field
        if ($groupType) {
            $typeAdapter = new ClientToSystemAccountTypeAdapter($this);
            $this->setSystemType($typeAdapter->getType());
        }

        return $this;
    }

    /**
     * Get groupType.
     *
     * @return \App\Entity\AccountGroupType
     */
    public function getGroupType()
    {
        return parent::getGroupType();
    }

    /**
     * Set process_step.
     *
     * @param int $processStep
     *
     * @return ClientAccount
     */
    public function setProcessStep($processStep)
    {
        return parent::setProcessStep($processStep);
    }

    /**
     * Get process_step.
     *
     * @return int
     */
    public function getProcessStep()
    {
        return parent::getProcessStep();
    }

    /**
     * Add beneficiaries.
     *
     * @param \App\Entity\Beneficiary $beneficiaries
     *
     * @return ClientAccount
     */
    public function addBeneficiarie(Beneficiary $beneficiaries)
    {
        parent::addBeneficiarie($beneficiaries);

        return $this;
    }

    /**
     * Remove beneficiaries.
     *
     * @param Beneficiary $beneficiaries
     */
    public function removeBeneficiarie(Beneficiary $beneficiaries)
    {
        parent::removeBeneficiarie($beneficiaries);
    }

    /**
     * Get beneficiaries.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBeneficiaries()
    {
        return parent::getBeneficiaries();
    }

    /**
     * Set retirementPlanInfo.
     *
     * @param RetirementPlanInformation $retirementPlanInfo
     *
     * @return ClientAccount
     */
    public function setRetirementPlanInfo(RetirementPlanInformation $retirementPlanInfo = null)
    {
        $this->retirementPlanInfo = $retirementPlanInfo;

        return $this;
    }

    /**
     * Get retirementPlanInfo.
     *
     * @return \App\Entity\RetirementPlanInformation
     */
    public function getRetirementPlanInfo()
    {
        return $this->retirementPlanInfo;
    }

    /**
     * Set transferInformation.
     *
     * @param TransferInformation $transferInformation
     *
     * @return ClientAccount
     */
    public function setTransferInformation(TransferInformation $transferInformation = null)
    {
        parent::setTransferInformation($transferInformation);

        return $this;
    }

    /**
     * Get transferInformation.
     *
     * @return \App\Entity\TransferInformation
     */
    public function getTransferInformation()
    {
        return parent::getTransferInformation();
    }

    /**
     * Set step_action.
     *
     * @param string $stepAction
     *
     * @return ClientAccount
     */
    public function setStepAction($stepAction)
    {
        return parent::setStepAction($stepAction);
    }

    /**
     * Get step_action.
     *
     * @return string
     */
    public function getStepAction()
    {
        return parent::getStepAction();
    }

    /**
     * Set is_pre_saved.
     *
     * @param bool $isPreSaved
     *
     * @return ClientAccount
     */
    public function setIsPreSaved($isPreSaved)
    {
        $this->is_pre_saved = $isPreSaved;

        return $this;
    }

    /**
     * Get is_pre_saved.
     *
     * @return bool
     */
    public function getIsPreSaved()
    {
        return $this->is_pre_saved;
    }

    /**
     * Add accountOutsideFunds.
     *
     * @param AccountOutsideFund $accountOutsideFunds
     *
     * @return ClientAccount
     */
    public function addAccountOutsideFund($accountOutsideFunds)
    {
        $this->accountOutsideFunds[] = $accountOutsideFunds;

        return $this;
    }

    /**
     * Remove accountOutsideFunds.
     *
     * @param \App\Entity\AccountOutsideFund $accountOutsideFunds
     */

    /**
     * @param AccountOutsideFund $accountOutsideFunds
     */
    public function removeAccountOutsideFund(AccountOutsideFund $accountOutsideFunds)
    {
        $this->accountOutsideFunds->removeElement($accountOutsideFunds);
    }

    /**
     * Get accountOutsideFunds.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAccountOutsideFunds()
    {
        return $this->accountOutsideFunds;
    }

    public function isCompleted()
    {
        $step = $this->getProcessStep();
        $group = $this->getGroupName();

        if ((AccountGroup::GROUP_EMPLOYER_RETIREMENT === $group && self::PROCESS_STEP_COMPLETED_CREDENTIALS === $step) ||
            (AccountGroup::GROUP_EMPLOYER_RETIREMENT !== $group && self::PROCESS_STEP_FINISHED_APPLICATION === $step)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Set systemAccount.
     *
     * @param \App\Entity\SystemAccount $systemAccount
     *
     * @return ClientAccount
     */
    public function setSystemAccount(SystemAccount $systemAccount = null)
    {
        parent::setSystemAccount($systemAccount);

        return $this;
    }

    /**
     * Get systemAccount.
     *
     * @return \App\Entity\SystemAccount
     */
    public function getSystemAccount()
    {
        return parent::getSystemAccount();
    }

    /**
     * Set accountContribution.
     *
     * @param \App\Entity\AccountContribution $accountContribution
     *
     * @return ClientAccount
     */
    public function setAccountContribution(AccountContribution $accountContribution = null)
    {
        parent::setAccountContribution($accountContribution);

        return $this;
    }

    /**
     * Get accountContribution.
     *
     * @return AccountContribution
     */
    public function getAccountContribution()
    {
        return parent::getAccountContribution();
    }

    /**
     * Set consolidator_id.
     *
     * @param int $consolidatorId
     *
     * @return ClientAccount
     */
    public function setConsolidatorId($consolidatorId)
    {
        parent::setConsolidatorId($consolidatorId);

        return $this;
    }

    /**
     * Get consolidator_id.
     *
     * @return int
     */
    public function getConsolidatorId()
    {
        return parent::getConsolidatorId();
    }

    /**
     * Add consolidatedAccounts.
     *
     * @param \App\Entity\ClientAccount $consolidatedAccounts
     *
     * @return ClientAccount
     */
    public function addConsolidatedAccount(ClientAccount $consolidatedAccounts)
    {
        parent::addConsolidatedAccount($consolidatedAccounts);

        return $this;
    }

    /**
     * Remove consolidatedAccounts.
     *
     * @param \App\Entity\ClientAccount $consolidatedAccounts
     */
    public function removeConsolidatedAccount(ClientAccount $consolidatedAccounts)
    {
        parent::removeConsolidatedAccount($consolidatedAccounts);
    }

    /**
     * Get consolidatedAccounts.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getConsolidatedAccounts()
    {
        return parent::getConsolidatedAccounts();
    }

    /**
     * Set consolidator.
     *
     * @param \App\Entity\ClientAccount $consolidator
     *
     * @return ClientAccount
     */
    public function setConsolidator(ClientAccount $consolidator = null)
    {
        parent::setConsolidator($consolidator);

        return $this;
    }

    /**
     * Get consolidator.
     *
     * @return \App\Entity\ClientAccount
     */
    public function getConsolidator()
    {
        return parent::getConsolidator();
    }

    /**
     * Set system_type.
     *
     * @param int $systemType
     *
     * @return ClientAccount
     */
    public function setSystemType($systemType)
    {
        parent::setSystemType($systemType);

        return $this;
    }

    /**
     * Get system_type.
     *
     * @return int
     */
    public function getSystemType()
    {
        return parent::getSystemType();
    }

    /**
     * Set unconsolidated.
     *
     * @param bool $unconsolidated
     *
     * @return ClientAccount
     */
    public function setUnconsolidated($unconsolidated)
    {
        $this->unconsolidated = $unconsolidated;

        return $this;
    }

    /**
     * Get unconsolidated.
     *
     * @return bool
     */
    public function getUnconsolidated()
    {
        return $this->unconsolidated;
    }

    /**
     * Get sum of the consolidated accounts values or value if account is not consolidated.
     *
     * @return float
     */
    public function getValueSum()
    {
        $sum = $this->getValue();

        if ($this->getConsolidatedAccounts() && $this->getConsolidatedAccounts()->count()) {
            foreach ($this->getConsolidatedAccounts() as $account) {
                $sum += $account->getValue();
            }
        }

        return $sum;
    }

    /**
     * Get sum of the consolidated accounts monthly_contributions or monthly_contribution if account is not consolidated.
     *
     * @return float
     */
    public function getContributionsSum()
    {
        $sum = $this->getMonthlyContributions();

        if ($this->getConsolidatedAccounts() && $this->getConsolidatedAccounts()->count()) {
            foreach ($this->getConsolidatedAccounts() as $account) {
                $sum += $account->getMonthlyContributions();
            }
        }

        return $sum;
    }

    /**
     * Get sum of the consolidated accounts monthly_distributions or monthly_distribution if account is not consolidated.
     *
     * @return float
     */
    public function getDistributionsSum()
    {
        $sum = $this->getMonthlyDistributions();

        if ($this->getConsolidatedAccounts() && $this->getConsolidatedAccounts()->count()) {
            foreach ($this->getConsolidatedAccounts() as $account) {
                $sum += $account->getMonthlyDistributions();
            }
        }

        return $sum;
    }

    /**
     * Get sum of the consolidated accounts sas_cache or sas_cache if account is not consolidated.
     *
     * @return float
     */
    public function getSasCashSum()
    {
        $sum = $this->getSasCash();

        if ($this->getConsolidatedAccounts() && $this->getConsolidatedAccounts()->count()) {
            foreach ($this->getConsolidatedAccounts() as $account) {
                $sum += $account->getSasCash();
            }
        }

        return $sum;
    }

    /**
     * Add accountOwners.
     *
     * @param \App\Model\ClientAccountOwner $accountOwner
     *
     * @return ClientAccount
     */
    public function addAccountOwner(\App\Model\ClientAccountOwner $accountOwner)
    {
        parent::addAccountOwner($accountOwner);

        return $this;
    }

    /**
     * Remove accountOwners.
     *
     * @param \App\Model\ClientAccountOwner $accountOwner
     */
    public function removeAccountOwner(\App\Model\ClientAccountOwner $accountOwner)
    {
        parent::removeAccountOwner($accountOwner);
    }

    /**
     * Get accountOwners.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAccountOwners()
    {
        return parent::getAccountOwners();
    }

    /**
     * Add beneficiaries.
     *
     * @param \App\Entity\Beneficiary $beneficiaries
     *
     * @return ClientAccount
     */
    public function addBeneficiary(Beneficiary $beneficiaries)
    {
        $this->beneficiaries[] = $beneficiaries;

        return $this;
    }

    /**
     * Remove beneficiaries.
     *
     * @param \App\Entity\Beneficiary $beneficiaries
     */
    public function removeBeneficiary(Beneficiary $beneficiaries)
    {
        $this->beneficiaries->removeElement($beneficiaries);
    }

    public function getOwnerNames()
    {
        $owners = $this->getAccountOwners()->getValues();

        $names = array_map(function (ClientAccountOwner  $owner) {
            /* @var $owner ClientAccountOwner */
            $client = $owner->getClient();
            if ($client) {
                return $client->getLastName().' '.$client->getFirstName();
            }

            return '';
        }, $owners);

        return implode(', ', $names);
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return ClientAccount
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set is_init_rebalanced.
     *
     * @param bool $isInitRebalanced
     *
     * @return ClientAccount
     */
    public function setIsInitRebalanced($isInitRebalanced)
    {
        $this->is_init_rebalanced = $isInitRebalanced;

        return $this;
    }

    /**
     * Get is_init_rebalanced.
     *
     * @return bool
     */
    public function getIsInitRebalanced()
    {
        return $this->is_init_rebalanced;
    }

    /**
     * Set modified.
     *
     * @param \DateTime $modified
     *
     * @return ClientAccount
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified.
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * Set modified_by.
     *
     * @param string $modifiedBy
     *
     * @return ClientAccount
     */
    public function setModifiedBy($modifiedBy)
    {
        $this->modified_by = $modifiedBy;

        return $this;
    }

    /**
     * Get modified_by.
     *
     * @return string
     */
    public function getModifiedBy()
    {
        return $this->modified_by;
    }
}
