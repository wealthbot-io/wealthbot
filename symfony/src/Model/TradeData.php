<?php

namespace App\Model;

use App\Entity\RebalancerQueue;

class TradeData
{
    /** @var int */
    private $id;

    /** @var int */
    private $jobId;

    /** @var int */
    private $accountId;

    /** @var int */
    private $securityId;

    /** @var string */
    private $accountNumber;

    /** @var int */
    private $accountType;

    /** @var string */
    private $securityType;

    /** @var string */
    private $action;

    /** @var string */
    private $quantityType;

    /** @var int */
    private $quantity;

    /** @var string */
    private $symbol;

    /** @var string */
    private $exchangeSwapSymbol;

    /** @var string */
    private $orderType;

    /** @var string */
    private $limitPrice;

    /** @var string */
    private $stopPrice;

    /** @var string */
    private $timeInForce;

    /** @var bool */
    private $isDoNotReduce;

    /** @var bool */
    private $isAllOrNone;

    /** @var bool */
    private $isReinvestDividends;

    /** @var bool */
    private $isIncludeTransactionFee;

    /** @var bool */
    private $isReinvestCapGains;

    /** @var string */
    private $taxLotIdMethod;

    /** @var array */
    private $vsps;

    const ACCOUNT_TYPE_CASH_ACCOUNT = 1;

    const SECURITY_TYPE_EQUITY = 'E';
    const SECURITY_TYPE_MUTUAL_FUND = 'M';

    const ACTION_BUY = 'B';
    const ACTION_SELL = 'S';
    const ACTION_SHORT_SELL = 'SS';
    const ACTION_BUY_TO_COVER = 'BC';
    const ACTION_EXCHANGE = 'X';
    const ACTION_SWAP = 'SW';

    const QUANTITY_TYPE_DOLLARS = 'D';
    const QUANTITY_TYPE_SHARES = 'S';
    const QUANTITY_TYPE_ALL_SHARES = 'AS';

    const ORDER_TYPE_MARKET_ORDER = 'M';
    const ORDER_TYPE_LIMIT_ORDER = 'L';
    const ORDER_TYPE_STOP_ORDER = 'S';
    const ORDER_TYPE_STOP_LIMIT_ORDER = 'SL';
    const ORDER_TYPE_TRAILING_STOP_ORDER_PERCENT = 'TSP';
    const ORDER_TYPE_TRAILING_STOP_ORDER_DOLLAR = 'TSD';

    const TIME_IN_FORCE_GOOD_TILL_END_OF_DAY = 'DAY';
    const TIME_IN_FORCE_GOOD_TILL_DATE = 'GTD';

    const TAX_LOT_ID_METHOD_FIFO = 'F';
    const TAX_LOT_ID_METHOD_LIFO = 'L';
    const TAX_LOT_ID_METHOD_HIGHEST_COST = 'H';
    const TAX_LOT_ID_METHOD_LOWEST_COST = 'C';
    const TAX_LOT_ID_METHOD_TAX_EFFICIENT_LOSS_HARVESTER = 'T';
    const TAX_LOT_ID_METHOD_SPECIFIC_LOT = 'S';

    public function __construct()
    {
        $this->accountType = self::ACCOUNT_TYPE_CASH_ACCOUNT;
        $this->exchangeSwapSymbol = '';
        $this->orderType = self::ORDER_TYPE_MARKET_ORDER;
        $this->limitPrice = '';
        $this->stopPrice = '';
        $this->timeInForce = self::TIME_IN_FORCE_GOOD_TILL_END_OF_DAY;
        $this->isDoNotReduce = '';
        $this->isAllOrNone = '';
        $this->isReinvestDividends = false;
        $this->isIncludeTransactionFee = true;
        $this->isReinvestCapGains = false;
        $this->taxLotIdMethod = self::TAX_LOT_ID_METHOD_SPECIFIC_LOT;

        $this->vsps = [];
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $jobId
     *
     * @return $this
     */
    public function setJobId($jobId)
    {
        $this->jobId = $jobId;

        return $this;
    }

    /**
     * @return int
     */
    public function getJobId()
    {
        return $this->jobId;
    }

    /**
     * @param int $accountId
     *
     * @return $this
     */
    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;

        return $this;
    }

