<?php

namespace Model\Pas;

class Security extends Base
{
    /**
     * @var  string
     */
    protected $symbol;

    /**
     * @var  string
     */
    protected $name;

    /**
     * @var int
     */
    protected $securityTypeId;

    const SYMBOL_IDA12 = 'IDA12';
    const SYMBOL_CASH  = 'CASH';

    public function setName($name)
    {
        return $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setSymbol($symbol)
    {
        return $this->symbol = $symbol;
    }

    public function getSymbol()
    {
        return $this->symbol;
    }

    public function setSecurityTypeId($securityTypeId)
    {
        $this->securityTypeId = $securityTypeId;

        return $this;
    }

    public function getSecurityTypeId()
    {
        return $this->securityTypeId;
    }

    /**
     * @return bool
     */
    public function isTypeCash()
    {
        return ($this->symbol === self::SYMBOL_IDA12 || $this->symbol === self::SYMBOL_CASH);
    }
}