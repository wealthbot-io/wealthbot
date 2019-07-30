<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\CeModel;
use App\Model\CeModelInterface;
use App\Entity\Document;
use App\Entity\User;

/**
 * Class RiaCompanyInformation
 * @package App\Entity
 */
class RiaCompanyInformation
{
    public $fees;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $website;

    /**
     * @var string
     */
    private $address;

    /**
     * @var string
     */
    private $office;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $zipcode;

    /**
     * @var string
     */
    private $phone_number;

    /**
     * @var string
     */
    private $contact_email;

    /**
     * @var string
     */
    private $logo;

    /**
     * @var int
     */
    private $ria_user_id;

    /**
     * @param \App\Entity\User
     */
    private $ria;

    /**
     * @var int
     */
    private $account_managed;

    /**
     * @var bool
     */
    private $is_allow_retirement_plan = true;

    /**
     * @var bool
     */
    private $activated;

    /**
     * @var bool
     */
    private $is_use_qualified_models;

    /**
     * @var int
     */
    private $relationship_type;

    /**
     * @var
     */
    private $custodianKey;

    /**
     * @var
     */
    private $custodianSecret;


    const RELATIONSHIP_TYPE_LICENSE_FEE = 0;
    const RELATIONSHIP_TYPE_TAMP = 1;

    public static $relationship_type_choices = [
        'TAMP' => self::RELATIONSHIP_TYPE_TAMP,
        'License Fee' => self::RELATIONSHIP_TYPE_LICENSE_FEE,
    ];

    // constants for $account_managed
    const ACCOUNT_MANAGED_ACCOUNT = 1;
    const ACCOUNT_MANAGED_HOUSEHOLD = 2;
    const ACCOUNT_MANAGED_ACCOUNT_OR_HOUSEHOLD = 3;

    /**
     * Choices for account management.
     */
    public static $account_managed_choices = [
        'Account Level' => self::ACCOUNT_MANAGED_ACCOUNT,
        'Household Level' => self::ACCOUNT_MANAGED_HOUSEHOLD,
        'Account or Household Level' => self::ACCOUNT_MANAGED_ACCOUNT_OR_HOUSEHOLD,
    ];

    /**
     * @var array Choices for rebalanced method
     */
    public static $rebalanced_method_choices = [
       'Asset Class' =>  1,
        'Subclass' => 2,
    ];

    const REBALANCED_FREQUENCY_QUARTERLY = 1;
    const REBALANCED_FREQUENCY_SEMI_ANNUALLY = 2;
    const REBALANCED_FREQUENCY_ANNUALLY = 3;
    const REBALANCED_FREQUENCY_TOLERANCE_BANDS = 4;

    /**
     * @var array Choices for rebalanced frequency choices
     */
    public static $rebalanced_frequency_choices = [
        'Quarterly' => 1,
        'Semi-Annually' => 2,
        'Annually' => 3,
        'Tolerance Bands' => 4,
    ];

    /**
     * @var int
     */
    private $portfolio_processing;

    const PORTFOLIO_PROCESSING_STRAIGHT_THROUGH = 1;
    const PORTFOLIO_PROCESSING_COLLABORATIVE = 2;

    /**
     * @var array Choices for portfolio processing
     */
    private static $portfolioProcessingChoices = [
        1 => 'Straight-Through',
        2 => 'Collaborative',
    ];

    /**
     * @var bool
     */
    private $tlh_buy_back_original;

    private $items;