    /**
     * @return int
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @param int $securityId
     *
     * @return $this
     */
    public function setSecurityId($securityId)
    {
        $this->securityId = $securityId;

        return $this;
    }

    /**
     * @return int
     */
    public function getSecurityId()
    {
        return $this->securityId;
    }

    /**
     * @param $accountNumber
     *
     * @return $this
     */
    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param int $accountType
     *
     * @return $this
     */
    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;

        return $this;
    }

    /**
     * @return int
     */
    public function getAccountType()
    {
        return $this->accountType;
    }

    /**
     * @param string $action
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setAction($action)
    {
        if (0 === strcasecmp($action, RebalancerQueue::STATUS_SELL)) {
            $action = self::ACTION_SELL;
        } elseif (0 === strcasecmp($action, RebalancerQueue::STATUS_BUY)) {
            $action = self::ACTION_BUY;
        }

        if (0 === strcasecmp($action, self::ACTION_BUY) || 0 === strcasecmp($action, self::ACTION_SELL)) {
            $this->action = ucwords($action);
        } else {
            throw new \Exception('Action must be '.self::ACTION_BUY.' or '.self::ACTION_SELL);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $exchangeSwapSymbol
     *
     * @return $this
     */
    public function setExchangeSwapSymbol($exchangeSwapSymbol)
    {
        $this->exchangeSwapSymbol = $exchangeSwapSymbol;

        return $this;
    }

    /**
     * @return string
     */
    public function getExchangeSwapSymbol()
    {
        return $this->exchangeSwapSymbol;
    }

    /**
     * @param bool $isAllOrNone
     *
     * @return $this
     */
    public function setIsAllOrNone($isAllOrNone)
    {
        $this->isAllOrNone = $isAllOrNone;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsAllOrNone()
    {
        return $this->isAllOrNone;
    }

    /**
     * @param bool $isDoNotReduce
     *
     * @return $this
     */
    public function setIsDoNotReduce($isDoNotReduce)
    {
        $this->isDoNotReduce = $isDoNotReduce;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDoNotReduce()
    {
        return $this->isDoNotReduce;
    }

    /**
     * @param bool $isIncludeTransactionFee
     *
     * @return $this
     */
    public function setIsIncludeTransactionFee($isIncludeTransactionFee)
    {
        $this->isIncludeTransactionFee = $isIncludeTransactionFee;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsIncludeTransactionFee()
    {
        return $this->isIncludeTransactionFee;
    }

    /**
     * @param bool $isReinvestDividends
     *
     * @return $this
     */
    public function setIsReinvestDividends($isReinvestDividends)
    {
        $this->isReinvestDividends = $isReinvestDividends;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsReinvestDividends()
    {
        return $this->isReinvestDividends;
    }

    /**
     * @param bool $isReinvestCapGains
     *
     * @return $this
     */
    public function setIsReinvestCapGains($isReinvestCapGains)
    {
        $this->isReinvestCapGains = $isReinvestCapGains;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsReinvestCapGains()
    {
        return $this->isReinvestCapGains;
    }

    /**
     * @param string $limitPrice
     *
     * @return $this
     */
    public function setLimitPrice($limitPrice)
    {
        $this->limitPrice = $limitPrice;

        return $this;
    }

    /**
     * @return string
     */
    public function getLimitPrice()
    {
        return $this->limitPrice;
    }

    /**
     * @param string $orderType
     *
     * @return $this
     */
    public function setOrderType($orderType)
    {
        $this->orderType = $orderType;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrderType()
    {
        return $this->orderType;
    }

    /**
     * @param int $quantity
     *
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param string $quantityType
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setQuantityType($quantityType)
    {
        $quantityType = strtoupper($quantityType);

        if (self::QUANTITY_TYPE_ALL_SHARES === $quantityType || self::QUANTITY_TYPE_SHARES === $quantityType) {
            $this->quantityType = $quantityType;
        } else {
            throw new \Exception('Quantity Type must be '.self::QUANTITY_TYPE_SHARES.' or '.self::QUANTITY_TYPE_ALL_SHARES);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getQuantityType()
    {
        return $this->quantityType;
    }

    /**
     * @param string $securityType
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function setSecurityType($securityType)
    {
        $securityType = ucfirst(substr($securityType, 0, 1));

        if (self::SECURITY_TYPE_EQUITY === $securityType || self::SECURITY_TYPE_MUTUAL_FUND === $securityType) {
            $this->securityType = $securityType;
        } else {
            throw new \Exception('Security Type must be '.self::SECURITY_TYPE_EQUITY.' or '.self::SECURITY_TYPE_MUTUAL_FUND);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getSecurityType()
    {
        return $this->securityType;
    }

    /**
     * @param string $stopPrice
     *
     * @return $this
     */
    public function setStopPrice($stopPrice)
    {
        $this->stopPrice = $stopPrice;

        return $this;
    }

    /**
     * @return string
     */
    public function getStopPrice()
    {
        return $this->stopPrice;
    }

    /**
     * @param string $symbol
     *
     * @return $this
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * @param string $taxLotIdMethod
     *
     * @return $this
     */
    public function setTaxLotIdMethod($taxLotIdMethod)
    {
        $this->taxLotIdMethod = $taxLotIdMethod;

        return $this;
    }

    /**
     * @return string
     */
    public function getTaxLotIdMethod()
    {
        return $this->taxLotIdMethod;
    }

    /**
     * @param string $timeInForce
     *
     * @return $this
     */
    public function setTimeInForce($timeInForce)
    {
        $this->timeInForce = $timeInForce;

        return $this;
    }

    /**
     * @return string
     */
    public function getTimeInForce()
    {
        return $this->timeInForce;
    }

    /**
     * @param array $vsps
     *
     * @return $this
     */
    public function setVsps($vsps)
    {
        $this->vsps = $vsps;

        return $this;
    }

    /**
     * @return array
     */
    public function getVsps()
    {
        return $this->vsps;
    }

    public function toArrayForTradeFile()
    {
        return [
            'A' => $this->getAccountNumber(),
            'B' => $this->getAccountType(),
            'C' => $this->getSecurityType(),
            'D' => $this->getAction(),
            'E' => $this->getQuantityType(),
            'F' => (self::QUANTITY_TYPE_ALL_SHARES === $this->getQuantityType() ? '' : $this->getQuantity()),
            'G' => $this->getSymbol(),
            'H' => $this->getExchangeSwapSymbol(),
            'I' => $this->getOrderType(),
            'J' => $this->getLimitPrice(),
            'K' => $this->getStopPrice(),
            'L' => $this->getTimeInForce(),
            'M' => $this->getIsDoNotReduce(),
            'N' => $this->getIsAllOrNone(),
            'O' => $this->getIsReinvestDividends() ? 'Y' : 'N',
            'P' => $this->getIsIncludeTransactionFee() ? 'Y' : 'N',
            'Q' => $this->getIsReinvestCapGains() ? 'Y' : 'N',
            'R' => $this->getTaxLotIdMethod(),
        ];
    }

    /**
     * Load object data from array.
     *
     * @param array $data
     *
     * @throws \Exception
     */
    public function loadFromArray(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $setter = 'set'.ucfirst($key);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        }

        $tmp = explode('_', $key);

        $tmp = array_map(function ($item) {
            return ucfirst($item);
        }, $tmp);

        $setter = 'set'.implode('', $tmp);
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        }
    }
}
