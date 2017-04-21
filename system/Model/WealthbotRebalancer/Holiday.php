<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 25.02.14
 * Time: 13:10
 */

namespace Model\WealthbotRebalancer;

require_once(__DIR__ . '/../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class Holiday extends Base
{

    const TYPE_WEEKEND = 1;
    const TYPE_MARKET_HOLIDAY = 2;

    /** @var  \DateTime */
    private $date;

    /** @var integer */
    private $type;

    /**
     * @param \DateTime $date
     * @return $this
     */
    public function setDate($date)
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
     * @param int $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }


    public function loadFromArray(array $data = array())
    {
        foreach ($data as $key => $value) {
            if ($key === 'date' && !($value instanceof \DateTime)) {
                $this->$key = new \DateTime($value);
            } else {
                $this->$key = $value;
            }
        }
    }

}