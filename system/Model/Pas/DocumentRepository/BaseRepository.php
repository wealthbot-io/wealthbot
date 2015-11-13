<?php

namespace Model\Pas\DocumentRepository;

use Lib\Ioc;
use Database\Builder\MongoBuilder as MB;

class BaseRepository
{
    const DOCUMENT_POSITION = 'positions';
    const DOCUMENT_TRANSACTION = 'transactions';
    const DOCUMENT_SECURITY = 'securities';
    const DOCUMENT_REALIZED = 'realized';
    const DOCUMENT_PRICE = 'prices';
    const DOCUMENT_UNREALIZED = 'unrealized';

    const STATUS_NOT_POSTED = 1;
    const STATUS_POSTED     = 2;
    const STATUS_CANCELLED  = 3;

    /**
     * @var object
     */
    protected $mongoDB;

    /**
     * @var sting
     */
    protected $collection;

    public function __construct()
    {
        // Get current connection instance
        $connection = Ioc::resolve('connection');
       	$this->mongoDB = $connection->getMongoDB();
        $this->mongo = new MB($this->mongoDB);
    }

    /**
     * @param string $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * Change loading status by id
     *
     * @param $id
     * @param $status
     */
    public function changeStatusById($id, $status)
    {
        $this
            ->mongo
            ->where(array('_id' => $id))
            ->set(array('status' => $status))
            ->update($this->collection)
        ;
    }

    /**
     * Get unique account array from mongo
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getUniqueAccount($startDate, $endDate)
    {
        /*
         $cursor = $this
            ->mongo
            //->where('status', static::STATUS_NOT_POSTED)
            ->whereGte('import_date', $startDate)
            ->whereLte('import_date', $endDate)
            ->orderBy(array('account_number' => 'asc'))
            ->get($this->collection)
        ;
        */

        $collectionName = $this->collection;

        $cursor = $this->mongoDB->{$collectionName}->find(
            array(
                'import_date' => array('$gte' => $startDate, '$lte' => $endDate),
                'status' => static::STATUS_NOT_POSTED
            )
        );

        $accounts = array();
        foreach ($cursor as $doc) {
            if (isset($doc['account_number'])) {
                $accounts[] = $doc['account_number'];
            }
        }

        return array_unique($accounts);
    }

    /**
     * Get all transaction array from mongo
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $accountNumber
     * @return array
     */
    public function getAllByAccountNumber($accountNumber, $startDate, $endDate)
    {
        $cursor = $this
            ->mongo
            ->whereGte('import_date', $startDate)
            ->whereLte('import_date', $endDate)
            ->where('account_number', $accountNumber)
            ->get($this->collection)
        ;

        return $cursor;
    }

    /**
     * Change loading status by accountt number
     *
     * @param $accountNumber
     * @param $startDate
     * @param $endDate
     * @param $status
     */
    public function changeStatusByAccountNumber($accountNumber, $startDate, $endDate, $status)
    {
        $this
            ->mongo
            ->where('account_number', $accountNumber)
            ->whereGte('import_date', $startDate)
            ->whereLte('import_date', $endDate)
            ->set(array('status' => $status))
            ->updateAll($this->collection)
        ;
    }

    /**
     * Delete by import date
     *
     * @param $date
     */
    public function deleteByImportDate($date)
    {
        $this
            ->mongo
            ->where('import_date', $date)
            ->deleteAll($this->collection)
        ;
    }
}