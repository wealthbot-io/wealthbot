<?php

namespace Model\Pas\DocumentRepository;

class TransactionRepository extends BaseRepository
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->collection = static::DOCUMENT_TRANSACTION;
        parent::__construct();
    }
}