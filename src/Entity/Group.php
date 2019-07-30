<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\Group as BaseGroup;

/**
 * Class Group
 * @package App\Entity
 */
class Group extends BaseGroup
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $users;


    /**
     * @var array
     */
    protected $roles;

    /**
     * @var int
     */
    private $owner_id;

    /**
     * @param \App\Entity\User
     */
    private $owner;

    const GROUP_NAME_ALL = 'All';

    public function __construct($name = null, $roles = [])
    {
        parent::__construct($name, $roles);
        $this->users = new ArrayCollection();

        $this->roles = [];
        $this->name = $name;
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
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName();
    }

    /**
     * Set roles.
     *
     * @param array $roles
     *
     * @return Group
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles.
     *
     * @return array
     */
    public function getRoles()
    {
        if (!$this->roles) {
            return [];
        }

        return $this->roles;
    }

    /**
     * Add users.
     *
     * @param \App\Entity\User $users
     *
     * @return Group
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
     * Set owner_id.
     *
     * @param int $ownerId
     *
     * @return Group
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
     * @return Group
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

    public function isAll()
    {
        return self::GROUP_NAME_ALL === $this->name;
    }

    public function getRiaCount($riaId)
    {
        $count = 0;

        /** @var User $user */
        foreach ($this->users as $user) {
            if (($user->hasRole('ROLE_RIA_ADMIN') || $user->hasRole('ROLE_RIA_USER')) && $user->getProfile()->getRia()->getId() === $riaId) {
                ++$count;
            }
        }

        return $count;
    }
}
