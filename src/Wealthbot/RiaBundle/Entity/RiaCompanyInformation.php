<?php

namespace Wealthbot\RiaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Wealthbot\AdminBundle\Entity\CeModel;
use Wealthbot\AdminBundle\Model\CeModelInterface;
use Wealthbot\UserBundle\Entity\Document;
use Wealthbot\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Wealthbot\RiaBundle\Entity\RiaCompanyInformation
 */
class RiaCompanyInformation
{
    /**
     * @var integer $id
     */
    private $id;

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var string $website
     */
    private $website;

    /**
     * @var string $address
     */
    private $address;

    /**
     * @var string $office
     */
    private $office;

    /**
     * @var string $city
     */
    private $city;

    /**
     * @var string $zipcode
     */
    private $zipcode;

    /**
     * @var string $phone_number
     */
    private $phone_number;

    /**
     * @var string $contact_email
     */
    private $contact_email;

    /**
     * @var string $logo
     */
    private $logo;

    /**
     * @var integer $ria_user_id
     */
    private $ria_user_id;

    /**
     * @var \Wealthbot\UserBundle\Entity\User
     */
    private $ria;

    /**
     * @var integer $account_managed
     */
    private $account_managed;

    /**
     * @var boolean $is_allow_retirement_plan
     */
    private $is_allow_retirement_plan = true;

    /**
     * @var boolean
     */
    private $activated;

    /**
     * @var boolean
     */
    private $is_use_qualified_models;

    /**
     * @var integer
     */
    private $relationship_type;

    const RELATIONSHIP_TYPE_LICENSE_FEE = 0;
    const RELATIONSHIP_TYPE_TAMP = 1;

    public static $relationship_type_choices = array(
        self::RELATIONSHIP_TYPE_TAMP => 'TAMP',
        self::RELATIONSHIP_TYPE_LICENSE_FEE => 'License Fee'
    );

    // constants for $account_managed
    const ACCOUNT_MANAGED_ACCOUNT = 1;
    const ACCOUNT_MANAGED_HOUSEHOLD = 2;
    const ACCOUNT_MANAGED_ACCOUNT_OR_HOUSEHOLD = 3;

    /**
     * Choices for account management
     */
    public static $account_managed_choices = array(
        self::ACCOUNT_MANAGED_ACCOUNT => 'Account Level',
        self::ACCOUNT_MANAGED_HOUSEHOLD => 'Household Level',
        self::ACCOUNT_MANAGED_ACCOUNT_OR_HOUSEHOLD => 'Account or Household Level'
    );

    /**
     * @var array Choices for rebalanced method
     */
    public static $rebalanced_method_choices = array(
        1 => 'Asset Class',
        2 => 'Subclass'
    );


    const REBALANCED_FREQUENCY_QUARTERLY = 1;
    const REBALANCED_FREQUENCY_SEMI_ANNUALLY = 2;
    const REBALANCED_FREQUENCY_ANNUALLY = 3;
    const REBALANCED_FREQUENCY_TOLERANCE_BANDS = 4;

    /**
     * @var array Choices for rebalanced frequency choices
     */
    public static $rebalanced_frequency_choices = array(
        1 => 'Quarterly',
        2 => 'Semi-Annually',
        3 => 'Annually',
        4 => 'Tolerance Bands'
    );

    /**
     * @var integer
     */
    private $portfolio_processing;

    const PORTFOLIO_PROCESSING_STRAIGHT_THROUGH = 1;
    const PORTFOLIO_PROCESSING_COLLABORATIVE = 2;

    /**
     * @var array Choices for portfolio processing
     */
    private static $portfolioProcessingChoices = array(
        1 => 'Straight-Through',
        2 => 'Collaborative'
    );

