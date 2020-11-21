<?php

namespace App\Entity;

use App\Exception\InvalidRecipientStatusException;

/**
 * Class DocumentOwnerSignature
 * @package App\Entity
 */
class DocumentOwnerSignature
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $document_signature_id;

    /**
     * @var int
     */
    private $owner_client_user_id;

    /**
     * @var int
     */
    private $owner_contact_id;

    /**
     * @var string
     */
    private $status;

    // Status constants
    const STATUS_SENT = 'sent';
    const STATUS_CREATED = 'created';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_SIGNED = 'signed';
    const STATUS_DECLINED = 'declined';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAX_PENDING = 'faxpending';
    const STATUS_AUTO_RESPONDED = 'autoresponded';

    private static $_statuses = null;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var \DateTime
     */
    private $updated;

    /**
     * @param \App\Entity\DocumentSignature
     */
    private $documentSignature;

    /**
     * @param \App\Entity\User
     */
    private $clientOwner;

    /**
     * @param \App\Entity\ClientAdditionalContact
     */
    private $contactOwner;

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
     * Set document_signature_id.
     *
     * @param int $documentSignatureId
     *
     * @return DocumentOwnerSignature
     */
    public function setDocumentSignatureId($documentSignatureId)
    {
        $this->document_signature_id = $documentSignatureId;

        return $this;
    }

    /**
     * Get document_signature_id.
     *
     * @return int
     */
    public function getDocumentSignatureId()
    {
        return $this->document_signature_id;
    }

    /**
     * Set owner_client_user_id.
     *
     * @param int $ownerClientUserId
     *
     * @return DocumentOwnerSignature
     */
    public function setOwnerClientUserId($ownerClientUserId)
    {
        $this->owner_client_user_id = $ownerClientUserId;

        return $this;
    }

    /**
     * Get owner_client_user_id.
     *
     * @return int
     */
    public function getOwnerClientUserId()
    {
        return $this->owner_client_user_id;
    }

    /**
     * Set owner_contact_id.
     *
     * @param int $ownerContactId
     *
     * @return DocumentOwnerSignature
     */
    public function setOwnerContactId($ownerContactId)
    {
        $this->owner_contact_id = $ownerContactId;

        return $this;
    }

    /**
     * Get owner_contact_id.
     *
     * @return int
     */
    public function getOwnerContactId()
    {
        return $this->owner_contact_id;
    }

    /**
     * Get status choices.
     *
     * @return array
     */
    public static function getStatusChoices()
    {
        if (null === self::$_statuses) {
            self::$_statuses = [];

            $rClass = new \ReflectionClass('App\\Entity\\DocumentOwnerSignature');
            $prefix = 'STATUS_';

            foreach ($rClass->getConstants() as $key => $value) {
                if (substr($key, 0, strlen($prefix)) === $prefix) {
                    self::$_statuses[$value] = $value;
                }
            }
        }

        return self::$_statuses;
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return $this
     *
     * @throws InvalidRecipientStatusException
     */
    public function setStatus($status)
    {
        if (!array_key_exists($status, self::getStatusChoices())) {
            throw new InvalidRecipientStatusException(sprintf(
                'Invalid value: %s for document_owner_signature.status column',
                $status
            ));
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
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return DocumentOwnerSignature
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
     * @return DocumentOwnerSignature
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
     * Set documentSignature.
     *
     * @param \App\Entity\DocumentSignature $documentSignature
     *
     * @return DocumentOwnerSignature
     */
    public function setDocumentSignature(DocumentSignature $documentSignature = null)
    {
        $this->documentSignature = $documentSignature;

        return $this;
    }

    /**
     * Get documentSignature.
     *
     * @return \App\Entity\DocumentSignature
     */
    public function getDocumentSignature()
    {
        return $this->documentSignature;
    }

    /**
     * Set clientOwner.
     *
     * @param \App\Entity\User $clientOwner
     *
     * @return DocumentOwnerSignature
     */
    public function setClientOwner(User $clientOwner = null)
    {
        $this->clientOwner = $clientOwner;

        return $this;
    }

    /**
     * Get clientOwner.
     *
     * @return \App\Entity\User
     */
    public function getClientOwner()
    {
        return $this->clientOwner;
    }

    /**
     * Set contactOwner.
     *
     * @param \App\Entity\ClientAdditionalContact $contactOwner
     *
     * @return DocumentOwnerSignature
     */
    public function setContactOwner(ClientAdditionalContact $contactOwner = null)
    {
        $this->contactOwner = $contactOwner;

        return $this;
    }

    /**
     * Get contactOwner.
     *
     * @return \App\Entity\ClientAdditionalContact
     */
    public function getContactOwner()
    {
        return $this->contactOwner;
    }

    /**
     * Is owner signature completed.
     *
     * @return bool
     */
    public function isCompleted()
    {
        return self::STATUS_COMPLETED === $this->getStatus() || self::STATUS_SIGNED === $this->getStatus();
    }
}
