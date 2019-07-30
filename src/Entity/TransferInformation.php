<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use App\Model\TransferInformation as BaseTransferInformation;
use App\Entity\DocumentSignature;
use App\Model\SignableInterface;
use Doctrine\Common\Collections\Collection;

/**
 * Class TransferInformation
 * @package App\Entity
 */
class TransferInformation extends BaseTransferInformation implements SignableInterface
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
     * @var int
     */
    private $client_account_id;

    /**
     * @var string
     */
    private $account_number;

    /**
     * @var string
     */
    private $financial_institution;

    /**
     * @var string
     */
    private $firm_address;

    /**
     * @var string
     */
    private $phone_number;

    /**
     * @var string
     */
    protected $account_type;

    /**
     * @var bool
     */
    private $transfer_shares_cash;

    /**
     * @var int
     */
    protected $insurance_policy_type;

    /**
     * @var float
     */
    private $penalty_amount;

    /**
     * @var bool
     */
    private $is_penalty_free;

    /**
     * @var bool
     */
    private $redeem_certificates_deposit;

    /**
     * @var string
     */
    private $delivering_account_title;

    /**
     * @var string
     */
    private $ameritrade_account_title;

    /**
     * @param \App\Entity\Document
     */
    private $statementDocument;

    /**
     * @param \App\Entity\ClientAccount
     */
    private $clientAccount;

    /**
     * @param \App\Entity\SystemAccount
     */
    private $systemAccount;

    /**
     * @var int
     */
    protected $transfer_from;

    /**
     * @var bool
     */
    private $is_include_policy;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $questionnaireAnswers;

    /**
     * @var int
     */
    private $transfer_custodian_id;

    /**
     * @var string
     */
    private $title_first;

    /**
     * @var string
     */
    private $title_middle;

    /**
     * @var string
     */
    private $title_last;

    /**
     * @var string
     */
    private $joint_title_first;

    /**
     * @var string
     */
    private $joint_title_middle;

    /**
     * @var string
     */
    private $joint_title_last;

    /**
     * @param \App\Entity\TransferCustodian
     */
    private $transferCustodian;

    public function __construct()
    {
        $this->redeem_certificates_deposit = false;
        $this->questionnaireAnswers = new ArrayCollection();
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
     * Set system_account_id.
     *
     * @param int $systemAccountId
     *
     * @return TransferInformation
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
     * Set client_account_id.
     *
     * @param int $clientAccountId
     *
     * @return TransferInformation
     */
    public function setClientAccountId($clientAccountId)
    {
        $this->client_account_id = $clientAccountId;

        return $this;
    }

    /**
     * Get client_account_id.
     *
     * @return int
     */
    public function getClientAccountId()
    {
        return $this->client_account_id;
    }

    /**
     * Set account_number.
     *
     * @param string $accountNumber
     *
     * @return TransferInformation
     */
    public function setAccountNumber($accountNumber)
    {
        $this->account_number = $accountNumber;

        return $this;
    }

    /**
     * Get account_number.
     *
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->account_number;
    }

    /**
     * Set financial_institution.
     *
     * @param string $financialInstitution
     *
     * @return TransferInformation
     */
    public function setFinancialInstitution($financialInstitution)
    {
        $this->financial_institution = $financialInstitution;

        return $this;
    }

    /**
     * Get financial_institution.
     *
     * @return string
     */
    public function getFinancialInstitution()
    {
        return $this->financial_institution;
    }

    /**
     * Set firm_address.
     *
     * @param string $firmAddress
     *
     * @return TransferInformation
     */
    public function setFirmAddress($firmAddress)
    {
        $this->firm_address = $firmAddress;

        return $this;
    }

    /**
     * Get firm_address.
     *
     * @return string
     */
    public function getFirmAddress()
    {
        return $this->firm_address;
    }

    /**
     * Set phone_number.
     *
     * @param string $phoneNumber
     *
     * @return TransferInformation
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
     * Set account_type.
     *
     * @param string $accountType
     *
     * @return TransferInformation
     */
    public function setAccountType($accountType)
    {
        return parent::setAccountType($accountType);
    }

    /**
     * Get account_type.
     *
     * @return string
     */
    public function getAccountType()
    {
        return parent::getAccountType();
    }

    /**
     * Set transfer_shares_cash.
     *
     * @param bool $transferSharesCash
     *
     * @return TransferInformation
     */
    public function setTransferSharesCash($transferSharesCash)
    {
        $this->transfer_shares_cash = $transferSharesCash;

        return $this;
    }

    /**
     * Get transfer_shares_cash.
     *
     * @return bool
     */
    public function getTransferSharesCash()
    {
        return $this->transfer_shares_cash;
    }

    /**
     * Set transfer_from.
     *
     * @param int $transferFrom
     *
     * @return TransferInformation
     */
    public function setTransferFrom($transferFrom)
    {
        return parent::setTransferFrom($transferFrom);
    }

    /**
     * Get transfer_from.
     *
     * @return int
     */
    public function getTransferFrom()
    {
        return parent::getTransferFrom();
    }

    public function setInsurancePolicyType($insurancePolicyType)
    {
        return parent::setInsurancePolicyType($insurancePolicyType);
    }

    public function getInsurancePolicyType()
    {
        return parent::getInsurancePolicyType();
    }

    /**
     * Set penalty_amount.
     *
     * @param float $penaltyAmount
     *
     * @return TransferInformation
     */
    public function setPenaltyAmount($penaltyAmount)
    {
        $this->penalty_amount = $penaltyAmount;

        return $this;
    }

    /**
     * Get penalty_amount.
     *
     * @return float
     */
    public function getPenaltyAmount()
    {
        return $this->penalty_amount;
    }

    /**
     * Set is_penalty_free.
     *
     * @param bool $isPenaltyFree
     *
     * @return TransferInformation
     */
    public function setIsPenaltyFree($isPenaltyFree)
    {
        $this->is_penalty_free = $isPenaltyFree;

        return $this;
    }

    /**
     * Get is_penalty_free.
     *
     * @return bool
     */
    public function getIsPenaltyFree()
    {
        return $this->is_penalty_free;
    }

    /**
     * Set redeem_certificates_deposit.
     *
     * @param bool $redeemCertificatesDeposit
     *
     * @return TransferInformation
     */
    public function setRedeemCertificatesDeposit($redeemCertificatesDeposit)
    {
        $this->redeem_certificates_deposit = $redeemCertificatesDeposit;

        return $this;
    }

    /**
     * Get redeem_certificates_deposit.
     *
     * @return bool
     */
    public function getRedeemCertificatesDeposit()
    {
        return $this->redeem_certificates_deposit;
    }

    /**
     * Set delivering_account_title.
     *
     * @param string $deliveringAccountTitle
     *
     * @return TransferInformation
     */
    public function setDeliveringAccountTitle($deliveringAccountTitle)
    {
        $this->delivering_account_title = $deliveringAccountTitle;

        return $this;
    }

    /**
     * Get delivering_account_title.
     *
     * @return string
     */
    public function getDeliveringAccountTitle()
    {
        return $this->delivering_account_title;
    }

    /**
     * Set ameritrade_account_title.
     *
     * @param string $ameritradeAccountTitle
     *
     * @return TransferInformation
     */
    public function setAmeritradeAccountTitle($ameritradeAccountTitle)
    {
        $this->ameritrade_account_title = $ameritradeAccountTitle;

        return $this;
    }

    /**
     * Get ameritrade_account_title.
     *
     * @return string
     */
    public function getAmeritradeAccountTitle()
    {
        return $this->ameritrade_account_title;
    }

    /**
     * Set clientAccount.
     *
     * @param \App\Entity\ClientAccount $clientAccount
     *
     * @return TransferInformation
     */
    public function setClientAccount(ClientAccount $clientAccount = null)
    {
        $this->clientAccount = $clientAccount;

        return $this;
    }

    /**
     * Get clientAccount.
     *
     * @return \App\Entity\ClientAccount
     */
    public function getClientAccount()
    {
        return $this->clientAccount;
    }

    /**
     * Set systemAccount.
     *
     * @param \App\Entity\SystemAccount $systemAccount
     *
     * @return TransferInformation
     */
    public function setSystemAccount(SystemAccount $systemAccount = null)
    {
        $this->systemAccount = $systemAccount;

        return $this;
    }

    /**
     * Get systemAccount.
     *
     * @return \App\Entity\SystemAccount
     */
    public function getSystemAccount()
    {
        return $this->systemAccount;
    }

    /**
     * Set transfer_custodian_id.
     *
     * @param int $transferCustodianId
     *
     * @return TransferInformation
     */
    public function setTransferCustodianId($transferCustodianId)
    {
        $this->transfer_custodian_id = $transferCustodianId;

        return $this;
    }

    /**
     * Get transfer_custodian_id.
     *
     * @return int
     */
    public function getTransferCustodianId()
    {
        return $this->transfer_custodian_id;
    }

    /**
     * Set title_first.
     *
     * @param string $titleFirst
     *
     * @return TransferInformation
     */
    public function setTitleFirst($titleFirst)
    {
        $this->title_first = $titleFirst;

        return $this;
    }

    /**
     * Get title_first.
     *
     * @return string
     */
    public function getTitleFirst()
    {
        return $this->title_first;
    }

    /**
     * Set title_middle.
     *
     * @param string $titleMiddle
     *
     * @return TransferInformation
     */
    public function setTitleMiddle($titleMiddle)
    {
        $this->title_middle = $titleMiddle;

        return $this;
    }

    /**
     * Get title_middle.
     *
     * @return string
     */
    public function getTitleMiddle()
    {
        return $this->title_middle;
    }

    /**
     * Set title_last.
     *
     * @param string $titleLast
     *
     * @return TransferInformation
     */
    public function setTitleLast($titleLast)
    {
        $this->title_last = $titleLast;

        return $this;
    }

    /**
     * Get title_last.
     *
     * @return string
     */
    public function getTitleLast()
    {
        return $this->title_last;
    }

    /**
     * Set joint_title_first.
     *
     * @param string $jointTitleFirst
     *
     * @return TransferInformation
     */
    public function setJointTitleFirst($jointTitleFirst)
    {
        $this->joint_title_first = $jointTitleFirst;

        return $this;
    }

    /**
     * Get joint_title_first.
     *
     * @return string
     */
    public function getJointTitleFirst()
    {
        return $this->joint_title_first;
    }

    /**
     * Set joint_title_middle.
     *
     * @param string $jointTitleMiddle
     *
     * @return TransferInformation
     */
    public function setJointTitleMiddle($jointTitleMiddle)
    {
        $this->joint_title_middle = $jointTitleMiddle;

        return $this;
    }

    /**
     * Get joint_title_middle.
     *
     * @return string
     */
    public function getJointTitleMiddle()
    {
        return $this->joint_title_middle;
    }

    /**
     * Set joint_title_last.
     *
     * @param string $jointTitleLast
     *
     * @return TransferInformation
     */
    public function setJointTitleLast($jointTitleLast)
    {
        $this->joint_title_last = $jointTitleLast;

        return $this;
    }

    /**
     * Get joint_title_last.
     *
     * @return string
     */
    public function getJointTitleLast()
    {
        return $this->joint_title_last;
    }

    /**
     * Set transferCustodian.
     *
     * @param \App\Entity\TransferCustodian $transferCustodian
     *
     * @return TransferInformation
     */
    public function setTransferCustodian(TransferCustodian $transferCustodian = null)
    {
        $this->transferCustodian = $transferCustodian;

        return $this;
    }

    /**
     * Get transferCustodian.
     *
     * @return \App\Entity\TransferCustodian
     */
    public function getTransferCustodian()
    {
        return $this->transferCustodian;
    }

    /**
     * Get account title.
     *
     * @return string
     */
    public function getAccountTitle()
    {
        return $this->getTitleFirst().' '.$this->getTitleMiddle().' '.$this->getTitleLast();
    }

    /**
     * Get account joint title.
     *
     * @return string
     */
    public function getAccountJointTitle()
    {
        return $this->getJointTitleFirst().' '.$this->getJointTitleMiddle().' '.$this->getJointTitleLast();
    }

    /**
     * Set is_include_policy.
     *
     * @param bool $isIncludePolicy
     *
     * @return TransferInformation
     */
    public function setIsIncludePolicy($isIncludePolicy)
    {
        $this->is_include_policy = $isIncludePolicy;

        return $this;
    }

    /**
     * Get is_include_policy.
     *
     * @return bool
     */
    public function getIsIncludePolicy()
    {
        return $this->is_include_policy;
    }

    /**
     * Set questionnaireAnswers.
     *
     * @param array $questionnaireAnswers
     *
     * @return $this
     */
    public function setQuestionnaireAnswer(array $questionnaireAnswers)
    {
        $this->questionnaireAnswers = new ArrayCollection($questionnaireAnswers);

        return $this;
    }

    /**
     * Add questionnaireAnswers.
     *
     * @param \App\Entity\TransferCustodianQuestionAnswer $questionnaireAnswers
     *
     * @return TransferInformation
     */
    public function addQuestionnaireAnswer(TransferCustodianQuestionAnswer $questionnaireAnswers)
    {
        $this->questionnaireAnswers[] = $questionnaireAnswers;

        return $this;
    }

    /**
     * Remove questionnaireAnswers.
     *
     * @param \App\Entity\TransferCustodianQuestionAnswer $questionnaireAnswers
     */
    public function removeQuestionnaireAnswer(TransferCustodianQuestionAnswer $questionnaireAnswers)
    {
        $this->questionnaireAnswers->removeElement($questionnaireAnswers);
    }

    /**
     * Get questionnaireAnswers.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuestionnaireAnswers()
    {
        return $this->questionnaireAnswers;
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
        return DocumentSignature::TYPE_TRANSFER_INFORMATION;
    }

    /**
     * Set statementDocument.
     *
     * @param \App\Entity\Document $statementDocument
     *
     * @return TransferInformation
     */
    public function setStatementDocument(Document $statementDocument = null)
    {
        $this->statementDocument = $statementDocument;

        return $this;
    }

    /**
     * Get statementDocument.
     *
     * @return \App\Entity\Document
     */
    public function getStatementDocument()
    {
        return $this->statementDocument;
    }
}
