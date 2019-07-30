<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use App\Model\Bill as BaseBill;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;

/**
 * Class Bill
 * @package App\Entity
 */
class Bill extends BaseBill
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $quarter;

    /**
     * @var int
     */
    protected $year;

    /**
     * @var BillItem|ArrayCollection
     */
    protected $billItems;

    /**
     * @var User
     */
    protected $client;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->billItems = new ArrayCollection();

        parent::__construct();
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
     * Set quarter.
     *
     * @param int $quarter
     *
     * @return $this
     */
    public function setQuarter($quarter)
    {
        $this->quarter = $quarter;

        return $this;
    }

    /**
     * Get quarter.
     *
     * @return int
     */
    public function getQuarter()
    {
        return $this->quarter;
    }

    /**
     * Set year.
     *
     * @param int $year
     *
     * @return $this
     */
    public function setYear($year)
    {
        $this->year = $year;

        return $this;
    }

    /**
     * Get year.
     *
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    public function addBillItem(BillItem $billItem)
    {
        $this->billItems[] = $billItem;

        return $this;
    }

    public function removeBillItem(BillItem $billItem)
    {
        $this->billItems->removeElement($billItem);

        return $this;
    }

    /**
     * @return ArrayCollection|BillItem[]
     */
    public function getBillItems()
    {
        return $this->billItems;
    }

    public function setBillItems($billItems)
    {
        $this->billItems = $billItems;

        return $this;
    }

    /**
     * @param \App\Entity\User $client
     */
    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * @return \App\Entity\User
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @var \DateTime
     */
    private $approvedAt;

    /**
     * Set approvedAt.
     *
     * @param \DateTime $approvedAt
     *
     * @return Bill
     */
    public function setApprovedAt($approvedAt)
    {
        $this->approvedAt = $approvedAt;

        return $this;
    }

    /**
     * Get approvedAt.
     *
     * @return \DateTime
     */
    public function getApprovedAt()
    {
        return $this->approvedAt;
    }
}
