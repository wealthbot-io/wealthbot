<?php

namespace App\Entity;

/**
 * Class ClientRetirementFund
 * @package App\Entity
 */
class ClientRetirementFund
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $accounts;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->accounts = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param $name
     * @return $this
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
     * Add accounts.
     *
     * @param \App\Entity\ClientAccount $accounts
     *
     * @return $this;
     */
    public function addAccount(ClientAccount $accounts)
    {
        $this->accounts[] = $accounts;

        return $this;
    }

    /**
     * Remove accounts.
     *
     * @param \App\Entity\ClientAccount $accounts
     */
    public function removeAccount(ClientAccount $accounts)
    {
        $this->accounts->removeElement($accounts);
    }

    /**
     * Get accounts.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * @var string
     */
    private $symbol;

    /**
     * Set symbol.
     *
     * @param string $symbol
     *
     * @return ClientRetirementFund
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * Get symbol.
     *
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }
}
