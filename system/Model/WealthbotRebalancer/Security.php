<?php
namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class Security extends Base {

    private $name;

    private $symbol;

    private $subclass;

    private $assetClass;

    private $isPreferredBuy;

    private $price;

    private $qty;

    private $amount;

    const POSITION_STATUS_INITIAL = 1;
    const POSITION_STATUS_IS_OPEN = 2;
    const POSITION_STATUS_IS_CLOSE = 3;
    const POSITION_STATUS_IS_NOT_VERIFIED = 4;

    const SYMBOL_IDA12 = 'IDA12';
    const SYMBOL_CASH  = 'CASH';

    public function __construct()
    {
        $this->amount = 0;
        $this->qty = 0;
    }

    /**
     * @param AssetClass $assetClass
     * @return $this
     */
    public function setAssetClass(AssetClass $assetClass)
    {
        $this->assetClass = $assetClass;

        return $this;
    }

    /**
     * @return AssetClass
     */
    public function getAssetClass()
    {
        return $this->assetClass;
    }

    /**
     * @param bool $isPreferredBuy
     * @return $this
     */
    public function setIsPreferredBuy($isPreferredBuy)
    {
        $this->isPreferredBuy = $isPreferredBuy;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsPreferredBuy()
    {
        return $this->isPreferredBuy;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isTypeCash()
    {
        return ($this->symbol === self::SYMBOL_IDA12 || $this->symbol === self::SYMBOL_CASH);
    }

    /**
     * @param Subclass $subclass
     * @return $this
     */
    public function setSubclass(Subclass $subclass)
    {
        $this->subclass = $subclass;

        return $this;
    }

    /**
     * @return Subclass
     */
    public function getSubclass()
    {
        return $this->subclass;
    }

    /**
     * @param string $symbol
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
     * @param float $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $qty
     * @return $this
     */
    public function setQty($qty)
    {
        $this->qty = $qty;

        return $this;
    }

    /**
     * @return int
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param float $amount
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    public function isCanBePurchased($count, $amount)
    {
        if (!$count || !$amount || $count <= 0 || $amount <= 0) {
            return false;
        }

        return true;
    }

    public function buy($count, $amount)
    {
        $this->qty += $count;
        $this->amount += $amount;
    }

    public function isCanBeSold($count, $amount)
    {
        if (!$count || !$amount || $count < 0 || $amount < 0 || $count > $this->qty || $amount > $this->amount) {
            return false;
        }

        return true;
    }

    public function sell($count, $amount)
    {
        $this->qty -= $count;
        $this->amount -= $amount;
    }

    public function sellAll()
    {
        $this->setQty(0);
        $this->setAmount(0);
    }

    public function loadFromArray(array $data = array())
    {
        foreach ($data as $key => $value) {
            if ($key === 'subclass') {
                $class = 'Model\WealthbotRebalancer\Subclass';
                $subclass = new $class;
                $subclass->loadFromArray($value);

                $this->setSubclass($subclass);
                $subclass->setSecurity($this);

            } elseif ($key === 'assetClass') {
                $class = 'Model\WealthbotRebalancer\AssetClass';
                $assetClass = new $class;
                $assetClass->loadFromArray($value);

                $this->setAssetClass($assetClass);

            } else {
                $this->$key = $value;
            }
        }
    }
}