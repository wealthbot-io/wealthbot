<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\BillingSpec;
use App\Entity\CeModel;
use App\Entity\Custodian;
use App\Entity\Bill;
use App\Entity\ClientAccount;
use App\Entity\ClientAdditionalContact;
use App\Entity\ClientPortfolio;
use App\Model\ActivityInterface;
use App\Model\User as BaseUser;
use Doctrine\Common\Collections\Collection;

/**
 * Class User
 * @package App\Entity
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
     * @param \App\Entity\RiaCompanyInformation
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
     * @param \App\Entity\RiaModelCompletion
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
     * @param \App\Entity\User
     */
    private $masterClient;

    /**
     * @param \App\Entity\Profile
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
     * @var ArrayCollection
     */
    private $answers;


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
        $this->fees = new ArrayCollection();

        $this->additionalSerializerFields = [];
        $this->clientPortfolios = new ArrayCollection();
        $this->ownGroups = new ArrayCollection();
        $this->answers = new ArrayCollection();
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
     * @param \App\Entity\Profile $profile
     *
     * @return User
     */
    public function setProfile(Profile $profile = null)
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
     * @return \App\Entity\Profile
     */
    public function getProfile()
    {
        return parent::getProfile();
    }

    /**
     * Add clientAccounts.
     *
     * @param \App\Entity\ClientAccount $clientAccounts
     *
     * @return User
     */
    public function addClientAccount(ClientAccount $clientAccounts)
    {
        $this->clientAccounts[] = $clientAccounts;

        return $this;
    }

    /**
     * Remove clientAccounts.
     *
     * @param \App\Entity\ClientAccount $clientAccounts
     */
    public function removeClientAccount(ClientAccount $clientAccounts)
    {
        $this->clientAccounts->removeElement($clientAccounts);
    }

    /**
     * Set riaCompanyInformation.
     *
     * @param \App\Entity\RiaCompanyInformation $riaCompanyInformation
     *
     * @return User
     */
    public function setRiaCompanyInformation(RiaCompanyInformation $riaCompanyInformation = null)
    {
        $this->riaCompanyInformation = $riaCompanyInformation;

        return $this;
    }

    /**
     * Get riaCompanyInformation.
     *
     * @return \App\Entity\RiaCompanyInformation|null
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
     * @return \App\Entity\Custodian
     */
    public function getCustodian()
    {
        return $this->getRiaCompanyInformation()->getCustodian();
    }

    /**
     * Add fees.
     *
     * @param \App\Entity\Fee $fees
     *
     * @return User
     */
    public function addFee(Fee $fees)
    {
        $this->fees[] = $fees;

        return $this;
    }

    /**
     * Remove fees.
     *
     * @param \App\Entity\Fee $fees
     */
    public function removeFee(Fee $fees)
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
     * @param \App\Entity\RiaModelCompletion $riaModelCompletion
     *
     * @return User
     */
    public function setRiaModelCompletion(RiaModelCompletion $riaModelCompletion = null)
    {
        $this->riaModelCompletion = $riaModelCompletion;

        return $this;
    }

    /**
     * Get riaModelCompletion.
     *
     * @return \App\Entity\RiaModelCompletion
     */
    public function getRiaModelCompletion()
    {
        return $this->riaModelCompletion;
    }

    /**
     * @param \App\Entity\PersonalInformation
     */
    private $clientPersonalInformation;

    /**
     * Set clientPersonalInformation.
     *
     * @param \App\Entity\PersonalInformation $clientPersonalInformation
     *
     * @return User
     */
    public function setClientPersonalInformation(PersonalInformation $clientPersonalInformation = null)
    {
        $this->clientPersonalInformation = $clientPersonalInformation;

        return $this;
    }

    /**
     * Get clientPersonalInformation.
     *
     * @return \App\Entity\PersonalInformation
     */
    public function getClientPersonalInformation()
    {
        return $this->clientPersonalInformation;
    }

    /**
     * Add systemAccount.
     *
     * @param \App\Entity\SystemAccount $systemAccounts
     *
     * @return User
     */
    public function addSystemAccount(SystemAccount $systemAccounts)
    {
        $this->systemAccounts[] = $systemAccounts;

        return $this;
    }

    /**
     * Remove systemAccount.
     *
     * @param \App\Entity\SystemAccount $systemAccounts
     */
    public function removeSystemAccount(SystemAccount $systemAccounts)
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
     * @return User|null
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
     * @param \App\Entity\ClientAdditionalContact $additionalContacts
     *
     * @return User
     */
    public function addAdditionalContact(ClientAdditionalContact $additionalContacts)
    {
        $this->additionalContacts[] = $additionalContacts;

        return $this;
    }

    /**
     * Remove additionalContacts.
     *
     * @param \App\Entity\ClientAdditionalContact $additionalContacts
     */
    public function removeAdditionalContact(ClientAdditionalContact $additionalContacts)
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
     * @return string|null
     */
    public function getFirstName()
    {
        return parent::getFirstName();
    }

    /**
     * Get user middle name.
     *
     * @return string|null
     */
    public function getMiddleName()
    {
        return $this->getProfile() ? $this->getProfile()->getMiddleName() : null;
    }

    /**
     * Get user last name.
     *
     * @return string|null
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
     * @return \App\Entity\State|null
     */
    public function getState()
    {
        return $this->getProfile() ? $this->getProfile()->getState() : null;
    }

    /**
     * Get user city.
     *
     * @return string|null
     */
    public function getCity()
    {
        return $this->getProfile() ? $this->getProfile()->getCity() : null;
    }

    /**
     * Get user street.
     *
     * @return string|null
     */
    public function getStreet()
    {
        return $this->getProfile() ? $this->getProfile()->getStreet() : null;
    }

    /**
     * Get user zip.
     *
     * @return string|null
     */
    public function getZip()
    {
        return $this->getProfile() ? $this->getProfile()->getZip() : null;
    }

    /**
     * Get user phone number.
     *
     * @return string|null
     */
    public function getPhoneNumber()
    {
        return $this->getProfile() ? $this->getProfile()->getPhoneNumber() : null;
    }

    public function setSpouse(ClientAdditionalContact $spouse)
    {
        if (ClientAdditionalContact::TYPE_SPOUSE !== $spouse->getType()) {
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
            if (ClientAdditionalContact::TYPE_SPOUSE === $contact->getType()) {
                return $contact;
            }
        }

        return;
    }

    /**
     * Get spouse first name.
     *
     * @return string|null
     */
    public function getSpouseFirstName()
    {
        $spouse = $this->getSpouse();

        return $spouse ? $spouse->getFirstName() : null;
    }

    /**
     * Get spouse last name.
     *
     * @return string|null
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
     * @param \App\Entity\User $slaveClients
     *
     * @return User
     */
    public function addSlaveClient(User $slaveClients)
    {
        $this->slaveClients[] = $slaveClients;

        return $this;
    }

    /**
     * Remove slaveClients.
     *
     * @param \App\Entity\User $slaveClients
     */
    public function removeSlaveClient(User $slaveClients)
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
     * @param \App\Entity\User $masterClient
     *
     * @return User
     */
    public function setMasterClient(User $masterClient = null)
    {
        $this->masterClient = $masterClient;

        return $this;
    }

    /**
     * Get masterClient.
     *
     * @return \App\Entity\User
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
     * @param \App\Entity\Document $userDocuments
     *
     * @return User
     */
    public function addUserDocument(Document $userDocuments)
    {
        $this->userDocuments[] = $userDocuments;

        return $this;
    }

    /**
     * Remove userDocuments.
     *
     * @param \App\Entity\Document $userDocuments
     */
    public function removeUserDocument(Document $userDocuments)
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
     * @param \App\Entity\AlertsConfiguration
     */
    private $alertsConfiguration;

    /**
     * Set alertsConfiguration.
     *
     * @param \App\Entity\AlertsConfiguration $alertsConfiguration
     *
     * @return User
     */
    public function setAlertsConfiguration(AlertsConfiguration $alertsConfiguration = null)
    {
        $this->alertsConfiguration = $alertsConfiguration;

        return $this;
    }

    /**
     * Get alertsConfiguration.
     *
     * @return \App\Entity\AlertsConfiguration
     */
    public function getAlertsConfiguration()
    {
        return $this->alertsConfiguration;
    }

    /**
     * Add riaDashboardBoxes.
     *
     * @param \App\Entity\RiaDashboardBox $riaDashboardBoxes
     *
     * @return User
     */
    protected function addRiaDashboardBoxe(RiaDashboardBox $riaDashboardBoxes)
    {
        return parent::addRiaDashboardBoxe($riaDashboardBoxes);
    }

    /**
     * Remove riaDashboardBoxes.
     *
     * @param \App\Entity\RiaDashboardBox $riaDashboardBoxes
     */
    protected function removeRiaDashboardBoxe(RiaDashboardBox $riaDashboardBoxes)
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
     * @param \App\Entity\ClientSettings
     */
    private $clientSettings;

    /**
     * Set clientSettings.
     *
     * @param \App\Entity\ClientSettings $clientSettings
     *
     * @return User
     */
    public function setClientSettings(ClientSettings $clientSettings = null)
    {
        $this->clientSettings = $clientSettings;

        return $this;
    }

    /**
     * Get clientSettings.
     *
     * @return \App\Entity\ClientSettings
     */
    public function getClientSettings()
    {
        return $this->clientSettings;
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
        } else {
            $this->groups = [$groups];
        }

        return $this;
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
     * @param \App\Entity\ClientActivitySummary $clientActivitySummaries
     *
     * @return User
     */
    protected function addClientActivitySummarie(ClientActivitySummary $clientActivitySummary)
    {
        return parent::addClientActivitySummarie($clientActivitySummary);
    }

    /**
     * Remove clientActivitySummaries.
     *
     * @param \App\Entity\ClientActivitySummary $clientActivitySummary
     */
    protected function removeClientActivitySummarie(ClientActivitySummary $clientActivitySummary)
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
     * @param \App\Entity\ClientPortfolio $clientPortfolio
     *
     * @return User
     */
    public function addClientPortfolio(ClientPortfolio $clientPortfolio)
    {
        $this->clientPortfolios[] = $clientPortfolio;

        return $this;
    }

    /**
     * Remove clientPortfolios.
     *
     * @param \App\Entity\ClientPortfolio $clientPortfolio
     */
    public function removeClientPortfolio(ClientPortfolio $clientPortfolio)
    {
        $this->clientPortfolios->removeElement($clientPortfolio);
    }

    /**
     * Get clientPortfolios.
     *
     * @return \App\Entity\ClientPortfolio[]
     */
    public function getClientPortfolios()
    {
        return $this->clientPortfolios;
    }

    /**
     * @param \App\Entity\BillingSpec[]|ArrayCollection $billingSpecs
     */
    public function setBillingSpecs($billingSpecs)
    {
        $this->billingSpecs = $billingSpecs;
    }

    /**
     * @return \App\Entity\BillingSpec[]|ArrayCollection
     */
    public function getBillingSpecs()
    {
        return $this->billingSpecs;
    }

    /**
     * Add billingSpecs.
     *
     * @param \App\Entity\BillingSpec $billingSpecs
     *
     * @return User
     */
    public function addBillingSpec(BillingSpec $billingSpecs)
    {
        $this->billingSpecs[] = $billingSpecs;

        return $this;
    }

    /**
     * Remove billingSpecs.
     *
     * @param \App\Entity\BillingSpec $billingSpecs
     */
    public function removeBillingSpec(BillingSpec $billingSpecs)
    {
        $this->billingSpecs->removeElement($billingSpecs);
    }

    /**
     * Add riaDashboardBoxes.
     *
     * @param \App\Entity\RiaDashboardBox $riaDashboardBoxes
     *
     * @return User
     */
    public function addRiaDashboardBox(RiaDashboardBox $riaDashboardBoxes)
    {
        $this->riaDashboardBoxes[] = $riaDashboardBoxes;

        return $this;
    }

    /**
     * Remove riaDashboardBoxes.
     *
     * @param \App\Entity\RiaDashboardBox $riaDashboardBoxes
     */
    public function removeRiaDashboardBox(RiaDashboardBox $riaDashboardBoxes)
    {
        $this->riaDashboardBoxes->removeElement($riaDashboardBoxes);
    }

    /**
     * Add clientActivitySummaries.
     *
     * @param \App\Entity\ClientActivitySummary $clientActivitySummaries
     *
     * @return User
     */
    public function addClientActivitySummary(ClientActivitySummary $clientActivitySummaries)
    {
        $this->clientActivitySummaries[] = $clientActivitySummaries;

        return $this;
    }

    /**
     * Remove clientActivitySummaries.
     *
     * @param \App\Entity\ClientActivitySummary $clientActivitySummaries
     */
    public function removeClientActivitySummary(ClientActivitySummary $clientActivitySummaries)
    {
        $this->clientActivitySummaries->removeElement($clientActivitySummaries);
    }

    /**
     * Add ownGroups.
     *
     * @param \App\Entity\Group $ownGroups
     *
     * @return User
     */
    public function addOwnGroup(Group $ownGroups)
    {
        $this->ownGroups[] = $ownGroups;

        return $this;
    }

    /**
     * Remove ownGroups.
     *
     * @param \App\Entity\Group $ownGroups
     */
    public function removeOwnGroup(Group $ownGroups)
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
     * @param \App\Entity\BillingSpec $appointedBillingSpec
     */
    public function setAppointedBillingSpec($appointedBillingSpec)
    {
        $this->appointedBillingSpec = $appointedBillingSpec;
    }

    /**
     * @return \App\Entity\BillingSpec
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


    public function getAnswers()
    {
        return $this->answers;
    }
}