    public function __construct()
    {
        $this->activated = false;
        $this->is_use_qualified_models = false;
        $this->is_searchable_db = true;
        $this->relationship_type = self::RELATIONSHIP_TYPE_LICENSE_FEE;
        $this->is_show_client_expected_asset_class = true;
        $this->is_show_expected_costs = true;
        $this->tlh_buy_back_original = false;

        $this->advisorCodes = new ArrayCollection();
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
     * Set name.
     *
     * @param string $name
     *
     * @return RiaCompanyInformation
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set website.
     *
     * @param string $website
     *
     * @return RiaCompanyInformation
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website.
     *
     * @return string
     */
    public function getWebsite()
    {
        $url = $this->website;

        if (!preg_match('~^(?:f|ht)tps?://~i', $url)) {
            $url = 'http://'.$url;
        }

        return $url;
    }

    /**
     * Set address.
     *
     * @param string $address
     *
     * @return RiaCompanyInformation
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address.
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set office.
     *
     * @param string $office
     *
     * @return RiaCompanyInformation
     */
    public function setOffice($office)
    {
        $this->office = $office;

        return $this;
    }

    /**
     * Get office.
     *
     * @return string
     */
    public function getOffice()
    {
        return $this->office;
    }

    /**
     * Set city.
     *
     * @param string $city
     *
     * @return RiaCompanyInformation
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set zipcode.
     *
     * @param string $zipcode
     *
     * @return RiaCompanyInformation
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Get zipcode.
     *
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set phone_number.
     *
     * @param string $phoneNumber
     *
     * @return RiaCompanyInformation
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phone_number = $phoneNumber;

        return $this;
    }

    /**
     * Get phone_number.
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phone_number;
    }

    /**
     * Set contact_email.
     *
     * @param string $contactEmail
     *
     * @return RiaCompanyInformation
     */
    public function setContactEmail($contactEmail)
    {
        $this->contact_email = $contactEmail;

        return $this;
    }

    /**
     * Get contact_email.
     *
     * @return string
     */
    public function getContactEmail()
    {
        return $this->contact_email;
    }

    /**
     * Set logo.
     *
     * @param string $logo
     *
     * @return RiaCompanyInformation
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo.
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set ria_user_id.
     *
     * @param int $riaUserId
     *
     * @return RiaCompanyInformation
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
     * Set ria.
     *
     * @param \App\Entity\User $ria
     *
     * @return RiaCompanyInformation
     */
    public function setRia(User $ria = null)
    {
        $this->ria = $ria;

        return $this;
    }

    /**
     * Get ria.
     *
     * @return \App\Entity\User
     */
    public function getRia()
    {
        return $this->ria;
    }

    /**
     * Virtual field for upload file.
     *
     * @Assert\File(maxSize="6000000")
     */
    private $logo_file;

    public function getLogoFile()
    {
        return $this->logo_file;
    }

    public function setLogoFile($logoFile)
    {
        $this->logo_file = $logoFile;

        return $this;
    }

//    private $filenameForRemove;

    public function getAbsoluteLogo()
    {
        return null === $this->getLogo() ? null : $this->getUploadRootDir().'/'.$this->getLogo();
    }

//    public function getWebLogo()
//    {
//        return null === $this->getLogo() ? null : '/' . $this->getUploadDir().'/'.$this->getLogo();
//    }

    protected function getUploadRootDir()
    {
        return __DIR__.'/../../'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return 'public/uploads/ria_company_logos';
    }

    public function preUpload()
    {
        if (null !== $this->logo_file) {
            // do whatever you want to generate a unique name
            $this->logo = sha1(uniqid(mt_rand(), true)).'.'.$this->logo_file->guessExtension();
        }
    }

    public function upload()
    {
        if (null === $this->logo_file) {
            return;
        }

        if (!is_dir($this->getUploadRootDir())) {
            mkdir($this->getUploadRootDir(), 0777, true);
        }
        $this->logo_file->move($this->getUploadRootDir(), $this->logo);
        unset($this->logo_file);
    }

    public function removeUpload()
    {
        if ($file = $this->getAbsoluteLogo()) {
            unlink($file);
        }
    }

    /**
     * Set account_managed.
     *
     * @param int $accountManaged
     *
     * @return RiaCompanyInformation
     */
    public function setAccountManaged($accountManaged)
    {
        $this->account_managed = $accountManaged;

        return $this;
    }

    /**
     * Get account_managed.
     *
     * @return int
     */
    public function getAccountManaged()
    {
        return $this->account_managed;
    }

    public function isAccountManagedLevel()
    {
        return self::ACCOUNT_MANAGED_ACCOUNT === $this->getAccountManaged() ? true : false;
    }

    public function isHouseholdManagedLevel()
    {
        return self::ACCOUNT_MANAGED_HOUSEHOLD === $this->getAccountManaged() ? true : false;
    }

    public function isClientByClientManagedLevel()
    {
        return self::ACCOUNT_MANAGED_ACCOUNT_OR_HOUSEHOLD === $this->getAccountManaged() ? true : false;
    }

    public function getAccountManagementAsString()
    {
        return self::$account_managed_choices[$this->getAccountManaged()];
    }

    public function getAccountManagedChoices()
    {
        $choices = self::$account_managed_choices;

        if (self::PORTFOLIO_PROCESSING_STRAIGHT_THROUGH === $this->getPortfolioProcessing()) {
            unset($choices[self::ACCOUNT_MANAGED_ACCOUNT_OR_HOUSEHOLD]);
        }

        return $choices;
    }

    public function isShowSubclassPriority()
    {
        return $this->isClientByClientManagedLevel() || $this->isHouseholdManagedLevel() || ($this->getIsAllowRetirementPlan() && $this->isAccountManagedLevel());
    }

    public function isRelationTypeTamp()
    {
        return self::RELATIONSHIP_TYPE_TAMP === $this->getRelationshipType();
    }

    public function isRelationTypeLicenseFee()
    {
        return self::RELATIONSHIP_TYPE_LICENSE_FEE === $this->getRelationshipType();
    }

    /**
     * Set is_allow_retirement_plan.
     *
     * @param bool $isAllowRetirementPlan
     *
     * @return RiaCompanyInformation
     */
    public function setIsAllowRetirementPlan($isAllowRetirementPlan)
    {
        $this->is_allow_retirement_plan = $isAllowRetirementPlan;

        return $this;
    }

    /**
     * Get is_allow_retirement_plan.
     *
     * @return bool
     */
    public function getIsAllowRetirementPlan()
    {
        return $this->is_allow_retirement_plan;
    }

    /**
     * @var int
     */
    private $minimum_billing_fee;

    /**
     * @var bool
     */
    private $is_show_client_expected_asset_class;

    /**
     * Set minimum_billing_fee.
     *
     * @param int $minimumBillingFee
     *
     * @return RiaCompanyInformation
     */
    public function setMinimumBillingFee($minimumBillingFee)
    {
        $this->minimum_billing_fee = $minimumBillingFee;

        return $this;
    }

    /**
     * Get minimum_billing_fee.
     *
     * @return int
     */
    public function getMinimumBillingFee()
    {
        return $this->minimum_billing_fee;
    }

    /**
     * Set is_show_client_expected_asset_class.
     *
     * @param bool $isShowClientExpectedAssetClass
     *
     * @return RiaCompanyInformation
     */
    public function setIsShowClientExpectedAssetClass($isShowClientExpectedAssetClass)
    {
        $this->is_show_client_expected_asset_class = $isShowClientExpectedAssetClass;

        return $this;
    }

    /**
     * Get is_show_client_expected_asset_class.
     *
     * @return bool
     */
    public function getIsShowClientExpectedAssetClass()
    {
        return $this->is_show_client_expected_asset_class;
    }

    /**
     * @var float
     */
    private $clients_tax_bracket;

    /**
     * Set clients_tax_bracket.
     *
     * @param float $clientsTaxBracket
     *
     * @return RiaCompanyInformation
     */
    public function setClientsTaxBracket($clientsTaxBracket)
    {
        $this->clients_tax_bracket = $clientsTaxBracket;

        return $this;
    }

    /**
     * Get clients_tax_bracket.
     *
     * @return float
     */
    public function getClientsTaxBracket()
    {
        return $this->clients_tax_bracket;
    }

    /**
     * @var bool
     */
    private $is_searchable_db;

    /**
     * @var float
     */
    private $min_asset_size;

    /**
     * @var string
     */
    private $adv_copy;

    /**
     * Set is_searchable_db.
     *
     * @param bool $isSearchableDb
     *
     * @return RiaCompanyInformation
     */
    public function setIsSearchableDb($isSearchableDb)
    {
        $this->is_searchable_db = $isSearchableDb;

        return $this;
    }

    /**
     * Get is_searchable_db.
     *
     * @return bool
     */
    public function getIsSearchableDb()
    {
        return $this->is_searchable_db;
    }

    /**
     * Set min_asset_size.
     *
     * @param float $minAssetSize
     *
     * @return RiaCompanyInformation
     */
    public function setMinAssetSize($minAssetSize)
    {
        $this->min_asset_size = $minAssetSize;

        return $this;
    }

    /**
     * Get min_asset_size.
     *
     * @return float
     */
    public function getMinAssetSize()
    {
        return $this->min_asset_size;
    }

    /**
     * Set adv_copy.
     *
     * @param string $advCopy
     *
     * @return RiaCompanyInformation
     */
    public function setAdvCopy($advCopy)
    {
        $this->adv_copy = $advCopy;

        return $this;
    }

    /**
     * Get adv_copy.
     *
     * @return string
     */
    public function getAdvCopy()
    {
        return $this->adv_copy;
    }

    /**
     * Virtual field for upload file.
     *
     * @Assert\File(maxSize="6000000")
     */
    private $adv_copy_file;

    public function getAbsoluteAdvCopy()
    {
        return null === $this->getAdvCopy() ? null : $this->getUploadAdvCopyRootDir().'/'.$this->getAdvCopy();
    }

    public function getWebAdvCopy()
    {
        return null === $this->getAdvCopy() ? null : $this->getUploadAdvCopyDir().'/'.$this->getAdvCopy();
    }

    public function getUploadAdvCopyRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return getcwd().'/'.$this->getUploadAdvCopyDir();
    }

