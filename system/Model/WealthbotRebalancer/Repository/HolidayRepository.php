<?php

namespace Model\WealthbotRebalancer\Repository;

require_once(__DIR__ . '/../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class HolidayRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_HOLIDAY,
            'model_name' => 'Model\WealthbotRebalancer\Holiday'
        );
    }

    public function getFromTo(\DateTime $dateFrom, \DateTime $dateTo)
    {
        $sql = "SELECT * FROM ".$this->table." WHERE date >= :from AND date < :to";
        $parameters = array('from' => $dateFrom->format('Y-m-d'), 'to'   => $dateTo->format('Y-m-d'));

        $result = $this->db->query($sql, $parameters);

        return $this->bindCollection($result);
    }

} 