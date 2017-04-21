<?php
namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class Lot extends Base
{

    const LOT_INITIAL = 1;
    const LOT_IS_OPEN = 2;
    const LOT_CLOSED = 3;

    /** @var  int */
	private $age;

    /** @var  float */
    private $amount;

    /** @var  int */
    private $quantity;

    /** @var \DateTime */
    private $date;

    /** @var int */
    private $status;

    /** @var Lot */
    private $initial;

    /** @var int */
    private $initialLotId;

    /** @var int */
    private $positionId;

    /** @var  bool */
    private $isMuni;

	/**
	 * is the lot current open or closed?
	 * @var bool
	 */
	private $wasClosed;

	// 6D - determine if lot has been sold (realized)
	// and if so was it a loss or gain?
	private $realizedGainOrLoss;

    /** @var float */
    private $costBasis;


    public function __construct()
    {
        $this->realizedGainOrLoss = 0;
    }

    /**
     * @param \Model\WealthbotRebalancer\Lot $initial
     * @return $this
     */
    public function setInitial(Lot $initial)
    {
        $this->initial = $initial;

        return $this;
    }

    /**
     * @return \Model\WealthbotRebalancer\Lot
     */
    public function getInitial()
    {
        return $this->initial;
    }

    /**
     * @param int $age
     * @return $this
     */
    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * Returns lot age in days
     *
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @param int $amount
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


    /**
     * @param int $quantity
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
     * @param \DateTime $date
     * @return $this
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Is status initial
     *
     * @return bool
     */
    public function isInitial()
    {
        return ($this->status == self::LOT_INITIAL);
    }

    /**
     * Is status open
     *
     * @return bool
     */
    public function isOpen()
    {
        return ($this->status == self::LOT_IS_OPEN);
    }

    /**
     * Is status closed
     *
     * @return bool
     */
    public function isClosed()
    {
        return ($this->status == self::LOT_CLOSED);
    }

    public function calcPrice()
    {
        if (!$this->quantity) {
            return null;
        }

        return $this->amount / $this->quantity;
    }

    public function sell($count)
    {
        if ($count > $this->quantity) {
            return false;
        }

        $price = $this->calcPrice();

        $this->amount -= $price * $count;
        $this->quantity -= $count;

        return true;
    }

    /**
     * @param bool $wasClosed
     * @return $this
     */
    public function setWasClosed($wasClosed)
    {
        $this->wasClosed = $wasClosed;

        return $this;
    }

    /**
     * @return bool
     */
    public function getWasClosed()
    {
        return $this->wasClosed;
    }

    /**
     * @param float $realizedGainOrLoss
     * @return $this
     */
    public function setRealizedGainOrLoss($realizedGainOrLoss)
    {
        $this->realizedGainOrLoss = $realizedGainOrLoss;

        return $this;
    }

    /**
     * @return float
     */
    public function getRealizedGainOrLoss()
    {
        return $this->realizedGainOrLoss;
    }

    /**
     * @return bool
     */
    public function isLoss()
    {
        return ($this->realizedGainOrLoss < 0);
    }

    /**
     * @return bool
     */
    public function isShortTerm()
    {
        $today = new \DateTime();

        if ($this->isInitial()) {
            $difference = $today->diff($this->getDate());
        } else {
            $difference = $today->diff($this->initial->getDate());
        }

        return ($difference->days <= 365);
    }

    /**
     * @param float $costBasis
     * @return $this
     */
    public function setCostBasis($costBasis)
    {
        $this->costBasis = $costBasis;

        return $this;
    }

    /**
     * @return float
     */
    public function getCostBasis()
    {
        return $this->costBasis;
    }



//    /**
//     * Get number of days since last purchase
//     *
//     * @param  [type] $symbol [description]
//     * @param  [type] $today  [description]
//     * @return [type]         [description]
//     */
//    public function interval($symbol, $today = null)
//    {
//        if (is_null($today)) {
//            $today = time();
//        }
//        $datetime1 = strtotime($today);
//        $datetime2 = strtotime($this->getAge());
//
//        $secs = $datetime1 - $datetime2;
//        $days = $secs / 86400;
//
//        return $days;
//    }


    /**
     * Get number of days since last purchase
     *
     * @param \DateTime $date
     * @return int
     */
    public function interval(\DateTime $date = null)
    {
        if (null === $date) {
            $date = new \DateTime();
        }

        $interval = $date->diff($this->date);

        return (int) $interval->format('%a');
    }

    /**
     * @param int $initialLotId
     * @return $this
     */
    protected function setInitialLotId($initialLotId)
    {
        $this->initialLotId = $initialLotId;

        return $this;
    }

    /**
     * @return int
     */
    public function getInitialLotId()
    {
        return $this->initialLotId;
    }

    /**
     * @param int $positionId
     * @return $this
     */
    public function setPositionId($positionId)
    {
        $this->positionId = $positionId;

        return $this;
    }

    /**
     * @return int
     */
    public function getPositionId()
    {
        return $this->positionId;
    }

    /**
     * @param bool $isMuni
     * @return $this
     */
    public function setIsMuni($isMuni)
    {
        $this->isMuni = (bool) $isMuni;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsMuni()
    {
        return $this->isMuni;
    }

    protected function getRelations()
    {
        return array(
            'initial' => 'Model\WealthbotRebalancer\Lot'
        );
    }

    public function loadFromArray(array $data = array())
    {
        foreach ($data as $key => $value) {
            if ($key === 'realizedGain' || $key === 'realized_gain') {
                $gainOrLossKey = 'realized_gain_or_loss';
                $this->$gainOrLossKey = $value;
                unset($data[$key]);
            } elseif ($key === 'date' && !($value instanceof \DateTime)) {
                $this->$key = new \DateTime($value);
                unset($data[$key]);
            }
        }

        parent::loadFromArray($data);
    }


}