<?php

namespace Model\Pas\DocumentRepository;

class PositionRepository extends BaseRepository
{
    public function __construct()
    {
        $this->collection = static::DOCUMENT_POSITION;
        parent::__construct();
    }
}