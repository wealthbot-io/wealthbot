<?php

namespace Wealthbot\AdminBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(collection="prices")
 */
class Price
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String(name="symbol")
     */
    protected $symbol;

    /**
     * @MongoDB\String(name="security_type")
     */
    protected $securityType;

    /**
     * @MongoDB\String(name="date")
     */
    protected $date;

    /**
     * @MongoDB\Float(name="price")
     */
    protected $price;

    /**
     * @MongoDB\String(name="factor")
     */
    protected $factor;

    /**
     * @MongoDB\String(name="created")
     */
    protected $created;

    /**
     * @MongoDB\String(name="import_date")
     */
    protected $importDate;

    /**
     * @MongoDB\String(name="source")
     */
    protected $source;

    /**
     * @param mixed $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $factor
     */
    public function setFactor($factor)
    {
        $this->factor = $factor;
    }

    /**
     * @return mixed
     */
    public function getFactor()
    {
        return $this->factor;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $importDate
     */
    public function setImportDate($importDate)
    {
        $this->importDate = $importDate;
    }

    /**
     * @return mixed
     */
    public function getImportDate()
    {
        return $this->importDate;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $securityType
     */
    public function setSecurityType($securityType)
    {
        $this->securityType = $securityType;
    }

    /**
     * @return mixed
     */
    public function getSecurityType()
    {
        return $this->securityType;
    }

    /**
     * @param mixed $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $symbol
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;
    }

    /**
     * @return mixed
     */
    public function getSymbol()
    {
        return $this->symbol;
    }
}
