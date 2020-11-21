<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;

/**
 * Class BillingSpec
 * @package App\Entity
 */
class BillingSpec
{
    const TYPE_TIER = 1;
    const TYPE_FLAT = 2;

    /**
     * @var int
     */
    private $id;

    /**
     * @var User
     */
    private $owner;

    /**
     * @var bool
     */
    private $master;

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $type;

    /**
     * @var float
     */
    private $minimalFee;

    /**
     * @var Fee[]|ArrayCollection
     */
    private $fees;

    /**
     * @var User[]|ArrayCollection
     */
    private $appointedUsers;

    public function __construct()
    {
        $this->master = true;
        $this->fees = new ArrayCollection();
        $this->appointedUsers = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param bool $master
     */
    public function setMaster($master)
    {
        $this->master = $master;
    }

    /**
     * @return bool
     */
    public function getMaster()
    {
        return $this->master;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param \App\Entity\User $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * @return \App\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param \App\Entity\Fee[] $fees
     */
    public function setFees($fees)
    {
        $this->fees = $fees;
    }

    /**
     * @param Fee $fee
     */
    public function addFee(Fee $fee)
    {
        $this->fees->add($fee);
        $fee->setBillingSpec($this);
    }

    /**
     * @param Fee $fee
     */
    public function removeFee(Fee $fee)
    {
        $this->fees->removeElement($fee);
    }

    /**
     * @return \App\Entity\Fee[]|ArrayCollection
     */
    public function getFees()
    {
        return $this->fees;
    }

    /**
     * @param float $minimalFee
     */
    public function setMinimalFee($minimalFee)
    {
        $this->minimalFee = $minimalFee;

        return $this;
    }

    /**
     * @return float
     */
    public function getMinimalFee()
    {
        return $this->minimalFee;
    }

    public function addAppointedUser(User $user)
    {
        $this->appointedUsers[] = $user;

        return $this;
    }

    public function removeAppointedUser(User $user)
    {
        $this->appointedUsers->removeElement($user);

        return $this;
    }

    public function getAppointedUsers()
    {
        return $this->appointedUsers;
    }

    public function setAppointedUsers(ArrayCollection $users)
    {
        $this->appointedUsers = $users;
    }

    public function __toString()
    {
        return (string) $this->name;
    }
}
