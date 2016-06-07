<?php

namespace Wealthbot\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Wealthbot\AdminBundle\Entity\BillingSpec;
use Wealthbot\AdminBundle\Entity\CeModel;
use Wealthbot\AdminBundle\Entity\Custodian;
use Wealthbot\ClientBundle\Entity\Bill;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\ClientBundle\Entity\ClientAdditionalContact;
use Wealthbot\ClientBundle\Entity\ClientPortfolio;
use Wealthbot\ClientBundle\Model\ActivityInterface;
use Wealthbot\UserBundle\Model\User as BaseUser;

/**
 * Wealthbot\UserBundle\Entity\User.
 */
class User extends BaseUser implements ActivityInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $additionalContacts;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $clientAccounts;

    /**
     * @var \Wealthbot\RiaBundle\Entity\RiaCompanyInformation
     */
    private $riaCompanyInformation;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $fees;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $updated;

    /**
     * @var \DateTime
     */
    protected $password_expired_at;

    /**
     * @var \Wealthbot\RiaBundle\Entity\RiaModelCompletion
     */
    private $riaModelCompletion;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $systemAccounts;

    /**
     * @var int
     */
    private $master_client_id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $slaveClients;

    /**
     * @var \Wealthbot\UserBundle\Entity\User
     */
    private $masterClient;

    /**
     * @var \Wealthbot\UserBundle\Entity\Profile
     */
    protected $profile;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $userDocuments;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $riaDashboardBoxes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $clientActivitySummaries;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    protected $groups;

    /**
     * @var BillingSpec[]|ArrayCollection
     */
    private $billingSpecs;

    /**
     * @var BillingSpec
     */
    private $appointedBillingSpec;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $ownGroups;

    /**
     * @var array
     */
    public $additionalSerializerFields;

    /**
     * @var Bill[]|ArrayCollection
     */
    private $bills;

    /**
     * @var CeModel[]|ArrayCollection
     */
    private $ceModels;

    /**
     * @var \DateTime
     */
    private $closed;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->clientAccounts = new ArrayCollection();
        $this->systemAccounts = new ArrayCollection();
        $this->additionalContacts = new ArrayCollection();
        $this->slaveClients = new ArrayCollection();
        $this->userDocuments = new ArrayCollection();
        $this->riaDashboardBoxes = new ArrayCollection();
        $this->clientActivitySummaries = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->billingSpecs = new ArrayCollection();
        $this->bills = new ArrayCollection();
        $this->ceModels = new ArrayCollection();

        $this->additionalSerializerFields = [];
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
     * Set profile.
     *
     * @param \Wealthbot\UserBundle\Entity\Profile $profile
     *
     * @return User
     */
    public function setProfile(\Wealthbot\UserBundle\Entity\Profile $profile = null)
    {
        $this->profile = $profile;

        if (!is_null($profile)) {
            $profile->setUser($this);
        }

        return $this;
    }

    /**
     * Get profile.
     *
     * @return \Wealthbot\UserBundle\Entity\Profile
     */
    public function getProfile()
    {
        return parent::getProfile();
    }

    /**
     * Add clientAccounts.
     *
     * @param \Wealthbot\ClientBundle\Entity\ClientAccount $clientAccounts
     *
     * @return User
     */
    public function addClientAccount(\Wealthbot\ClientBundle\Entity\ClientAccount $clientAccounts)
    {
        $this->clientAccounts[] = $clientAccounts;

        return $this;
    }

    /**
     * Remove clientAccounts.
     *
     * @param \Wealthbot\ClientBundle\Entity\ClientAccount $clientAccounts
     */
    public function removeClientAccount(\Wealthbot\ClientBundle\Entity\ClientAccount $clientAccounts)
    {
        $this->clientAccounts->removeElement($clientAccounts);
    }

    /**
     * Set riaCompanyInformation.
     *
     * @param \Wealthbot\RiaBundle\Entity\RiaCompanyInformation $riaCompanyInformation
     *
     * @return User
     */
    public function setRiaCompanyInformation(\Wealthbot\RiaBundle\Entity\RiaCompanyInformation $riaCompanyInformation = null)
    {
        $this->riaCompanyInformation = $riaCompanyInformation;

        return $this;
    }

    /**
     * Get riaCompanyInformation.
     *
     * @return null|\Wealthbot\RiaBundle\Entity\RiaCompanyInformation
     *
     * @throws \Exception
     */
    public function getRiaCompanyInformation()
    {
        if (!$this->hasRole('ROLE_RIA')) {
            $ria = $this->getRia();

            return $ria ? $ria->getRiaCompanyInformation() : null;
        }

        /*if (!$this->hasRole('ROLE_RIA')) {
            throw new \Exception("User must have role ROLE_CLIENT or ROLE_RIA");
        }*/

        return $this->riaCompanyInformation;
    }

    /**
     * Get custodian.
     *
     * @return \Wealthbot\AdminBundle\Entity\Custodian
     */
    public function getCustodian()
    {
        return $this->getRiaCompanyInformation()->getCustodian();
    }

    /**
     * Add fees.
     *
     * @param \Wealthbot\AdminBundle\Entity\Fee $fees
     *
     * @return User
     */
    public function addFee(\Wealthbot\AdminBundle\Entity\Fee $fees)
    {
        $this->fees[] = $fees;

        return $this;
    }

    /**
     * Remove fees.
     *
     * @param \Wealthbot\AdminBundle\Entity\Fee $fees
     */
    public function removeFee(\Wealthbot\AdminBundle\Entity\Fee $fees)
    {
        $this->fees->removeElement($fees);
    }

    /**
     * Get fees.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFees()
    {
        return $this->fees;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return User
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
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return User
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Get clientAccounts.
     *
     * @return ArrayCollection|ClientAccount[]
     */
    public function getClientAccounts()
    {
        return $this->clientAccounts;
    }

    /**
     * Set password_expired_at.
     *
     * @param \DateTime $passwordExpiredAt
     *
     * @return User
     */
    public function setPasswordExpiredAt($passwordExpiredAt)
    {
        $this->password_expired_at = $passwordExpiredAt;

        return $this;
    }

    /**
     * Get password_expired_at.
     *
     * @return \DateTime
     */
    public function getPasswordExpiredAt()
    {
        return $this->password_expired_at;
    }

    /**
     * Set riaModelCompletion.
     *
     * @param \Wealthbot\RiaBundle\Entity\RiaModelCompletion $riaModelCompletion
     *
     * @return User
     */
    public function setRiaModelCompletion(\Wealthbot\RiaBundle\Entity\RiaModelCompletion $riaModelCompletion = null)
    {
        $this->riaModelCompletion = $riaModelCompletion;

        return $this;
    }

    /**
     * Get riaModelCompletion.
     *
     * @return \Wealthbot\RiaBundle\Entity\RiaModelCompletion
     */
    public function getRiaModelCompletion()
    {
        return $this->riaModelCompletion;
    }

    /**
     * @var \Wealthbot\ClientBundle\Entity\PersonalInformation
     */
    private $clientPersonalInformation;

    /**
     * Set clientPersonalInformation.
     *
     * @param \Wealthbot\ClientBundle\Entity\PersonalInformation $clientPersonalInformation
     *
     * @return User
     */
    public function setClientPersonalInformation(\Wealthbot\ClientBundle\Entity\PersonalInformation $clientPersonalInformation = null)
    {
        $this->clientPersonalInformation = $clientPersonalInformation;

        return $this;
    }

    /**
     * Get clientPersonalInformation.
     *
     * @return \Wealthbot\ClientBundle\Entity\PersonalInformation
     */
    public function getClientPersonalInformation()
    {
        return $this->clientPersonalInformation;
    }

    /**
     * Add systemAccount.
     *
     * @param \Wealthbot\ClientBundle\Entity\SystemAccount $systemAccounts
     *
     * @return User
     */
    public function addSystemAccount(\Wealthbot\ClientBundle\Entity\SystemAccount $systemAccounts)
    {
        $this->systemAccounts[] = $systemAccounts;

        return $this;
    }

    /**
     * Remove systemAccount.
     *
     * @param \Wealthbot\ClientBundle\Entity\SystemAccount $systemAccounts
     */
    public function removeSystemAccount(\Wealthbot\ClientBundle\Entity\SystemAccount $systemAccounts)
    {
        $this->systemAccounts->removeElement($systemAccounts);
    }

    /**
     * Get systemAccounts.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSystemAccounts()
    {
        return $this->systemAccounts;
    }

    /**
     * Has systemAccounts.
     *
     * @return bool
     */
    public function hasSystemAccounts()
    {
        return $this->systemAccounts->count() > 0;
    }

    /**
     * Return ria if the user is client.
     *
     * @return null|User
     *
     * @throws \Exception
     */
    public function getRia()
    {
        /*if (!$this->hasRole('ROLE_CLIENT')) {
            throw new \Exception("User has not role: ROLE_CLIENT");
        }*/

        return $this->getProfile() ? $this->getProfile()->getRia() : null;
    }

    /**
     * Add additionalContacts.
     *
     * @param \Wealthbot\ClientBundle\Entity\ClientAdditionalContact $additionalContacts
     *
     * @return User
     */
    public function addAdditionalContact(\Wealthbot\ClientBundle\Entity\ClientAdditionalContact $additionalContacts)
    {
        $this->additionalContacts[] = $additionalContacts;

        return $this;
    }

    /**
     * Remove additionalContacts.
     *
     * @param \Wealthbot\ClientBundle\Entity\ClientAdditionalContact $additionalContacts
     */
    public function removeAdditionalContact(\Wealthbot\ClientBundle\Entity\ClientAdditionalContact $additionalContacts)
    {
        $this->additionalContacts->removeElement($additionalContacts);
    }

    /**
     * Get additionalContacts.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdditionalContacts()
    {
        return $this->additionalContacts;
    }

    /**
     * Get user first name.
     *
     * @return null|string
     */
    public function getFirstName()
    {
        return parent::getFirstName();
    }

    /**
     * Get user middle name.
     *
     * @return null|string
     */
    public function getMiddleName()
    {
        return $this->getProfile() ? $this->getProfile()->getMiddleName() : null;
    }

    /**
     * Get user last name.
     *
     * @return null|string
     */
    public function getLastName()
    {
        return parent::getLastName();
    }

    /**
     * Get user full name.
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    /**
     * Get user birth date.
     *
     * @return \DateTime|null
     */
    public function getBirthDate()
    {
        return $this->getProfile() ? $this->getProfile()->getBirthDate() : null;
    }

    /**
     * Get user state.
     *
     * @return null|\Wealthbot\AdminBundle\Entity\State
     */
    public function getState()
    {
        return $this->getProfile() ? $this->getProfile()->getState() : null;
    }

    /**
     * Get user city.
     *
     * @return null|string
     */
    public function getCity()
    {
        return $this->getProfile() ? $this->getProfile()->getCity() : null;
    }

    /**
     * Get user street.
     *
     * @return null|string
     */
    public function getStreet()
    {
        return $this->getProfile() ? $this->getProfile()->getStreet() : null;
    }

    /**
     * Get user zip.
     *
     * @return null|string
     */
    public function getZip()
    {
        return $this->getProfile() ? $this->getProfile()->getZip() : null;
    }

    /**
     * Get user phone number.
     *
     * @return null|string
     */
    public function getPhoneNumber()
    {
        return $this->getProfile() ? $this->getProfile()->getPhoneNumber() : null;
    }

    public function setSpouse(ClientAdditionalContact $spouse)
    {
        if ($spouse->getType() !== ClientAdditionalContact::TYPE_SPOUSE) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid client spouse type field value: %s',
                $spouse->getType()
            ));
        }

        $currSpouse = $this->getSpouse();

        if ($currSpouse) {
            $currSpouse->setFirstName($spouse->getFirstName());
            $currSpouse->setMiddleName($spouse->getMiddleName());
            $currSpouse->setLastName($spouse->getLastName());
            $currSpouse->setBirthDate($spouse->getBirthDate());
        } else {
            $this->addAdditionalContact($spouse);
        }

        return $this;
    }

    /**
     * Get spouse.
     *
     * @return ClientAdditionalContact
     */
    public function getSpouse()
    {
        foreach ($this->getAdditionalContacts() as $contact) {
            if ($contact->getType() === ClientAdditionalContact::TYPE_SPOUSE) {
                return $contact;
            }
        }

        return;
    }

    /**
     * Get spouse first name.
     *
     * @return null|string
     */
    public function getSpouseFirstName()
    {
        $spouse = $this->getSpouse();

        return $spouse ? $spouse->getFirstName() : null;
    }

    /**
     * Get spouse last name.
     *
     * @return null|string
     */
    public function getSpouseLastName()
    {
        $spouse = $this->getSpouse();

        return $spouse ? $spouse->getLastName() : null;
    }

    /**
     * Get spouse full name.
     *
     * @return string
     */
    public function getSpouseFullName()
    {
        return $this->getSpouseFirstName().' '.$this->getSpouseLastName();
    }

    /**
     * Returns true if client is married and false otherwise.
     *
     * @return bool
     */
    public function isMarried()
    {
        return $this->getProfile() ? $this->getProfile()->isMarried() : false;
    }

    /**
     * Returns true if the client has approved final portfolio and false otherwise.
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function hasApprovedPortfolio()
    {
        if (!$this->hasRole('ROLE_CLIENT')) {
            throw new \Exception('User has not role: ROLE_CLIENT');
        }

        foreach ($this->getClientPortfolios() as $clientPortfolio) {
            if ($clientPortfolio->getIsActive()) {
                return $clientPortfolio->isClientAccepted();
            }
        }

        return false;
    }

    /**
     * Get registration step.
     *
     * @return int|null
     */
    public function getRegistrationStep()
    {
        return $this->getProfile() ? $this->getProfile()->getRegistrationStep() : null;
    }

    /**
     * Set master_client_id.
     *
     * @param int $masterClientId
     *
     * @return User
     */
    public function setMasterClientId($masterClientId)
    {
        $this->master_client_id = $masterClientId;

        return $this;
    }

    /**
     * Get master_client_id.
     *
     * @return int
     */
    public function getMasterClientId()
    {
        return $this->master_client_id;
    }

    /**
     * Add slaveClients.
     *
     * @param \Wealthbot\UserBundle\Entity\User $slaveClients
     *
     * @return User
     */
    public function addSlaveClient(\Wealthbot\UserBundle\Entity\User $slaveClients)
    {
        $this->slaveClients[] = $slaveClients;

        return $this;
    }

    /**
     * Remove slaveClients.
     *
     * @param \Wealthbot\UserBundle\Entity\User $slaveClients
     */
    public function removeSlaveClient(\Wealthbot\UserBundle\Entity\User $slaveClients)
    {
        $this->slaveClients->removeElement($slaveClients);
    }

    /**
     * Get slaveClients.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSlaveClients()
    {
        return $this->slaveClients;
    }

    /**
     * Set masterClient.
     *
     * @param \Wealthbot\UserBundle\Entity\User $masterClient
     *
     * @return User
     */
    public function setMasterClient(\Wealthbot\UserBundle\Entity\User $masterClient = null)
    {
        $this->masterClient = $masterClient;

        return $this;
    }

    /**
     * Get masterClient.
     *
     * @return \Wealthbot\UserBundle\Entity\User
     */
    public function getMasterClient()
    {
        return $this->masterClient;
    }

    /**
     * Create new spouse object for user.
     *
     * @return ClientAdditionalContact
     */
    public function createSpouseObject()
    {
        $spouse = new ClientAdditionalContact();

        $spouse->setSpouseFirstName($this->getFirstName());
        $spouse->setSpouseMiddleName($this->getMiddleName());
        $spouse->setSpouseLastName($this->getLastName());
        $spouse->setSpouseBirthDate($this->getBirthDate());
        $spouse->setCity($this->getCity());
        $spouse->setStreet($this->getStreet());
        $spouse->setState($this->getState());
        $spouse->setZip($this->getZip());
        $spouse->setPhoneNumber($this->getPhoneNumber());
        $spouse->setMaritalStatus(Profile::CLIENT_MARITAL_STATUS_MARRIED);
        $spouse->setType(ClientAdditionalContact::TYPE_SPOUSE);

        return $spouse;
    }

    /**
     * Add userDocuments.
     *
     * @param \Wealthbot\UserBundle\Entity\Document $userDocuments
     *
     * @return User
     */
    public function addUserDocument(\Wealthbot\UserBundle\Entity\Document $userDocuments)
    {
        $this->userDocuments[] = $userDocuments;

        return $this;
    }

    /**
     * Remove userDocuments.
     *
     * @param \Wealthbot\UserBundle\Entity\Document $userDocuments
     */
    public function removeUserDocument(\Wealthbot\UserBundle\Entity\Document $userDocuments)
    {
        $this->userDocuments->removeElement($userDocuments);
    }

    /**
     * Get userDocuments.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserDocuments()
    {
        return $this->userDocuments;
    }
    /**
     * @var bool
     */
    private $is_password_reset;

    /**
     * Set is_password_reset.
     *
     * @param bool $isPasswordReset
     *
     * @return User
     */
    public function setIsPasswordReset($isPasswordReset)
    {
        $this->is_password_reset = $isPasswordReset;

        return $this;
    }

    /**
     * Get is_password_reset.
     *
     * @return bool
     */
    public function getIsPasswordReset()
    {
        return $this->is_password_reset;
    }
    /**
     * @var \Wealthbot\MailerBundle\Entity\AlertsConfiguration
     */
    private $alertsConfiguration;

    /**
     * Set alertsConfiguration.
     *
     * @param \Wealthbot\MailerBundle\Entity\AlertsConfiguration $alertsConfiguration
     *
     * @return User
     */
    public function setAlertsConfiguration(\Wealthbot\MailerBundle\Entity\AlertsConfiguration $alertsConfiguration = null)
    {
        $this->alertsConfiguration = $alertsConfiguration;

        return $this;
    }

    /**
     * Get alertsConfiguration.
     *
     * @return \Wealthbot\MailerBundle\Entity\AlertsConfiguration
     */
    public function getAlertsConfiguration()
    {
        return $this->alertsConfiguration;
    }

    /**
     * Add riaDashboardBoxes.
     *
     * @param \Wealthbot\RiaBundle\Entity\RiaDashboardBox $riaDashboardBoxes
     *
     * @return User
     */
    protected function addRiaDashboardBoxe(\Wealthbot\RiaBundle\Entity\RiaDashboardBox $riaDashboardBoxes)
    {
        return parent::addRiaDashboardBoxe($riaDashboardBoxes);
    }

    /**
     * Remove riaDashboardBoxes.
     *
     * @param \Wealthbot\RiaBundle\Entity\RiaDashboardBox $riaDashboardBoxes
     */
    protected function removeRiaDashboardBoxe(\Wealthbot\RiaBundle\Entity\RiaDashboardBox $riaDashboardBoxes)
    {
        parent::removeRiaDashboardBoxe($riaDashboardBoxes);
    }

    /**
     * Get riaDashboardBoxes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRiaDashboardBoxes()
    {
        return $this->riaDashboardBoxes;
    }
    /**
     * @var \Wealthbot\ClientBundle\Entity\ClientSettings
     */
    private $clientSettings;

    /**
     * Set clientSettings.
     *
     * @param \Wealthbot\ClientBundle\Entity\ClientSettings $clientSettings
     *
     * @return User
     */
    public function setClientSettings(\Wealthbot\ClientBundle\Entity\ClientSettings $clientSettings = null)
    {
        $this->clientSettings = $clientSettings;

        return $this;
    }

    /**
     * Get clientSettings.
     *
     * @return \Wealthbot\ClientBundle\Entity\ClientSettings
     */
    public function getClientSettings()
    {
        return $this->clientSettings;
    }

    /**
     * Add groups.
     *
     * @param \FOS\UserBundle\Model\GroupInterface $groups
     *
     * @return User
     */
    public function addGroup(\FOS\UserBundle\Model\GroupInterface $groups)
    {
        $this->groups[] = $groups;

        return $this;
    }

    /**
     * Add groups.
     *
     * @param \FOS\UserBundle\Model\GroupInterface|array $groups
     *
     * @return User
     */
    public function setGroups($groups)
    {
        if (is_array($groups)) {
            $this->groups = $groups;
        } elseif ($groups instanceof \FOS\UserBundle\Model\GroupInterface) {
            $this->groups = [$groups];
        }

        return $this;
    }

    /**
     * Remove groups.
     *
     * @param \FOS\UserBundle\Model\GroupInterface $groups
     */
    public function removeGroup(\FOS\UserBundle\Model\GroupInterface $groups)
    {
        $this->groups->removeElement($groups);
    }

    /**
     * Get groups.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    public function getGroupsAsString()
    {
        /*
         * @var Group
         */
        foreach ($this->groups as  $group) {
            $groupNames[] = $group->getName();
        }
        if (isset($groupNames) && $groupNames) {
            return implode(', ', $groupNames);
        }

        return 'No Groups';
    }

    /**
     * Add clientActivitySummaries.
     *
     * @param \Wealthbot\ClientBundle\Entity\ClientActivitySummary $clientActivitySummaries
     *
     * @return User
     */
    protected function addClientActivitySummarie(\Wealthbot\ClientBundle\Entity\ClientActivitySummary $clientActivitySummary)
    {
        return parent::addClientActivitySummarie($clientActivitySummary);
    }

    /**
     * Remove clientActivitySummaries.
     *
     * @param \Wealthbot\ClientBundle\Entity\ClientActivitySummary $clientActivitySummary
     */
    protected function removeClientActivitySummarie(\Wealthbot\ClientBundle\Entity\ClientActivitySummary $clientActivitySummary)
    {
        parent::removeClientActivitySummarie($clientActivitySummary);
    }

    /**
     * Get clientActivitySummaries.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClientActivitySummaries()
    {
        return $this->clientActivitySummaries;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $clientPortfolios;

    /**
     * Add clientPortfolio.
     *
     * @param \Wealthbot\ClientBundle\Entity\ClientPortfolio $clientPortfolio
     *
     * @return User
     */
    public function addClientPortfolio(\Wealthbot\ClientBundle\Entity\ClientPortfolio $clientPortfolio)
    {
        $this->clientPortfolios[] = $clientPortfolio;

        return $this;
    }

    /**
     * Remove clientPortfolios.
     *
     * @param \Wealthbot\ClientBundle\Entity\ClientPortfolio $clientPortfolio
     */
    public function removeClientPortfolio(\Wealthbot\ClientBundle\Entity\ClientPortfolio $clientPortfolio)
    {
        $this->clientPortfolios->removeElement($clientPortfolio);
    }

    /**
     * Get clientPortfolios.
     *
     * @return \Wealthbot\ClientBundle\Entity\ClientPortfolio[]
     */
    public function getClientPortfolios()
    {
        return $this->clientPortfolios;
    }

    /**
     * @param \Wealthbot\AdminBundle\Entity\BillingSpec[]|ArrayCollection $billingSpecs
     */
    public function setBillingSpecs($billingSpecs)
    {
        $this->billingSpecs = $billingSpecs;
    }

    /**
     * @return \Wealthbot\AdminBundle\Entity\BillingSpec[]|ArrayCollection
     */
    public function getBillingSpecs()
    {
        return $this->billingSpecs;
    }

    /**
     * Add billingSpecs.
     *
     * @param \Wealthbot\AdminBundle\Entity\BillingSpec $billingSpecs
     *
     * @return User
     */
    public function addBillingSpec(\Wealthbot\AdminBundle\Entity\BillingSpec $billingSpecs)
    {
        $this->billingSpecs[] = $billingSpecs;

        return $this;
    }

    /**
     * Remove billingSpecs.
     *
     * @param \Wealthbot\AdminBundle\Entity\BillingSpec $billingSpecs
     */
    public function removeBillingSpec(\Wealthbot\AdminBundle\Entity\BillingSpec $billingSpecs)
    {
        $this->billingSpecs->removeElement($billingSpecs);
    }

    /**
     * Add riaDashboardBoxes.
     *
     * @param \Wealthbot\RiaBundle\Entity\RiaDashboardBox $riaDashboardBoxes
     *
     * @return User
     */
    public function addRiaDashboardBox(\Wealthbot\RiaBundle\Entity\RiaDashboardBox $riaDashboardBoxes)
    {
        $this->riaDashboardBoxes[] = $riaDashboardBoxes;

        return $this;
    }

    /**
     * Remove riaDashboardBoxes.
     *
     * @param \Wealthbot\RiaBundle\Entity\RiaDashboardBox $riaDashboardBoxes
     */
    public function removeRiaDashboardBox(\Wealthbot\RiaBundle\Entity\RiaDashboardBox $riaDashboardBoxes)
    {
        $this->riaDashboardBoxes->removeElement($riaDashboardBoxes);
    }

    /**
     * Add clientActivitySummaries.
     *
     * @param \Wealthbot\ClientBundle\Entity\ClientActivitySummary $clientActivitySummaries
     *
     * @return User
     */
    public function addClientActivitySummary(\Wealthbot\ClientBundle\Entity\ClientActivitySummary $clientActivitySummaries)
    {
        $this->clientActivitySummaries[] = $clientActivitySummaries;

        return $this;
    }

    /**
     * Remove clientActivitySummaries.
     *
     * @param \Wealthbot\ClientBundle\Entity\ClientActivitySummary $clientActivitySummaries
     */
    public function removeClientActivitySummary(\Wealthbot\ClientBundle\Entity\ClientActivitySummary $clientActivitySummaries)
    {
        $this->clientActivitySummaries->removeElement($clientActivitySummaries);
    }

    /**
     * Add ownGroups.
     *
     * @param \Wealthbot\UserBundle\Entity\Group $ownGroups
     *
     * @return User
     */
    public function addOwnGroup(\Wealthbot\UserBundle\Entity\Group $ownGroups)
    {
        $this->ownGroups[] = $ownGroups;

        return $this;
    }

    /**
     * Remove ownGroups.
     *
     * @param \Wealthbot\UserBundle\Entity\Group $ownGroups
     */
    public function removeOwnGroup(\Wealthbot\UserBundle\Entity\Group $ownGroups)
    {
        $this->ownGroups->removeElement($ownGroups);
    }

    /**
     * Get ownGroups.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOwnGroups()
    {
        return $this->ownGroups;
    }

    /**
     * @param \Wealthbot\AdminBundle\Entity\BillingSpec $appointedBillingSpec
     */
    public function setAppointedBillingSpec($appointedBillingSpec)
    {
        $this->appointedBillingSpec = $appointedBillingSpec;
    }

    /**
     * @return \Wealthbot\AdminBundle\Entity\BillingSpec
     */
    public function getAppointedBillingSpec()
    {
        return $this->appointedBillingSpec;
    }

    /**
     * Get client type.
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getClientType()
    {
        if (!$this->hasRole('ROLE_CLIENT')) {
            throw new \Exception('User has not role: ROLE_CLIENT.');
        }

        return $this->clientSettings ? $this->clientSettings->getClientType() : '';
    }

    /**
     * Get activity message.
     *
     * @return string
     */
    public function getActivityMessage()
    {
        $message = null;
        if ($this->hasRole('ROLE_CLIENT') && $this->profile && $this->profile->hasStatusProspect()) {
            $message = 'Prospect Registered';
        }

        return $message;
    }

    /**
     * Get activity client.
     *
     * @return User
     */
    public function getActivityClient()
    {
        return $this;
    }

    public function getClientStatus()
    {
        return $this->profile->getClientStatus();
    }

    public function getFeeShedule()
    {
        return $this->getAppointedBillingSpec() ? $this->getAppointedBillingSpec()->getName() : '';
    }

    public function getName()
    {
        return $this->getLastName().', '.$this->getFirstName();
    }

    public function getPaymentMethod()
    {
        return $this->profile->getPaymentMethod();
    }

    public function addBill(Bill $bill)
    {
        $this->bills[] = $bill;

        return $this;
    }

    public function removeBill(Bill $bill)
    {
        $this->bills->removeElement($bill);

        return $this;
    }

    public function setBills($bills)
    {
        $this->bills = $bills;

        return $this;
    }

    public function getBills()
    {
        return $this->bills;
    }

    public function isRiaAdmin()
    {
        return $this->hasRole('ROLE_RIA') || $this->hasRole('ROLE_RIA_ADMIN');
    }

    /**
     * @param ArrayCollection|CeModel[] $ceModels
     */
    public function setCeModels($ceModels)
    {
        $this->ceModels = $ceModels;
    }

    /**
     * @return ArrayCollection|CeModel[]
     */
    public function getCeModels()
    {
        return $this->ceModels;
    }

    public function addCeModel(CeModel $ceModel)
    {
        $this->ceModels->add($ceModel);

        return $this;
    }

    public function removeCeModel(CeModel $ceModel)
    {
        $this->ceModels->removeElement($ceModel);

        return $this;
    }

    /**
     * Set closed.
     *
     * @param \DateTime $closed
     *
     * @return User
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;

        return $this;
    }

    /**
     * Get closed.
     *
     * @return \DateTime
     */
    public function getClosed()
    {
        return $this->closed;
    }
}
