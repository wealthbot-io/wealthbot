<?php

namespace System\Model\WealthbotRebalancer\Repository;




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