    protected function getUploadAdvCopyDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return 'uploads/ria_company_adv_copies';
    }

    public function preUploadAdvCopy()
    {
        if (null !== $this->adv_copy_file) {
            // do whatever you want to generate a unique name
            $this->adv_copy = sha1(uniqid(mt_rand(), true)).'.'.$this->adv_copy_file->guessExtension();
        }
    }

    public function uploadAdvCopy()
    {
        if (null === $this->adv_copy_file) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->adv_copy_file->move($this->getUploadAdvCopyRootDir(), $this->adv_copy);

        unset($this->adv_copy_file);
    }

    public function removeUploadAdvCopy()
    {
        if ($file = $this->getAbsoluteAdvCopy()) {
            unlink($file);
        }
    }

    /**
     * @var int
     */
    private $rebalanced_method;

    /**
     * @var int
     */
    private $rebalanced_frequency;

    /**
     * Set rebalanced_method.
     *
     * @param int $rebalancedMethod
     *
     * @return RiaCompanyInformation
     */
    public function setRebalancedMethod($rebalancedMethod)
    {
        $this->rebalanced_method = $rebalancedMethod;

        return $this;
    }

    /**
     * Get rebalanced_method.
     *
     * @return int
     */
    public function getRebalancedMethod()
    {
        return $this->rebalanced_method;
    }

    /**
     * Set rebalanced_frequency.
     *
     * @param int $rebalancedFrequency
     *
     * @return RiaCompanyInformation
     */
    public function setRebalancedFrequency($rebalancedFrequency)
    {
        $this->rebalanced_frequency = $rebalancedFrequency;

        return $this;
    }

    /**
     * Get rebalanced_frequency.
     *
     * @return int
     */
    public function getRebalancedFrequency()
    {
        return $this->rebalanced_frequency;
    }

    public function getRebalancedFrequencyName()
    {
        if (isset(self::$rebalanced_frequency_choices[$this->rebalanced_frequency])) {
            return self::$rebalanced_frequency_choices[$this->rebalanced_frequency];
        }

        return;
    }

    public function isRebalancedFrequencyToleranceBand()
    {
        return self::REBALANCED_FREQUENCY_TOLERANCE_BANDS === $this->getRebalancedFrequency();
    }

    /**
     * @var int
     */
    private $state_id;

    /**
     * @param \App\Entity\State
     */
    private $state;

    /**
     * Set state_id.
     *
     * @param int $stateId
     *
     * @return RiaCompanyInformation
     */
    public function setStateId($stateId)
    {
        $this->state_id = $stateId;

        return $this;
    }

    /**
     * Get state_id.
     *
     * @return int
     */
    public function getStateId()
    {
        return $this->state_id;
    }

    /**
     * Set state.
     *
     * @param \App\Entity\State $state
     *
     * @return RiaCompanyInformation
     */
    public function setState(State $state = null)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state.
     *
     * @return \App\Entity\State
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @var int
     */
    private $risk_adjustment;

    /**
     * Set risk_adjustment.
     *
     * @param int $riskAdjustment
     *
     * @return RiaCompanyInformation
     */
    public function setRiskAdjustment($riskAdjustment)
    {
        $this->risk_adjustment = $riskAdjustment;

        return $this;
    }

    /**
     * Get risk_adjustment.
     *
     * @return int
     */
    public function getRiskAdjustment()
    {
        return $this->risk_adjustment;
    }

    /**
     * Get risk adjustment factor.
     *
     * @return int
     */
    public function getRiskAdjustmentFactor()
    {
        $riskAdjustment = $this->getRiskAdjustment();

        if (!$riskAdjustment) {
            return 0;
        }

        $factor = ($riskAdjustment - 5) * -1;

        return $factor;
    }

    /**
     * Add fee.
     *
     * @param \App\Entity\Fee $fee
     *
     * @return RiaCompanyInformation
     */
    public function addFee(Fee $fee)
    {
        return $this;
    }

    /**
     * Remove fee.
     *
     * @param \App\Entity\Fee $fee
     */
    public function removeFee(Fee $fee)
    {
    }

    /**
     * Get fees.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @var string
     */
    private $primary_first_name;

    /**
     * @var string
     */
    private $primary_last_name;

    /**
     * Set primary_first_name.
     *
     * @param string $primaryFirstName
     *
     * @return RiaCompanyInformation
     */
    public function setPrimaryFirstName($primaryFirstName)
    {
        $this->primary_first_name = $primaryFirstName;

        return $this;
    }

    /**
     * Get primary_first_name.
     *
     * @return string
     */
    public function getPrimaryFirstName()
    {
        return $this->primary_first_name;
    }

    /**
     * Set primary_last_name.
     *
     * @param string $primaryLastName
     *
     * @return RiaCompanyInformation
     */
    public function setPrimaryLastName($primaryLastName)
    {
        $this->primary_last_name = $primaryLastName;

        return $this;
    }

    /**
     * Get primary_last_name.
     *
     * @return string
     */
    public function getPrimaryLastName()
    {
        return $this->primary_last_name;
    }

    /**
     * @var bool
     */
    private $use_municipal_bond = true;

    /**
     * Set use_municipal_bond.
     *
     * @param bool $useMunicipalBond
     *
     * @return RiaCompanyInformation
     */
    public function setUseMunicipalBond($useMunicipalBond)
    {
        $this->use_municipal_bond = $useMunicipalBond;

        return $this;
    }

    /**
     * Get use_municipal_bond.
     *
     * @return bool
     */
    public function getUseMunicipalBond()
    {
        return $this->use_municipal_bond;
    }

    /**
     * @var int
     */
    private $portfolio_model_id;

    /**
     * @var CeModel
     */
    private $portfolioModel;

    /**
     * Set portfolio_model_id.
     *
     * @param int $portfolioModelId
     *
     * @return RiaCompanyInformation
     */
    public function setPortfolioModelId($portfolioModelId)
    {
        $this->portfolio_model_id = $portfolioModelId;

        return $this;
    }

    /**
     * Get portfolio_model_id.
     *
     * @return int
     */
    public function getPortfolioModelId()
    {
        return $this->portfolio_model_id;
    }

    /**
     * Set portfolioModel.
     *
     * @param CeModelInterface $portfolioModel
     *
     * @return RiaCompanyInformation
     */
    public function setPortfolioModel(CeModelInterface $portfolioModel = null)
    {
        $this->portfolioModel = $portfolioModel;

        return $this;
    }

    /**
     * Get portfolioModel.
     *
     * @return CeModelInterface
     */
    public function getPortfolioModel()
    {
        return $this->portfolioModel;
    }

    /**
     * Set activated.
     *
     * @param bool $activated
     *
     * @return RiaCompanyInformation
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $this;
    }

    /**
     * Get activated.
     *
     * @return bool
     */
    public function getActivated()
    {
        return $this->activated;
    }

    /**
     * @var float
     */
    private $transaction_amount;

    /**
     * @var float
     */
    private $transaction_amount_percent;

    /**
     * @var bool
     */
    private $is_transaction_fees;

    /**
     * @var bool
     */
    private $is_transaction_minimums;

    /**
     * Set transaction_amount.
     *
     * @param float $transactionAmount
     *
     * @return RiaCompanyInformation
     */
    public function setTransactionAmount($transactionAmount)
    {
        $this->transaction_amount = $transactionAmount;

        return $this;
    }

    /**
     * Get transaction_amount.
     *
     * @return float
     */
    public function getTransactionAmount()
    {
        return $this->transaction_amount;
    }

    /**
     * Set transaction_amount_percent.
     *
     * @param float $transactionAmountPercent
     *
     * @return RiaCompanyInformation
     */
    public function setTransactionAmountPercent($transactionAmountPercent)
    {
        $this->transaction_amount_percent = $transactionAmountPercent;

        return $this;
    }

    /**
     * Get transaction_amount_percent.
     *
     * @return float
     */
    public function getTransactionAmountPercent()
    {
        return $this->transaction_amount_percent;
    }

    /**
     * Set is_transaction_fees.
     *
     * @param bool $isTransactionFees
     *
     * @return RiaCompanyInformation
     */
    public function setIsTransactionFees($isTransactionFees)
    {
        $this->is_transaction_fees = $isTransactionFees;

        return $this;
    }

    /**
     * Get is_transaction_fees.
     *
     * @return bool
     */
    public function getIsTransactionFees()
    {
        return $this->is_transaction_fees;
    }

    /**
     * Set is_transaction_minimums.
     *
     * @param bool $isTransactionMinimums
     *
     * @return RiaCompanyInformation
     */
    public function setIsTransactionMinimums($isTransactionMinimums)
    {
        $this->is_transaction_minimums = $isTransactionMinimums;

        return $this;
    }

    /**
     * Get is_transaction_minimums.
     *
     * @return bool
     */
    public function getIsTransactionMinimums()
    {
        return $this->is_transaction_minimums;
    }

    private $is_transaction_redemption_fees;

    /**
     * Set is_transaction_redemption_fees.
     *
     * @param bool $isTransactionRedemptionFees
     *
     * @return RiaCompanyInformation
     */
    public function setIsTransactionRedemptionFees($isTransactionRedemptionFees)
    {
        $this->is_transaction_redemption_fees = $isTransactionRedemptionFees;

        return $this;
    }

    /**
     * Get is_transaction_redemption_fees.
     *
     * @return bool
     */
    public function getIsTransactionRedemptionFees()
    {
        return $this->is_transaction_redemption_fees;
    }

    public function isShowTransactionEdit()
    {
        return $this->getIsTransactionFees() || $this->getIsTransactionMinimums() || $this->getIsTransactionRedemptionFees();
    }

    /**
     * @var bool
     */
    private $is_tax_loss_harvesting;

    /**
     * @var float
     */
    private $tax_loss_harvesting;

    /**
     * @var float
     */
    private $tax_loss_harvesting_percent;

    /**
     * @var float
     */
    private $tax_loss_harvesting_minimum;

    /**
     * @var float
     */
    private $tax_loss_harvesting_minimum_percent;

    /**
     * Set is_tax_loss_harvesting.
     *
     * @param bool $isTaxLossHarvesting
     *
     * @return RiaCompanyInformation
     */
    public function setIsTaxLossHarvesting($isTaxLossHarvesting)
    {
        $this->is_tax_loss_harvesting = $isTaxLossHarvesting;

        return $this;
    }

    /**
     * Get is_tax_loss_harvesting.
     *
     * @return bool
     */
    public function getIsTaxLossHarvesting()
    {
        return $this->is_tax_loss_harvesting;
    }

    /**
     * Set tax_loss_harvesting.
     *
     * @param float $taxLossHarvesting
     *
     * @return RiaCompanyInformation
     */
    public function setTaxLossHarvesting($taxLossHarvesting)
    {
        $this->tax_loss_harvesting = $taxLossHarvesting;

        return $this;
    }

    /**
     * Get tax_loss_harvesting.
     *
     * @return float
     */
    public function getTaxLossHarvesting()
    {
        return $this->tax_loss_harvesting;
    }

    /**
     * Set tax_loss_harvesting_percent.
     *
     * @param float $taxLossHarvestingPercent
     *
     * @return RiaCompanyInformation
     */
    public function setTaxLossHarvestingPercent($taxLossHarvestingPercent)
    {
        $this->tax_loss_harvesting_percent = $taxLossHarvestingPercent;

        return $this;
    }

    /**
     * Get tax_loss_harvesting_percent.
     *
     * @return float
     */
    public function getTaxLossHarvestingPercent()
    {
        return $this->tax_loss_harvesting_percent;
    }

    /**
     * Set tax_loss_harvesting_minimum.
     *
     * @param float $taxLossHarvestingMinimum
     *
     * @return RiaCompanyInformation
     */
    public function setTaxLossHarvestingMinimum($taxLossHarvestingMinimum)
    {
        $this->tax_loss_harvesting_minimum = $taxLossHarvestingMinimum;

        return $this;
    }

    /**
     * Get tax_loss_harvesting_minimum.
     *
     * @return float
     */
    public function getTaxLossHarvestingMinimum()
    {
        return $this->tax_loss_harvesting_minimum;
    }

    /**
     * Set tax_loss_harvesting_minimum_percent.
     *
     * @param float $taxLossHarvestingMinimumPercent
     *
     * @return RiaCompanyInformation
     */
    public function setTaxLossHarvestingMinimumPercent($taxLossHarvestingMinimumPercent)
    {
        $this->tax_loss_harvesting_minimum_percent = $taxLossHarvestingMinimumPercent;

        return $this;
    }

    /**
     * Get tax_loss_harvesting_minimum_percent.
     *
     * @return float
     */
    public function getTaxLossHarvestingMinimumPercent()
    {
        return $this->tax_loss_harvesting_minimum_percent;
    }

    /**
     * Set is_use_qualified_models.
     *
     * @param bool $isUseQualifiedModels
     *
     * @return RiaCompanyInformation
     */
    public function setIsUseQualifiedModels($isUseQualifiedModels)
    {
        if (self::ACCOUNT_MANAGED_ACCOUNT === $this->account_managed) {
            $this->is_use_qualified_models = $isUseQualifiedModels;
        } else {
            $this->is_use_qualified_models = false;
        }

        if ($this->is_use_qualified_models) {
            $this->setUseMunicipalBond(false);
        }

        return $this;
    }

    /**
     * Get is_use_qualified_models.
     *
     * @return bool
     */
    public function getIsUseQualifiedModels()
    {
        return $this->is_use_qualified_models;
    }

    /**
     * Check is use qualified models.
     *
     * @return bool
     */
    public function isUseQualifiedModels()
    {
        // if RIA selected an Account Level and agreed to use qualified models
        if (1 === $this->account_managed && $this->is_use_qualified_models) {
            return true;
        }

        return false;
    }

    public static function getPortfolioProcessingChoices()
    {
        return array_combine(array_values(self::$portfolioProcessingChoices), array_values(self::$portfolioProcessingChoices));
    }

    /**
     * Set portfolio_processing.
     *
     * @param int $portfolioProcessing
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setPortfolioProcessing($portfolioProcessing)
    {
        $this->portfolio_processing = $portfolioProcessing;

        return $this;
    }

    /**
     * Get portfolio_processing.
     *
     * @return int
     */
    public function getPortfolioProcessing()
    {
        return $this->portfolio_processing;
    }

    /**
     * Returns true if portfolio_processing is PORTFOLIO_PROCESSING_STRAIGHT_THROUGH.
     *
     * @return bool
     */
    public function isStraightThroughProcessing()
    {
        return self::PORTFOLIO_PROCESSING_STRAIGHT_THROUGH === $this->portfolio_processing;
    }

    /**
     * Returns true if portfolio_processing is PORTFOLIO_PROCESSING_COLLABORATIVE.
     *
     * @return bool
     */
    public function isCollaborativeProcessing()
    {
        return self::PORTFOLIO_PROCESSING_COLLABORATIVE === $this->portfolio_processing;
    }

    /**
     * Get portfolio_processing as string.
     *
     * @return string
     */
    public function getPortfolioProcessingAsString()
    {
        return self::$portfolioProcessingChoices[$this->getPortfolioProcessing()];
    }

    /**
     * @var int
     */
    private $custodian_id;

    /**
     * @var bool
     */
    private $allow_non_electronically_signing;

    /**
     * Set custodian_id.
     *
     * @param int $custodianId
     *
     * @return RiaCompanyInformation
     */
    public function setCustodianId($custodianId)
    {
        $this->custodian_id = $custodianId;

        return $this;
    }

    /**
     * Get custodian_id.
     *
     * @return int
     */
    public function getCustodianId()
    {
        return $this->custodian_id;
    }

    /**
     * Set allow_non_electronically_signing.
     *
     * @param bool $allowNonElectronicallySigning
     *
     * @return RiaCompanyInformation
     */
    public function setAllowNonElectronicallySigning($allowNonElectronicallySigning)
    {
        $this->allow_non_electronically_signing = $allowNonElectronicallySigning;

        return $this;
    }

    /**
     * Get allow_non_electronically_signing.
     *
     * @return bool
     */
    public function getAllowNonElectronicallySigning()
    {
        return $this->allow_non_electronically_signing;
    }

    /**
     * @param \App\Entity\Custodian
     */
    private $custodian;

    /**
     * Set custodian.
     *
     * @param \App\Entity\Custodian $custodian
     *
     * @return RiaCompanyInformation
     */
    public function setCustodian(Custodian $custodian = null)
    {
        $this->custodian = $custodian;

        return $this;
    }

    /**
     * Get custodian.
     *
     * @return \App\Entity\Custodian
     */
    public function getCustodian()
    {
        return $this->custodian;
    }

    /**
     * @var string
     */
    private $slug;

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return RiaCompanyInformation
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @var bool
     */
    private $is_show_expected_costs;

    /**
     * Set is_show_expected_costs.
     *
     * @param bool $isShowExpectedCosts
     *
     * @return RiaCompanyInformation
     */
    public function setIsShowExpectedCosts($isShowExpectedCosts)
    {
        $this->is_show_expected_costs = $isShowExpectedCosts;

        return $this;
    }

    /**
     * Get is_show_expected_costs.
     *
     * @return bool
     */
    public function getIsShowExpectedCosts()
    {
        return $this->is_show_expected_costs;
    }

