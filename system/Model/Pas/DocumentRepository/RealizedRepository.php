<?php

namespace Model\Pas\DocumentRepository;

class RealizedRepository extends BaseRepository
{
    const STATUS_NOT_MATCH = 1;
    const STATUS_MATCH = 2;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->collection = static::DOCUMENT_REALIZED;
        parent::__construct();
    }
}