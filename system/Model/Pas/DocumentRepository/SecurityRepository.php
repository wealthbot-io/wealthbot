<?php

namespace Model\Pas\DocumentRepository;

class SecurityRepository extends BaseRepository
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->collection = static::DOCUMENT_SECURITY;
        parent::__construct();
    }

    /**
     * Get all security array from mongo
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getAllByDate($startDate, $endDate)
    {
        $cursor = $this
            ->mongo
            ->whereGte('import_date', $startDate)
            ->whereLte('import_date', $endDate)
            ->get($this->collection)
        ;

        return $cursor;
    }
}