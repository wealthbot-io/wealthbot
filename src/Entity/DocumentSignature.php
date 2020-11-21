<?php

namespace App\Entity;

use App\Exception\InvalidEnvelopeStatusException;
use App\Model\Envelope;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Class DocumentSignature
 * @package App\Entity
 */
class DocumentSignature
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $source_id;

    /**
     * @var int
     */
    private $document_id;

    /**
     * @param \App\Entity\Document
     */
    private $document;

    /**
     * @var string
     */
    private $docusign_envelope_id;

    /**
     * @var string
     */
    private $type;

    const TYPE_OPEN_OR_TRANSFER_ACCOUNT = 'open_or_transfer_account';
    const TYPE_TRANSFER_INFORMATION = 'transfer_information';
    const TYPE_AUTO_INVEST_CONTRIBUTION = 'auto_invest_contribution';
    const TYPE_AUTO_DISTRIBUTION = 'auto_distribution';
    const TYPE_ONE_TIME_CONTRIBUTION = 'one_time_contribution';
    const TYPE_ONE_TIME_DISTRIBUTION = 'one_time_distribution';
    const TYPE_CHANGE_BENEFICIARY = 'change_beneficiary';
    const TYPE_BANK_INFORMATION  = 'bank_information';

    //const TYPE_BANK_INFORMATION = 'bank_information';

    // TODO: uncomment after beta release
    /*const TYPE_CHANGE_ADDRESS = 'change_address';*/

    private static $_types = null;

    /**
     * @var string
     */
    private $status;

    /**
     * @var bool
     */
    private $active;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $updated;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $ownerSignatures;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->ownerSignatures = new \Doctrine\Common\Collections\ArrayCollection();
        $this->active = false;
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
     * Set source_id.
     *
     * @param int $sourceId
     *
     * @return DocumentSignature
     */
    public function setSourceId($sourceId)
    {
        $this->source_id = $sourceId;

        return $this;
    }

    /**
     * Get source_id.
     *
     * @return int
     */
    public function getSourceId()
    {
        return $this->source_id;
    }

    /**
     * Set document_id.
     *
     * @param int $documentId
     *
     * @return DocumentSignature
     */
    public function setDocumentId($documentId)
    {
        $this->document_id = $documentId;

        return $this;
    }

    /**
     * Get document_id.
     *
     * @return int
     */
    public function getDocumentId()
    {
        return $this->document_id;
    }

    /**
     * Set document.
     *
     * @param \App\Entity\Document $document
     *
     * @return DocumentSignature
     */
    public function setDocument(Document $document = null)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document.
     *
     * @return \App\Entity\Document
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set docusign_envelope_id.
     *
     * @param string $docusignEnvelopeId
     *
     * @return DocumentSignature
     */
    public function setDocusignEnvelopeId($docusignEnvelopeId)
    {
        $this->docusign_envelope_id = $docusignEnvelopeId;

        return $this;
    }

    /**
     * Get docusign_envelope_id.
     *
     * @return string
     */
    public function getDocusignEnvelopeId()
    {
        return $this->docusign_envelope_id;
    }

    /**
     * Get type choices.
     *
     * @return array
     */
    public static function getTypeChoices()
    {
        if (null === self::$_types) {
            self::$_types = [];

            $rClass = new \ReflectionClass('App\\Entity\\DocumentSignature');
            $prefix = 'TYPE_';

            foreach ($rClass->getConstants() as $key => $value) {
                if (substr($key, 0, strlen($prefix)) === $prefix) {
                    self::$_types[$value] = $value;
                }
            }
        }

        return self::$_types;
    }

    /**
     * Set type.
     *
     * @param $type
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setType($type)
    {
        if (!in_array($type, self::getTypeChoices())) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid value: %s for document_signature.type column',
                $type
            ));
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return $this
     *
     * @throws \App\Exception\InvalidEnvelopeStatusException
     */
    public function setStatus($status)
    {
        if (!in_array($status, Envelope::getStatusChoices())) {
            throw new InvalidEnvelopeStatusException(sprintf('Invalid envelope status: %s', $status));
        }

        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Is signature created.
     * Returns true if status is 'created' or 'sent'.
     *
     *
     * @return bool
     */
    public function isCreated()
    {
        return Envelope::STATUS_CREATED === $this->status;
    }

    /**
     * Is signature completed.
     *
     * @return bool
     */
    public function isCompleted()
    {
        return Envelope::STATUS_COMPLETED === $this->status || Envelope::STATUS_SIGNED === $this->status;
    }

    /**
     * Set active.
     *
     * @param bool $active
     *
     * @return DocumentSignature
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->getActive();
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return DocumentSignature
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
     * @return DocumentSignature
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
     * Add ownerSignatures.
     *
     * @param \App\Entity\DocumentOwnerSignature $ownerSignatures
     *
     * @return DocumentSignature
     */
    public function addOwnerSignature(DocumentOwnerSignature $ownerSignatures)
    {
        $this->ownerSignatures[] = $ownerSignatures;

        return $this;
    }

    /**
     * Remove ownerSignatures.
     *
     * @param \App\Entity\DocumentOwnerSignature $ownerSignatures
     */
    public function removeOwnerSignature(DocumentOwnerSignature $ownerSignatures)
    {
        $this->ownerSignatures->removeElement($ownerSignatures);
    }

    /**
     * Get ownerSignatures.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOwnerSignatures()
    {
        return $this->ownerSignatures;
    }

    /**
     * Get signature order.
     *
     * @return int
     */
    public function getOrder()
    {
        switch ($this->type) {
            case self::TYPE_OPEN_OR_TRANSFER_ACCOUNT:
                $order = 1;
                break;
            case self::TYPE_TRANSFER_INFORMATION:
                $order = 2;
                break;
            case self::TYPE_AUTO_INVEST_CONTRIBUTION:
                $order = 3;
                break;
            case self::TYPE_AUTO_DISTRIBUTION:
                $order = 4;
                break;
            case self::TYPE_ONE_TIME_CONTRIBUTION:
                $order = 5;
                break;
            case self::TYPE_ONE_TIME_DISTRIBUTION:
                $order = 6;
                break;
            default:
                $order = 7;
                break;
        }

        return $order;
    }

//    /**
//     * Get document signature activity by type and client account group
//     *
//     * @return string
//     */
//    public function getActivity()
//    {
//        $type = $this->getType();
//        $accountGroup = $this->clientAccount->getGroupName();
//
//        switch ($type) {
//            case self::TYPE_OPEN_ACCOUNT:
//                if ($accountGroup === AccountGroup::GROUP_DEPOSIT_MONEY) {
//                    $activity = 'Open an account';
//                } elseif ($accountGroup === AccountGroup::GROUP_FINANCIAL_INSTITUTION) {
//                    $activity = 'Transfer an account';
//                } elseif ($accountGroup === AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT) {
//                    $activity = 'Rollover a 401(k)';
//                } else {
//                    $activity = 'Undefined';
//                }
//                break;
//
//            case self::TYPE_TRANSFER_ACCOUNT:
//                $activity = 'Transfer Account';
//                break;
//
//            case self::TYPE_BANK_TRANSFER:
//                $activity = 'Bank Transfer';
//                break;
//
//            default:
//                $activity = 'Undefined';
//                break;
//        }
//
//        return $activity;
//    }
}
