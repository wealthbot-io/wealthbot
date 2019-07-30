<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Model\WorkflowableInterface;

/**
 * Class BankInformation
 * @package App\Entity
 */
class BankInformation implements WorkflowableInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $account_owner_first_name;

    /**
     * @var string
     */
    private $account_owner_middle_name;

    /**
     * @var string
     */
    private $account_owner_last_name;

    /**
     * @var string
     */
    private $joint_account_owner_first_name;

    /**
     * @var string
     */
    private $joint_account_owner_middle_name;

    /**
     * @var string
     */
    private $joint_account_owner_last_name;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $account_title;

    /**
     * @var string
     */
    private $phone_number;

    /**
     * @var string
     */
    private $routing_number;

    /**
     * @var string
     */
    private $account_number;

    /**
     * @var string
     */
    private $account_type;

    // ENUM values account_type column
    const ACCOUNT_TYPE_CHECK = 'checking';
    const ACCOUNT_TYPE_SAVING = 'saving';

    private static $_accountTypeValues = null;

    /**
     * @var string
     */
    private $pdf_copy;

    /**
     * @var UploadedFile
     */
    private $pdf_copy_file;

    /**
     * @var \DateTime
     */
    private $updated;

    /**
     * @var int
     */
    private $client_id;

    /**
     * @param \App\Entity\User
     */
    private $client;

    /**
     * @param \App\Entity\Document
     */
    private $pdfDocument;

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
     * Set account_owner_first_name.
     *
     * @param string $accountOwnerFirstName
     *
     * @return BankInformation
     */
    public function setAccountOwnerFirstName($accountOwnerFirstName)
    {
        $this->account_owner_first_name = $accountOwnerFirstName;

        return $this;
    }

    /**
     * Get account_owner_first_name.
     *
     * @return string
     */
    public function getAccountOwnerFirstName()
    {
        return $this->account_owner_first_name;
    }

    /**
     * Set account_owner_middle_name.
     *
     * @param string $accountOwnerMiddleName
     *
     * @return BankInformation
     */
    public function setAccountOwnerMiddleName($accountOwnerMiddleName)
    {
        $this->account_owner_middle_name = $accountOwnerMiddleName;

        return $this;
    }

    /**
     * Get account_owner_middle_name.
     *
     * @return string
     */
    public function getAccountOwnerMiddleName()
    {
        return $this->account_owner_middle_name;
    }

    /**
     * Set account_owner_last_name.
     *
     * @param string $accountOwnerLastName
     *
     * @return BankInformation
     */
    public function setAccountOwnerLastName($accountOwnerLastName)
    {
        $this->account_owner_last_name = $accountOwnerLastName;

        return $this;
    }

    /**
     * Get account_owner_last_name.
     *
     * @return string
     */
    public function getAccountOwnerLastName()
    {
        return $this->account_owner_last_name;
    }

    /**
     * Get account owner full name.
     *
     * @return string
     */
    public function getAccountOwnerFullName()
    {
        return $this->getAccountOwnerFirstName().' '.$this->getAccountOwnerMiddleName().' '
            .$this->getAccountOwnerLastName();
    }

    /**
     * Set joint_account_owner_first_name.
     *
     * @param string $jointAccountOwnerFirstName
     *
     * @return BankInformation
     */
    public function setJointAccountOwnerFirstName($jointAccountOwnerFirstName)
    {
        $this->joint_account_owner_first_name = $jointAccountOwnerFirstName;

        return $this;
    }

    /**
     * Get joint_account_owner_first_name.
     *
     * @return string
     */
    public function getJointAccountOwnerFirstName()
    {
        return $this->joint_account_owner_first_name;
    }

    /**
     * Set joint_account_owner_middle_name.
     *
     * @param string $jointAccountOwnerMiddleName
     *
     * @return BankInformation
     */
    public function setJointAccountOwnerMiddleName($jointAccountOwnerMiddleName)
    {
        $this->joint_account_owner_middle_name = $jointAccountOwnerMiddleName;

        return $this;
    }

    /**
     * Get joint_account_owner_middle_name.
     *
     * @return string
     */
    public function getJointAccountOwnerMiddleName()
    {
        return $this->joint_account_owner_middle_name;
    }

    /**
     * Set joint_account_owner_last_name.
     *
     * @param string $jointAccountOwnerLastName
     *
     * @return BankInformation
     */
    public function setJointAccountOwnerLastName($jointAccountOwnerLastName)
    {
        $this->joint_account_owner_last_name = $jointAccountOwnerLastName;

        return $this;
    }

    /**
     * Get joint_account_owner_last_name.
     *
     * @return string
     */
    public function getJointAccountOwnerLastName()
    {
        return $this->joint_account_owner_last_name;
    }

    /**
     * Get joint account owner full name.
     *
     * @return string
     */
    public function getJointAccountOwnerFullName()
    {
        return $this->getJointAccountOwnerFirstName().' '.$this->getJointAccountOwnerMiddleName().' '
            .$this->getJointAccountOwnerLastName();
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return BankInformation
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
     * Set account_title.
     *
     * @param string $accountTitle
     *
     * @return BankInformation
     */
    public function setAccountTitle($accountTitle)
    {
        $this->account_title = $accountTitle;

        return $this;
    }

    /**
     * Get account_title.
     *
     * @return string
     */
    public function getAccountTitle()
    {
        return $this->account_title;
    }

    /**
     * Set phone_number.
     *
     * @param string $phoneNumber
     *
     * @return BankInformation
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
     * Set routing_number.
     *
     * @param string $routingNumber
     *
     * @return BankInformation
     */
    public function setRoutingNumber($routingNumber)
    {
        $this->routing_number = $routingNumber;

        return $this;
    }

    /**
     * Get routing_number.
     *
     * @return string
     */
    public function getRoutingNumber()
    {
        return $this->routing_number;
    }

    /**
     * Set account_number.
     *
     * @param string $accountNumber
     *
     * @return BankInformation
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
     * Get array ENUM values type column.
     *
     * @static
     *
     * @return array
     */
    public static function getAccountTypeChoices()
    {
        // Build $_typeValues if this is the first call
        if (null === self::$_accountTypeValues) {
            self::$_accountTypeValues = [];
            $oClass = new \ReflectionClass('\App\Entity\BankInformation');
            $classConstants = $oClass->getConstants();
            $constantPrefix = 'ACCOUNT_TYPE_';
            foreach ($classConstants as $key => $val) {
                if (substr($key, 0, strlen($constantPrefix)) === $constantPrefix) {
                    self::$_accountTypeValues[$val] = $val;
                }
            }
        }

        return self::$_accountTypeValues;
    }

    /**
     * Set account_type.
     *
     * @param string $accountType
     *
     * @return BankInformation
     *
     * @throws \InvalidArgumentException
     */
    public function setAccountType($accountType)
    {
        if (!is_null($accountType) && !array_key_exists($accountType, self::getAccountTypeChoices())) {
            throw new \InvalidArgumentException(
                sprintf('Invalid value for bank_information.account_type : %s.', $accountType)
            );
        }

        $this->account_type = $accountType;

        return $this;
    }

    /**
     * Get account_type.
     *
     * @return string
     */
    public function getAccountType()
    {
        return $this->account_type;
    }

    /**
     * Set pdf_copy.
     *
     * @param string $pdfCopy
     *
     * @return BankInformation
     */
    public function setPdfCopy($pdfCopy)
    {
        $this->pdf_copy = $pdfCopy;

        return $this;
    }

    /**
     * Get pdf_copy.
     *
     * @return string
     */
    public function getPdfCopy()
    {
        return $this->pdf_copy;
    }

    /**
     * Set pdf_copy_file.
     *
     * @param $pdfCopyFile
     *
     * @return BankInformation
     */
    public function setPdfCopyFile($pdfCopyFile)
    {
        $this->pdf_copy_file = $pdfCopyFile;
        $this->setUpdated(new \DateTime());

        return $this;
    }

    /**
     * Get pdf_copy_file.
     *
     * @return UploadedFile
     */
    public function getPdfCopyFile()
    {
        return $this->pdf_copy_file;
    }

    public function getAbsolutePdfCopy()
    {
        return null === $this->getPdfCopy() ? null : $this->getUploadRootDir().'/'.$this->getPdfCopy();
    }

    public function getWebPdfCopy()
    {
        return null === $this->getPdfCopy() ? null : $this->getUploadDir().'/'.$this->getPdfCopy();
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded documents should be saved
        return getcwd().'/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // get rid of the __DIR__ so it doesn't screw when displaying uploaded doc/image in the view.
        return 'uploads/bank_information/pdf_copy';
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function preUpload()
    {
        if (null !== $this->pdf_copy_file) {
            $oldFile = $this->getAbsolutePdfCopy();
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }

            // do whatever you want to generate a unique name
            $this->pdf_copy = sha1(uniqid(mt_rand(), true)).'.'.$this->pdf_copy_file->guessExtension();
        }
    }

    /**
     * @ORM\PostPersist
     * @ORM\PostUpdate
     */
    public function upload()
    {
        if (null === $this->pdf_copy_file) {
            return;
        }

        // if there is an error when moving the file, an exception will
        // be automatically thrown by move(). This will properly prevent
        // the entity from being persisted to the database on error
        $this->pdf_copy_file->move($this->getUploadRootDir(), $this->pdf_copy);

        unset($this->pdf_copy_file);
    }

    /**
     * @ORM\PostRemove
     */
    public function removeUpload()
    {
        $file = $this->getAbsolutePdfCopy();
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return BankInformation
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
     * Set client_id.
     *
     * @param int $clientId
     *
     * @return BankInformation
     */
    public function setClientId($clientId)
    {
        $this->client_id = $clientId;

        return $this;
    }

    /**
     * Get client_id.
     *
     * @return int
     */
    public function getClientId()
    {
        return $this->client_id;
    }

    /**
     * Set client.
     *
     * @param \App\Entity\User $client
     *
     * @return BankInformation
     */
    public function setClient(User $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return \App\Entity\User
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get workflow message code.
     *
     * @return string
     */
    public function getWorkflowMessageCode()
    {
        return Workflow::MESSAGE_CODE_PAPERWORK_UPDATE_BANKING_INFORMATION;
    }

    public function __toString()
    {
        return $this->getName().', Account Number: '.$this->getAccountNumber();
    }

    /**
     * Set pdfDocument.
     *
     * @param \App\Entity\Document $pdfDocument
     *
     * @return BankInformation
     */
    public function setPdfDocument(Document $pdfDocument = null)
    {
        $this->pdfDocument = $pdfDocument;

        return $this;
    }

    /**
     * Get pdfDocument.
     *
     * @return \App\Entity\Document
     */
    public function getPdfDocument()
    {
        return $this->pdfDocument;
    }
}