    /**
     * @var boolean
     */
    private $tlh_buy_back_original;


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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return RiaCompanyInformation
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set website
     *
     * @param string $website
     * @return RiaCompanyInformation
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website
     *
     * @return string
     */
    public function getWebsite()
    {
        $url = $this->website;

        if(!preg_match("~^(?:f|ht)tps?://~i", $url)) {
            $url = "http://" . $url;
        }

        return $url;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return RiaCompanyInformation
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set office
     *
     * @param string $office
     * @return RiaCompanyInformation
     */
    public function setOffice($office)
    {
        $this->office = $office;

        return $this;
    }

    /**
     * Get office
     *
     * @return string
     */
    public function getOffice()
    {
        return $this->office;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return RiaCompanyInformation
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set zipcode
     *
     * @param string $zipcode
     * @return RiaCompanyInformation
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Get zipcode
     *
     * @return string
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set phone_number
     *
     * @param string $phoneNumber
     * @return RiaCompanyInformation
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phone_number = $phoneNumber;

        return $this;
    }

    /**
     * Get phone_number
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phone_number;
    }

    /**
     * Set contact_email
     *
     * @param string $contactEmail
     * @return RiaCompanyInformation
     */
    public function setContactEmail($contactEmail)
    {
        $this->contact_email = $contactEmail;

        return $this;
    }

    /**
     * Get contact_email
     *
     * @return string
     */
    public function getContactEmail()
    {
        return $this->contact_email;
    }

    /**
     * Set logo
     *
     * @param string $logo
     * @return RiaCompanyInformation
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo
     *
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set ria_user_id
     *
     * @param integer $riaUserId
     * @return RiaCompanyInformation
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
     * Set ria
     *
     * @param \Wealthbot\UserBundle\Entity\User $ria
     * @return RiaCompanyInformation
     */
    public function setRia(\Wealthbot\UserBundle\Entity\User $ria = null)
    {
        $this->ria = $ria;

        return $this;
    }

    /**
     * Get ria
     *
     * @return \Wealthbot\UserBundle\Entity\User
     */
    public function getRia()
    {
        return $this->ria;
    }

    /**
     * Virtual field for upload file
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
        // the absolute directory path where uploaded documents should be saved
        return __DIR__.'/../../../../'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return 'uploads/ria_company_logos';
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
            mkdir($this->getUploadRootDir());
        }

        $image = new \Imagick($this->logo_file->getPathname());
        $image->scaleImage(300, 0);
        $image->writeimage($this->getUploadRootDir().'/'.$this->logo);

        unset($this->logo_file);
    }

    public function removeUpload()
    {
        if ($file = $this->getAbsoluteLogo()) {
            unlink($file);
        }
    }

    /**
     * Set account_managed
     *
     * @param integer $accountManaged
     * @return RiaCompanyInformation
     */
    public function setAccountManaged($accountManaged)
    {
        $this->account_managed = $accountManaged;

        return $this;
    }

    /**
     * Get account_managed
     *
     * @return integer
     */
    public function getAccountManaged()
    {
        return $this->account_managed;
    }

    public function isAccountManagedLevel()
    {
        return $this->getAccountManaged() === self::ACCOUNT_MANAGED_ACCOUNT ? true : false;
    }

    public function isHouseholdManagedLevel()
    {
        return $this->getAccountManaged() === self::ACCOUNT_MANAGED_HOUSEHOLD ? true : false;
    }

    public function isClientByClientManagedLevel()
    {
        return $this->getAccountManaged() === self::ACCOUNT_MANAGED_ACCOUNT_OR_HOUSEHOLD ? true : false;
    }

    public function getAccountManagementAsString()
    {
        return self::$account_managed_choices[$this->getAccountManaged()];
    }

    public function getAccountManagedChoices()
    {
        $choices = self::$account_managed_choices;

        if ($this->getPortfolioProcessing() == self::PORTFOLIO_PROCESSING_STRAIGHT_THROUGH) {
            unset($choices[self::ACCOUNT_MANAGED_ACCOUNT_OR_HOUSEHOLD]);
        }

        return $choices;
    }

    public function isShowSubclassPriority()
    {
        return ($this->isClientByClientManagedLevel() || $this->isHouseholdManagedLevel() || ($this->getIsAllowRetirementPlan() && $this->isAccountManagedLevel()));
    }

    public function isRelationTypeTamp()
    {
        return $this->getRelationshipType() === self::RELATIONSHIP_TYPE_TAMP;
    }

    public function isRelationTypeLicenseFee()
    {
        return $this->getRelationshipType() === self::RELATIONSHIP_TYPE_LICENSE_FEE;
    }

    /**
     * Set is_allow_retirement_plan
     *
     * @param boolean $isAllowRetirementPlan
     * @return RiaCompanyInformation
     */
    public function setIsAllowRetirementPlan($isAllowRetirementPlan)
    {
        $this->is_allow_retirement_plan = $isAllowRetirementPlan;

        return $this;
    }

    /**
     * Get is_allow_retirement_plan
     *
     * @return boolean
     */
    public function getIsAllowRetirementPlan()
    {
        return $this->is_allow_retirement_plan;
    }
    /**
     * @var integer $minimum_billing_fee
     */
    private $minimum_billing_fee;

    /**
     * @var boolean $is_show_client_expected_asset_class
     */
    private $is_show_client_expected_asset_class;


    /**
     * Set minimum_billing_fee
     *
     * @param integer $minimumBillingFee
     * @return RiaCompanyInformation
     */
    public function setMinimumBillingFee($minimumBillingFee)
    {
        $this->minimum_billing_fee = $minimumBillingFee;

        return $this;
    }

    /**
     * Get minimum_billing_fee
     *
     * @return integer
     */
    public function getMinimumBillingFee()
    {
        return $this->minimum_billing_fee;
    }

    /**
     * Set is_show_client_expected_asset_class
     *
     * @param boolean $isShowClientExpectedAssetClass
     * @return RiaCompanyInformation
     */
    public function setIsShowClientExpectedAssetClass($isShowClientExpectedAssetClass)
    {
        $this->is_show_client_expected_asset_class = $isShowClientExpectedAssetClass;

        return $this;
    }

    /**
     * Get is_show_client_expected_asset_class
     *
     * @return boolean
     */
    public function getIsShowClientExpectedAssetClass()
    {
        return $this->is_show_client_expected_asset_class;
    }
    /**
     * @var float $clients_tax_bracket
     */
    private $clients_tax_bracket;


    /**
     * Set clients_tax_bracket
     *
     * @param float $clientsTaxBracket
     * @return RiaCompanyInformation
     */
    public function setClientsTaxBracket($clientsTaxBracket)
    {
        $this->clients_tax_bracket = $clientsTaxBracket;

        return $this;
    }

    /**
     * Get clients_tax_bracket
     *
     * @return float
     */
    public function getClientsTaxBracket()
    {
        return $this->clients_tax_bracket;
    }

    /**
     * @var boolean $is_searchable_db
     */
    private $is_searchable_db;

    /**
     * @var float $min_asset_size
     */
    private $min_asset_size;

    /**
     * @var string $adv_copy
     */
    private $adv_copy;


    /**
     * Set is_searchable_db
     *
     * @param boolean $isSearchableDb
     * @return RiaCompanyInformation
     */
    public function setIsSearchableDb($isSearchableDb)
    {
        $this->is_searchable_db = $isSearchableDb;

        return $this;
    }

    /**
     * Get is_searchable_db
     *
     * @return boolean
     */
    public function getIsSearchableDb()
    {
        return $this->is_searchable_db;
    }

    /**
     * Set min_asset_size
     *
     * @param float $minAssetSize
     * @return RiaCompanyInformation
     */
    public function setMinAssetSize($minAssetSize)
    {
        $this->min_asset_size = $minAssetSize;

        return $this;
    }

    /**
     * Get min_asset_size
     *
     * @return float
     */
    public function getMinAssetSize()
    {
        return $this->min_asset_size;
    }

    /**
     * Set adv_copy
     *
     * @param string $advCopy
     * @return RiaCompanyInformation
     */
    public function setAdvCopy($advCopy)
    {
        $this->adv_copy = $advCopy;

        return $this;
    }

    /**
     * Get adv_copy
     *
     * @return string
     */
    public function getAdvCopy()
    {
        return $this->adv_copy;
    }

    /**
     * Virtual field for upload file
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
        return __DIR__.'/../../../../'.$this->getUploadAdvCopyDir();
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
     * @var integer $rebalanced_method
     */
    private $rebalanced_method;

    /**
     * @var integer $rebalanced_frequency
     */
    private $rebalanced_frequency;


    /**
     * Set rebalanced_method
     *
     * @param integer $rebalancedMethod
     * @return RiaCompanyInformation
     */
    public function setRebalancedMethod($rebalancedMethod)
    {
        $this->rebalanced_method = $rebalancedMethod;

        return $this;
    }

    /**
     * Get rebalanced_method
     *
     * @return integer
     */
    public function getRebalancedMethod()
    {
        return $this->rebalanced_method;
    }

    /**
     * Set rebalanced_frequency
     *
     * @param integer $rebalancedFrequency
     * @return RiaCompanyInformation
     */
    public function setRebalancedFrequency($rebalancedFrequency)
    {
        $this->rebalanced_frequency = $rebalancedFrequency;

        return $this;
    }

    /**
     * Get rebalanced_frequency
     *
     * @return integer
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

        return null;
    }

    public function isRebalancedFrequencyToleranceBand()
    {
        return $this->getRebalancedFrequency() === self::REBALANCED_FREQUENCY_TOLERANCE_BANDS;
    }

    /**
     * @var integer $state_id
     */
    private $state_id;

    /**
     * @var \Wealthbot\AdminBundle\Entity\State
     */
    private $state;


    /**
     * Set state_id
     *
     * @param integer $stateId
     * @return RiaCompanyInformation
     */
    public function setStateId($stateId)
    {
        $this->state_id = $stateId;

        return $this;
    }

    /**
     * Get state_id
     *
     * @return integer
     */
    public function getStateId()
    {
        return $this->state_id;
    }

    /**
     * Set state
     *
     * @param \Wealthbot\AdminBundle\Entity\State $state
     * @return RiaCompanyInformation
     */
    public function setState(\Wealthbot\AdminBundle\Entity\State $state = null)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return \Wealthbot\AdminBundle\Entity\State
     */
    public function getState()
    {
        return $this->state;
    }
    /**
     * @var integer $risk_adjustment
     */
    private $risk_adjustment;


    /**
     * Set risk_adjustment
     *
     * @param integer $riskAdjustment
     * @return RiaCompanyInformation
     */
    public function setRiskAdjustment($riskAdjustment)
    {
        $this->risk_adjustment = $riskAdjustment;

        return $this;
    }

    /**
     * Get risk_adjustment
     *
     * @return integer
     */
    public function getRiskAdjustment()
    {
        return $this->risk_adjustment;
    }

    /**
     * Get risk adjustment factor
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
     * Add fee
     *
     * @param \Wealthbot\AdminBundle\Entity\Fee $fee
     * @return RiaCompanyInformation
     */
    public function addFee(\Wealthbot\AdminBundle\Entity\Fee $fee)
    {
        return $this;
    }

    /**
     * Remove fee
     *
     * @param \Wealthbot\AdminBundle\Entity\Fee $fee
     */
    public function removeFee(\Wealthbot\AdminBundle\Entity\Fee $fee)
    {

    }

    /**
     * Get fees
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getItems()
    {
        return $this->items;
    }
    /**
     * @var string $primary_first_name
     */
    private $primary_first_name;

    /**
     * @var string $primary_last_name
     */
    private $primary_last_name;


    /**
     * Set primary_first_name
     *
     * @param string $primaryFirstName
     * @return RiaCompanyInformation
     */
    public function setPrimaryFirstName($primaryFirstName)
    {
        $this->primary_first_name = $primaryFirstName;

        return $this;
    }

    /**
     * Get primary_first_name
     *
     * @return string
     */
    public function getPrimaryFirstName()
    {
        return $this->primary_first_name;
    }

    /**
     * Set primary_last_name
     *
     * @param string $primaryLastName
     * @return RiaCompanyInformation
     */
    public function setPrimaryLastName($primaryLastName)
    {
        $this->primary_last_name = $primaryLastName;

        return $this;
    }

    /**
     * Get primary_last_name
     *
     * @return string
     */
    public function getPrimaryLastName()
    {
        return $this->primary_last_name;
    }
    /**
     * @var boolean $use_municipal_bond
     */
    private $use_municipal_bond = true;


    /**
     * Set use_municipal_bond
     *
     * @param boolean $useMunicipalBond
     * @return RiaCompanyInformation
     */
    public function setUseMunicipalBond($useMunicipalBond)
    {
        $this->use_municipal_bond = $useMunicipalBond;

        return $this;
    }

    /**
     * Get use_municipal_bond
     *
     * @return boolean
     */
    public function getUseMunicipalBond()
    {
        return $this->use_municipal_bond;
    }
    /**
     * @var integer $portfolio_model_id
     */
    private $portfolio_model_id;

    /**
     * @var CeModel
     */
    private $portfolioModel;


    /**
     * Set portfolio_model_id
     *
     * @param integer $portfolioModelId
     * @return RiaCompanyInformation
     */
    public function setPortfolioModelId($portfolioModelId)
    {
        $this->portfolio_model_id = $portfolioModelId;

        return $this;
    }

    /**
     * Get portfolio_model_id
     *
     * @return integer
     */
    public function getPortfolioModelId()
    {
        return $this->portfolio_model_id;
    }

    /**
     * Set portfolioModel
     *
     * @param CeModelInterface $portfolioModel
     * @return RiaCompanyInformation
     */
    public function setPortfolioModel(CeModelInterface $portfolioModel = null)
    {
        $this->portfolioModel = $portfolioModel;

        return $this;
    }

    /**
     * Get portfolioModel
     *
     * @return CeModelInterface
     */
    public function getPortfolioModel()
    {
        return $this->portfolioModel;
    }


    /**
     * Set activated
     *
     * @param boolean $activated
     * @return RiaCompanyInformation
     */
    public function setActivated($activated)
    {
        $this->activated = $activated;

        return $this;
    }

    /**
     * Get activated
     *
     * @return boolean
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
     * @var boolean
     */
    private $is_transaction_fees;

    /**
     * @var boolean
     */
    private $is_transaction_minimums;

    /**
     * Set transaction_amount
     *
     * @param float $transactionAmount
     * @return RiaCompanyInformation
     */
    public function setTransactionAmount($transactionAmount)
    {
        $this->transaction_amount = $transactionAmount;

        return $this;
    }

    /**
     * Get transaction_amount
     *
     * @return float
     */
    public function getTransactionAmount()
    {
        return $this->transaction_amount;
    }

    /**
     * Set transaction_amount_percent
     *
     * @param float $transactionAmountPercent
     * @return RiaCompanyInformation
     */
    public function setTransactionAmountPercent($transactionAmountPercent)
    {
        $this->transaction_amount_percent = $transactionAmountPercent;

        return $this;
    }

    /**
     * Get transaction_amount_percent
     *
     * @return float
     */
    public function getTransactionAmountPercent()
    {
        return $this->transaction_amount_percent;
    }

    /**
     * Set is_transaction_fees
     *
     * @param boolean $isTransactionFees
     * @return RiaCompanyInformation
     */
    public function setIsTransactionFees($isTransactionFees)
    {
        $this->is_transaction_fees = $isTransactionFees;

        return $this;
    }

    /**
     * Get is_transaction_fees
     *
     * @return boolean
     */
    public function getIsTransactionFees()
    {
        return $this->is_transaction_fees;
    }

    /**
     * Set is_transaction_minimums
     *
     * @param boolean $isTransactionMinimums
     * @return RiaCompanyInformation
     */
    public function setIsTransactionMinimums($isTransactionMinimums)
    {
        $this->is_transaction_minimums = $isTransactionMinimums;

        return $this;
    }

    /**
     * Get is_transaction_minimums
     *
     * @return boolean
     */
    public function getIsTransactionMinimums()
    {
        return $this->is_transaction_minimums;
    }

    private $is_transaction_redemption_fees;


    /**
     * Set is_transaction_redemption_fees
     *
     * @param boolean $isTransactionRedemptionFees
     * @return RiaCompanyInformation
     */
    public function setIsTransactionRedemptionFees($isTransactionRedemptionFees)
    {
        $this->is_transaction_redemption_fees = $isTransactionRedemptionFees;

        return $this;
    }

    /**
     * Get is_transaction_redemption_fees
     *
     * @return boolean
     */
    public function getIsTransactionRedemptionFees()
    {
        return $this->is_transaction_redemption_fees;
    }

    public function isShowTransactionEdit()
    {
        return ($this->getIsTransactionFees() || $this->getIsTransactionMinimums() || $this->getIsTransactionRedemptionFees());
    }
    /**
     * @var boolean
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
     * Set is_tax_loss_harvesting
     *
     * @param boolean $isTaxLossHarvesting
     * @return RiaCompanyInformation
     */
    public function setIsTaxLossHarvesting($isTaxLossHarvesting)
    {
        $this->is_tax_loss_harvesting = $isTaxLossHarvesting;

        return $this;
    }

    /**
     * Get is_tax_loss_harvesting
     *
     * @return boolean
     */
    public function getIsTaxLossHarvesting()
    {
        return $this->is_tax_loss_harvesting;
    }

    /**
     * Set tax_loss_harvesting
     *
     * @param float $taxLossHarvesting
     * @return RiaCompanyInformation
     */
    public function setTaxLossHarvesting($taxLossHarvesting)
    {
        $this->tax_loss_harvesting = $taxLossHarvesting;

        return $this;
    }

    /**
     * Get tax_loss_harvesting
     *
     * @return float
     */
    public function getTaxLossHarvesting()
    {
        return $this->tax_loss_harvesting;
    }

    /**
     * Set tax_loss_harvesting_percent
     *
     * @param float $taxLossHarvestingPercent
     * @return RiaCompanyInformation
     */
    public function setTaxLossHarvestingPercent($taxLossHarvestingPercent)
    {
        $this->tax_loss_harvesting_percent = $taxLossHarvestingPercent;

        return $this;
    }

    /**
     * Get tax_loss_harvesting_percent
     *
     * @return float
     */
    public function getTaxLossHarvestingPercent()
    {
        return $this->tax_loss_harvesting_percent;
    }

    /**
     * Set tax_loss_harvesting_minimum
     *
     * @param float $taxLossHarvestingMinimum
     * @return RiaCompanyInformation
     */
    public function setTaxLossHarvestingMinimum($taxLossHarvestingMinimum)
    {
        $this->tax_loss_harvesting_minimum = $taxLossHarvestingMinimum;

        return $this;
    }

    /**
     * Get tax_loss_harvesting_minimum
     *
     * @return float
     */
    public function getTaxLossHarvestingMinimum()
    {
        return $this->tax_loss_harvesting_minimum;
    }

    /**
     * Set tax_loss_harvesting_minimum_percent
     *
     * @param float $taxLossHarvestingMinimumPercent
     * @return RiaCompanyInformation
     */
    public function setTaxLossHarvestingMinimumPercent($taxLossHarvestingMinimumPercent)
    {
        $this->tax_loss_harvesting_minimum_percent = $taxLossHarvestingMinimumPercent;

        return $this;
    }

    /**
     * Get tax_loss_harvesting_minimum_percent
     *
     * @return float
     */
    public function getTaxLossHarvestingMinimumPercent()
    {
        return $this->tax_loss_harvesting_minimum_percent;
    }

    /**
     * Set is_use_qualified_models
     *
     * @param boolean $isUseQualifiedModels
     * @return RiaCompanyInformation
     */
    public function setIsUseQualifiedModels($isUseQualifiedModels)
    {
        if ($this->account_managed == self::ACCOUNT_MANAGED_ACCOUNT) {

            $this->is_use_qualified_models = $isUseQualifiedModels;
        } else {
            $this->is_use_qualified_models = false;
        }

        if($this->is_use_qualified_models) {
            $this->setUseMunicipalBond(false);
        }

        return $this;
    }

    /**
     * Get is_use_qualified_models
     *
     * @return boolean
     */
    public function getIsUseQualifiedModels()
    {
        return $this->is_use_qualified_models;
    }

    /**
     * Check is use qualified models
     *
     * @return bool
     */
    public function isUseQualifiedModels()
    {
        // if RIA selected an Account Level and agreed to use qualified models
        if($this->account_managed == 1 && $this->is_use_qualified_models) {
            return true;
        }
        return false;
    }

    public static function getPortfolioProcessingChoices()
    {
        return self::$portfolioProcessingChoices;
    }

    /**
     * Set portfolio_processing
     *
     * @param integer $portfolioProcessing
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setPortfolioProcessing($portfolioProcessing)
    {
        if (!is_null($portfolioProcessing) && !array_key_exists($portfolioProcessing, self::getPortfolioProcessingChoices())) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid value: %s for portfolio_processing column',
                $portfolioProcessing
            ));
        }

        $this->portfolio_processing = $portfolioProcessing;

        return $this;
    }

    /**
     * Get portfolio_processing
     *
     * @return integer
     */
    public function getPortfolioProcessing()
    {
        return $this->portfolio_processing;
    }

    /**
     * Returns true if portfolio_processing is PORTFOLIO_PROCESSING_STRAIGHT_THROUGH
     *
     * @return bool
     */
    public function isStraightThroughProcessing()
    {
        return ($this->portfolio_processing === self::PORTFOLIO_PROCESSING_STRAIGHT_THROUGH);
    }

    /**
     * Returns true if portfolio_processing is PORTFOLIO_PROCESSING_COLLABORATIVE
     *
     * @return bool
     */
    public function isCollaborativeProcessing()
    {
        return ($this->portfolio_processing === self::PORTFOLIO_PROCESSING_COLLABORATIVE);
    }

    /**
     * Get portfolio_processing as string
     *
     * @return string
     */
    public function getPortfolioProcessingAsString()
    {
        return self::$portfolioProcessingChoices[$this->getPortfolioProcessing()];
    }
    /**
     * @var integer
     */
    private $custodian_id;

    /**
     * @var boolean
     */
    private $allow_non_electronically_signing;


    /**
     * Set custodian_id
     *
     * @param integer $custodianId
     * @return RiaCompanyInformation
     */
    public function setCustodianId($custodianId)
    {
        $this->custodian_id = $custodianId;

        return $this;
    }

    /**
     * Get custodian_id
     *
     * @return integer
     */
    public function getCustodianId()
    {
        return $this->custodian_id;
    }

    /**
     * Set allow_non_electronically_signing
     *
     * @param boolean $allowNonElectronicallySigning
     * @return RiaCompanyInformation
     */
    public function setAllowNonElectronicallySigning($allowNonElectronicallySigning)
    {
        $this->allow_non_electronically_signing = $allowNonElectronicallySigning;

        return $this;
    }

    /**
     * Get allow_non_electronically_signing
     *
     * @return boolean
     */
    public function getAllowNonElectronicallySigning()
    {
        return $this->allow_non_electronically_signing;
    }
    /**
     * @var \Wealthbot\AdminBundle\Entity\Custodian
     */
    private $custodian;


    /**
     * Set custodian
     *
     * @param \Wealthbot\AdminBundle\Entity\Custodian $custodian
     * @return RiaCompanyInformation
     */
    public function setCustodian(\Wealthbot\AdminBundle\Entity\Custodian $custodian = null)
    {
        $this->custodian = $custodian;

        return $this;
    }

    /**
     * Get custodian
     *
     * @return \Wealthbot\AdminBundle\Entity\Custodian
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
     * Set slug
     *
     * @param string $slug
     * @return RiaCompanyInformation
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }
    /**
     * @var boolean
     */
    private $is_show_expected_costs;


    /**
     * Set is_show_expected_costs
     *
     * @param boolean $isShowExpectedCosts
     * @return RiaCompanyInformation
     */
    public function setIsShowExpectedCosts($isShowExpectedCosts)
    {
        $this->is_show_expected_costs = $isShowExpectedCosts;

        return $this;
    }

    /**
     * Get is_show_expected_costs
     *
     * @return boolean
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
     * Set fax_number
     *
     * @param string $faxNumber
     * @return RiaCompanyInformation
     */
    public function setFaxNumber($faxNumber)
    {
        $this->fax_number = $faxNumber;

        return $this;
    }

    /**
     * Get fax_number
     *
     * @return string
     */
    public function getFaxNumber()
    {
        return $this->fax_number;
    }

    /**
     * Get adv document
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
     * Set stop_tlh_value
     *
     * @param float $stopTlhValue
     * @return RiaCompanyInformation
     */
    public function setStopTlhValue($stopTlhValue)
    {
        $this->stop_tlh_value = $stopTlhValue;

        return $this;
    }

    /**
     * Get stop_tlh_value
     *
     * @return float
     */
    public function getStopTlhValue()
    {
        return $this->stop_tlh_value;
    }

    /**
     * Set relationship_type
     *
     * @param integer $relationshipType
     * @return RiaCompanyInformation
     */
    public function setRelationshipType($relationshipType)
    {
        $this->relationship_type = $relationshipType;

        return $this;
    }

    /**
     * Get relationship_type
     *
     * @return integer
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
     * Add advisor_codes
     *
     * @param \Wealthbot\RiaBundle\Entity\AdvisorCode $advisorCode
     * @return RiaCompanyInformation
     */
    public function addAdvisorCode(\Wealthbot\RiaBundle\Entity\AdvisorCode $advisorCode)
    {
        $this->advisorCodes[] = $advisorCode;

        return $this;
    }

    /**
     * Remove advisor_codes
     *
     * @param \Wealthbot\RiaBundle\Entity\AdvisorCode $advisorCode
     */
    public function removeAdvisorCode(\Wealthbot\RiaBundle\Entity\AdvisorCode $advisorCode)
    {
        $this->advisorCodes->removeElement($advisorCode);
    }

    /**
     * Get advisor_codes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdvisorCodes()
    {
        return $this->advisorCodes;
    }

    /**
     * Set tlh_buy_back_original
     *
     * @param boolean $tlhBuyBackOriginal
     * @return RiaCompanyInformation
     */
    public function setTlhBuyBackOriginal($tlhBuyBackOriginal)
    {
        $this->tlh_buy_back_original = $tlhBuyBackOriginal;

        return $this;
    }

    /**
     * Get tlh_buy_back_original
     *
     * @return boolean
     */
    public function getTlhBuyBackOriginal()
    {
        return $this->tlh_buy_back_original;
    }
}
