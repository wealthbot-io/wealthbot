<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Document
 * @package App\Entity
 */
class Document
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $filename;

    /**
     * @var string
     */
    private $original_name;

    /**
     * @var string
     */
    private $mime_type;

    /**
     * @var string
     */
    private $type;

    // Constants for type attribute
    const TYPE_INVESTMENT_MANAGEMENT_AGREEMENT = 'investment_management_agreement';
    const TYPE_USER_AGREEMENT = 'user_agreement';
    const TYPE_PRIVACY_POLICY = 'privacy_policy';
    const TYPE_ADV = 'adv';
    const TYPE_ACCOUNT_DISCLOSURE = 'account_disclosure';
    const TYPE_IRA_ACCOUNT_DISCLOSURE = 'ira_account_disclosure';
    const TYPE_ROTH_ACCOUNT_DISCLOSURE = 'roth_account_disclosure';
    const TYPE_ANY = 'any';
    const TYPE_APPLICATION = 'application';
    const TYPE_ACCOUNT_TRANSFER_STATEMENT = 'account_transfer_statement';
    const TYPE_BANK_PDF_COPY = 'bank_pdf_copy';

    /**
     * @var null
     */
    protected static $_types = null;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @var UploadedFile
     */
    private $file;

    private $temp;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $users;

    /**
     * @var int
     */
    private $owner_id;

    /**
     * @param \App\Entity\User
     */
    private $owner;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
        $this->custodians = new ArrayCollection();
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
     * Set filename.
     *
     * @param string $filename
     *
     * @return Document
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set mime_type.
     *
     * @param string $mimeType
     *
     * @return Document
     */
    public function setMimeType($mimeType)
    {
        $this->mime_type = $mimeType;

        return $this;
    }

    /**
     * Get mime_type.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mime_type;
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

            $oClass = new \ReflectionClass('\App\Entity\Document');
            $classConstants = $oClass->getConstants();
            $constantPrefix = 'TYPE_';

            foreach ($classConstants as $key => $value) {
                if (substr($key, 0, strlen($constantPrefix)) === $constantPrefix) {
                    self::$_types[$value] = $value;
                }
            }
        }

        return self::$_types;
    }

    /**
     * Get type choices for admin.
     *
     * @return array
     */
    public static function getAdminTypeChoices()
    {
        $adminTypes = [
            //self::TYPE_INVESTMENT_MANAGEMENT_AGREEMENT,
            self::TYPE_USER_AGREEMENT,
            self:: TYPE_PRIVACY_POLICY,
            self::TYPE_ADV,
        ];

        return $adminTypes;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setType($type)
    {
        if (!in_array($type, self::getTypeChoices())) {
            throw new \InvalidArgumentException(sprintf('Invalid value for type: %s', $type));
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
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return Document
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
     * Set file.
     *
     * @param File $file
     *
     * @return $this
     */
    public function setFile(File $file = null)
    {
        $this->file = $file;

        if (is_file($this->getAbsolutePath())) {
            $this->temp = $this->getAbsolutePath();
        }

        if (null !== $file) {
            $extension = $file->getExtension() ? $file->getExtension() : $file->guessExtension();

            if ($file instanceof UploadedFile) {
                $this->setOriginalName($file->getClientOriginalName());

                if (!$extension) {
                    $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
                }

                $filename = sha1($file->getClientOriginalName().rand(0, 99999)).'.'.$extension;
                $mimeType = $file->getMimeType();
            } else {
                $this->setOriginalName($file->getFilename());

                $filename = sha1($file->getFilename().rand(0, 99999)).'.'.$extension;
                $mimeType = $file->getMimeType();
            }

            $this->setFilename($filename);
            $this->setMimeType($mimeType);
        }

        return $this;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        $result = str_replace('_', ' ', $this->getType());

        return ucwords($result);
    }

    /**
     * Get link.
     *
     * @return string
     */
    public function getLink()
    {
        return '/uploads/documents/'.$this->getFilename();
    }

    public function getRootLink()
    {
        return $this->getUploadRootDir().'/'.$this->getFilename();
    }

    /**
     * Upload file.
     */
    public function upload()
    {
        if (null === $this->getFile()) {
            return;
        }


        if (isset($this->temp)) {
            unlink($this->temp);
            $this->temp = null;
        }


        $this->getFile()->move($this->getUploadRootDir(), $this->getFilename());
        $this->setFile(null);
    }

    /**
     * Get absolute path.
     *
     * @return string|null
     */
    public function getAbsolutePath()
    {
        return null === $this->filename ? null : $this->getUploadRootDir().'/'.$this->filename;
    }

    /**
     * Get upload root dir.
     *
     * @return string
     */
    public static function getUploadRootDir()
    {
        return __DIR__.'/../../'.self::getUploadDir();
    }

    /**
     * Get upload dir.
     *
     * @return string
     */
    public static function getUploadDir()
    {
        return 'public/uploads/documents';
    }

    /**
     * Add users.
     *
     * @param \App\Entity\User $users
     *
     * @return Document
     */
    public function addUser(User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users.
     *
     * @param \App\Entity\User $users
     */
    public function removeUser(User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $custodians;

    /**
     * Add custodians.
     *
     * @param \App\Entity\Custodian $custodians
     *
     * @return Document
     */
    public function addCustodian(Custodian $custodians)
    {
        $this->custodians[] = $custodians;

        return $this;
    }

    /**
     * Remove custodians.
     *
     * @param \App\Entity\Custodian $custodians
     */
    public function removeCustodian(Custodian $custodians)
    {
        $this->custodians->removeElement($custodians);
    }

    /**
     * Get custodians.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCustodians()
    {
        return $this->custodians;
    }

    /**
     * Set original_name.
     *
     * @param string $originalName
     *
     * @return $this
     */
    public function setOriginalName($originalName)
    {
        $this->original_name = $originalName;

        return $this;
    }

    /**
     * Get original_name.
     *
     * @return string
     */
    public function getOriginalName()
    {
        return $this->original_name;
    }

    /**
     * Is application document.
     *
     * @return bool
     */
    public function isApplication()
    {
        return self::TYPE_APPLICATION === $this->type;
    }

    /**
     * Set owner_id.
     *
     * @param int $ownerId
     *
     * @return Document
     */
    public function setOwnerId($ownerId)
    {
        $this->owner_id = $ownerId;

        return $this;
    }

    /**
     * Get owner_id.
     *
     * @return int
     */
    public function getOwnerId()
    {
        return $this->owner_id;
    }

    /**
     * Set owner.
     *
     * @param \App\Entity\User $owner
     *
     * @return Document
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner.
     *
     * @return \App\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }
}
