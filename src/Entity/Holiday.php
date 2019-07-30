<?php

namespace App\Entity;

/**
 * Class Holiday
 * @package App\Entity
 */
class Holiday
{
    const HOLIDAY_TYPE_WEEKEND = 1;
    const HOLIDAY_TYPE_MARKET_HOLIDAY = 2;

    /**
     * @var int
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var int
     */
    private $type;

    public function __construct()
    {
        $this->type = self::HOLIDAY_TYPE_WEEKEND;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
}
