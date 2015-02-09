<?php

namespace Model\WealthbotRebalancer\Repository;

require_once(__DIR__ . '/../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

class PositionRepository extends BaseRepository
{
    protected function getOptions()
    {
        return array(
            'table_name' => self::TABLE_POSITION,
            'model_name' => 'Model\WealthbotRebalancer\Position'
        );
    }

} 