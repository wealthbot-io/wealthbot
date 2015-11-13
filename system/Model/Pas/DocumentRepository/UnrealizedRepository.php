<?php

namespace Model\Pas\DocumentRepository;

class UnrealizedRepository extends BaseRepository
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->collection = static::DOCUMENT_UNREALIZED;
        parent::__construct();
    }

    /**
     * Update by id
     *
     * @param $id
     * @param $params
     */
    public function update($id, $params)
    {
        $this
            ->mongo
            ->where(array('_id' => $id))
            ->set($params)
            ->update($this->collection)
        ;
    }
}