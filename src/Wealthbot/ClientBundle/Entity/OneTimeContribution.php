<?php

namespace Wealthbot\ClientBundle\Entity;

use Wealthbot\ClientBundle\Model\OneTimeContribution as BaseOneTimeContribution;
use Wealthbot\SignatureBundle\Entity\DocumentSignature;
use Wealthbot\SignatureBundle\Model\SignableInterface;

/**
 * OneTimeContribution.
 */
class OneTimeContribution extends BaseOneTimeContribution implements SignableInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $system_account_id;

    /**
     * @var \Wealthbot\ClientBundle\Entity\SystemAccount
     */
    private $systemAccount;

    /**
     * @var int
     */
    protected $bank_information_id;

    /**
     * @var \DateTime
     */
    protected $start_transfer_date;

    /**
     * @var \Wealthbot\ClientBundle\Entity\BankInformation
     */
    protected $bankInformation;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @var string
     */
    protected $contribution_year;

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
     * Set system_account_id.
     *
     * @param int $systemAccountId
     *
     * @return OneTimeContribution
     */
    public function setSystemAccountId($systemAccountId)
    {
        $this->system_account_id = $systemAccountId;

        return $this;
    }

    /**
     * Get system_account_id.
     *
     * @return int
     */
    public function getSystemAccountId()
    {
        return $this->system_account_id;
    }

    /**
     * Set bank_information_id.
     *
     * @param int $bankInformationId
     *
     * @return OneTimeContribution
     */
    public function setBankInformationId($bankInformationId)
    {
        parent::setBankInformationId($bankInformationId);

        return $this;
    }

    /**
     * Get bank_information_id.
     *
     * @return int
     */
    public function getBankInformationId()
    {
        return parent::getBankInformationId();
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return OneTimeContribution
     */
    public function setType($type)
    {
        parent::setType($type);

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return parent::getType();
    }

    /**
     * Set start_transfer_date.
     *
     * @param \DateTime $startTransferDate
     *
     * @return OneTimeContribution
     */
    public function setStartTransferDate($startTransferDate)
    {
        parent::setStartTransferDate($startTransferDate);

        return $this;
    }

    /**
     * Get start_transfer_date.
     *
     * @return \DateTime
     */
    public function getStartTransferDate()
    {
        return parent::getStartTransferDate();
    }

    /**
     * Set amount.
     *
     * @param float $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        parent::setAmount($amount);

        return $this;
    }

    /**
     * Get amount.
     *
     * @return float
     */
    public function getAmount()
    {
        return parent::getAmount();
    }

    /**
     * Set contribution_year.
     *
     * @param string $contributionYear
     *
     * @return OneTimeContribution
     */
    public function setContributionYear($contributionYear)
    {
        parent::setContributionYear($contributionYear);

        return $this;
    }

    /**
     * Get contribution_year.
     *
     * @return string
     */
    public function getContributionYear()
    {
        return parent::getContributionYear();
    }

    /**
     * Set systemAccount.
     *
     * @param \Wealthbot\ClientBundle\Entity\SystemAccount $systemAccount
     *
     * @return OneTimeContribution
     */
    public function setSystemAccount(\Wealthbot\ClientBundle\Entity\SystemAccount $systemAccount = null)
    {
        $this->systemAccount = $systemAccount;

        return $this;
    }

    /**
     * Get systemAccount.
     *
     * @return \Wealthbot\ClientBundle\Entity\SystemAccount
     */
    public function getSystemAccount()
    {
        return $this->systemAccount;
    }

    /**
     * Set bankInformation.
     *
     * @param \Wealthbot\ClientBundle\Entity\BankInformation $bankInformation
     *
     * @return OneTimeContribution
     */
    public function setBankInformation(\Wealthbot\ClientBundle\Entity\BankInformation $bankInformation = null)
    {
        parent::setBankInformation($bankInformation);

        return $this;
    }

    /**
     * Get bankInformation.
     *
     * @return \Wealthbot\ClientBundle\Entity\BankInformation
     */
    public function getBankInformation()
    {
        return parent::getBankInformation();
    }

    /**
     * Get client account object.
     *
     * @return \Wealthbot\ClientBundle\Model\ClientAccount
     */
    public function getClientAccount()
    {
        return $this->systemAccount->getClientAccount();
    }

    /**
     * Get id of source object.
     *
     * @return mixed
     */
    public function getSourceObjectId()
    {
        return $this->id;
    }

    /**
     * Get type of document signature.
     *
     * @return string
     */
    public function getDocumentSignatureType()
    {
        return DocumentSignature::TYPE_ONE_TIME_CONTRIBUTION;
    }

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return OneTimeContribution
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return OneTimeContribution
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