//    public function isShowPerformanceSection(User $client = null, CeModel $model)
//    {
//        if (null !== $client) {
//            if (!$client->hasRole('ROLE_CLIENT')) {
//                return false;
//            }
//
//            if (!$model->getGenerousMarketReturn() || !$model->getLowMarketReturn()) {
//                return false;
//            }
//
//            if (!$model->getForecast() && $client->getRegistrationStep() == 3) {
//                return false;
//            }
//        }
//
//        return true;
//    }
    /**
     * @var string
     */
    private $fax_number;

    /**
     * Set fax_number.
     *
     * @param string $faxNumber
     *
     * @return RiaCompanyInformation
     */
    public function setFaxNumber($faxNumber)
    {
        $this->fax_number = $faxNumber;

        return $this;
    }

    /**
     * Get fax_number.
     *
     * @return string
     */
    public function getFaxNumber()
    {
        return $this->fax_number;
    }

    /**
     * Get adv document.
     *
     * @return Document|null
     */
    public function getAdvDocument()
    {
        $advDocument = null;

        $ria = $this->getRia();
        if ($ria) {
            $documents = $ria->getUserDocuments();
            foreach ($documents as $document) {
                if (Document::TYPE_ADV === $document->getType()) {
                    $advDocument = $document;
                    break;
                }
            }
        }

        return $advDocument;
    }

    /**
     * @var float
     */
    private $stop_tlh_value;

    /**
     * Set stop_tlh_value.
     *
     * @param float $stopTlhValue
     *
     * @return RiaCompanyInformation
     */
    public function setStopTlhValue($stopTlhValue)
    {
        $this->stop_tlh_value = $stopTlhValue;

        return $this;
    }

    /**
     * Get stop_tlh_value.
     *
     * @return float
     */
    public function getStopTlhValue()
    {
        return $this->stop_tlh_value;
    }

    /**
     * Set relationship_type.
     *
     * @param int $relationshipType
     *
     * @return RiaCompanyInformation
     */
    public function setRelationshipType($relationshipType)
    {
        $this->relationship_type = $relationshipType;

        return $this;
    }

    /**
     * Get relationship_type.
     *
     * @return int
     */
    public function getRelationshipType()
    {
        return $this->relationship_type;
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $advisorCodes;

    /**
     * Add advisor_codes.
     *
     * @param \App\Entity\AdvisorCode $advisorCode
     *
     * @return RiaCompanyInformation
     */
    public function addAdvisorCode(AdvisorCode $advisorCode)
    {
        $this->advisorCodes[] = $advisorCode;

        return $this;
    }

    /**
     * Remove advisor_codes.
     *
     * @param \App\Entity\AdvisorCode $advisorCode
     */
    public function removeAdvisorCode(AdvisorCode $advisorCode)
    {
        $this->advisorCodes->removeElement($advisorCode);
    }

    /**
     * Get advisor_codes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdvisorCodes()
    {
        return $this->advisorCodes;
    }

    /**
     * Set tlh_buy_back_original.
     *
     * @param bool $tlhBuyBackOriginal
     *
     * @return RiaCompanyInformation
     */
    public function setTlhBuyBackOriginal($tlhBuyBackOriginal)
    {
        $this->tlh_buy_back_original = $tlhBuyBackOriginal;

        return $this;
    }

    /**
     * Get tlh_buy_back_original.
     *
     * @return bool
     */
    public function getTlhBuyBackOriginal()
    {
        return $this->tlh_buy_back_original;
    }

    /**
     * @return mixed
     */
    public function getCustodianKey()
    {
        return $this->custodianKey;
    }

    /**
     * @param mixed $custodianKey
     */
    public function setCustodianKey($custodianKey): void
    {
        $this->custodianKey = $custodianKey;
    }

    /**
     * @return mixed
     */
    public function getCustodianSecret()
    {
        return $this->custodianSecret;
    }

    /**
     * @param mixed $custodianSecret
     */
    public function setCustodianSecret($custodianSecret): void
    {
        $this->custodianSecret = $custodianSecret;
    }
}
