<?php

namespace App\Entity;

/**
 * Class UserHistory
 * @package App\Entity
 */
class UserHistory
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $user_id;

    /**
     * @var int
     */
    private $updated_by_id;

    /**
     * @var string
     */
    private $description;

    /**
     * @var \DateTime
     */
    private $created;

    /**
     * @param \App\Entity\User
     */
    private $user;

    /**
     * @param \App\Entity\User
     */
    private $updater;

    /**
     * @var int
     */
    private $updater_type;

    const UPDATER_TYPE_ADMIN = 1;
    const UPDATER_TYPE_RIA = 2;
    const UPDATER_TYPE_CLIENT = 3;

    private static $_updaterTypes = [
        self::UPDATER_TYPE_ADMIN => 'Admin',
        self::UPDATER_TYPE_RIA => 'Ria',
        self::UPDATER_TYPE_CLIENT => 'Client',
    ];

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
     * Set user_id.
     *
     * @param int $userId
     *
     * @return UserHistory
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;

        return $this;
    }

    /**
     * Get user_id.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set updated_by_id.
     *
     * @param int $updatedById
     *
     * @return UserHistory
     */
    public function setUpdatedById($updatedById)
    {
        $this->updated_by_id = $updatedById;

        return $this;
    }

    /**
     * Get updated_by_id.
     *
     * @return int
     */
    public function getUpdatedById()
    {
        return $this->updated_by_id;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return UserHistory
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return UserHistory
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
     * Set user.
     *
     * @param \App\Entity\User $user
     *
     * @return UserHistory
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user.
     *
     * @return \App\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set updater.
     *
     * @param \App\Entity\User $updater
     *
     * @return UserHistory
     */
    public function setUpdater(User $updater = null)
    {
        $this->updater = $updater;

        return $this;
    }

    /**
     * Get updater.
     *
     * @return \App\Entity\User
     */
    public function getUpdater()
    {
        return $this->updater;
    }

    /**
     * Get updater_type choices.
     *
     * @return array
     */
    public static function getUpdaterTypeChoices()
    {
        return self::$_updaterTypes;
    }

    /**
     * Set updater_type.
     *
     * @param int $updaterType
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setUpdaterType($updaterType)
    {
        if (!array_key_exists($updaterType, self::getUpdaterTypeChoices())) {
            throw new \InvalidArgumentException(sprintf('Invalid value for updater_type column: %s', $updaterType));
        }

        $this->updater_type = $updaterType;

        return $this;
    }

    /**
     * Get updater_type.
     *
     * @return int
     */
    public function getUpdaterType()
    {
        return $this->updater_type;
    }

    /**
     * Get updater_type as string.
     *
     * @return string
     */
    public function getUpdaterTypeAsString()
    {
        return self::$_updaterTypes[$this->getUpdaterType()];
    }

    /**
     * Is updated by admin user.
     *
     * @return bool
     */
    public function isUpdatedByAdmin()
    {
        return self::UPDATER_TYPE_ADMIN === $this->updater_type;
    }

    /**
     * Is updated by ria user.
     *
     * @return bool
     */
    public function isUpdatedByRia()
    {
        return self::UPDATER_TYPE_RIA === $this->updater_type;
    }

    /**
     * Is updated by client.
     *
     * @return bool
     */
    public function isUpdatedByClient()
    {
        return self::UPDATER_TYPE_CLIENT === $this->updater_type;
    }
}
