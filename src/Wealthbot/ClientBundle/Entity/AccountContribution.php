<?php

namespace Wealthbot\ClientBundle\Entity;

use Wealthbot\ClientBundle\Model\AccountContribution as BaseAccountContribution;
use Wealthbot\SignatureBundle\Entity\DocumentSignature;
use Wealthbot\SignatureBundle\Model\SignableInterface;

/**
 * AccountContribution.
 */
class AccountContribution extends BaseAccountContribution implements SignableInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $account_id;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var float
     */
    protected $amount;

    /**
     * @var \Wealthbot\ClientBundle\Entity\ClientAccount
     */
    private $account;

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
     * Set account_id.
     *
     * @param int $accountId
     *
     * @return AccountContribution
     */
    public function setAccountId($accountId)
    {
        $this->account_id = $accountId;

        return $this;
    }

    /**
     * Get account_id.
     *
     * @return int
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return AccountContribution
     */
    public function setType($type)
    {
        return parent::setType($type);
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
     * @return AccountContribution
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
     * Set transaction_frequency.
     *
     * @param int $transactionFrequency
     *
     * @return AccountContribution
     */
    public function setTransactionFrequency($transactionFrequency)
    {
        return parent::setTransactionFrequency($transactionFrequency);
    }

    /**
     * Get transaction_frequency.
     *
     * @return int
     */
    public function getTransactionFrequency()
    {
        return parent::getTransactionFrequency();
    }

    /**
     * Set account.
     *
     * @param \Wealthbot\ClientBundle\Entity\ClientAccount $account
     *
     * @return AccountContribution
     */
    public function setAccount(\Wealthbot\ClientBundle\Entity\ClientAccount $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account.
     *
     * @return \Wealthbot\ClientBundle\Entity\ClientAccount
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set contribution_year.
     *
     * @param string $contributionYear
     *
     * @return AccountContribution
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
     * Set bank_information_id.
     *
     * @param int $bankInformationId
     *
     * @return AccountContribution
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
     * Set bankInformation.
     *
     * @param \Wealthbot\ClientBundle\Entity\BankInformation $bankInformation
     *
     * @return AccountContribution
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
        return $this->account;
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
        return DocumentSignature::TYPE_AUTO_INVEST_CONTRIBUTION;
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
     * @return AccountContribution
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
     * @return AccountContribution
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
