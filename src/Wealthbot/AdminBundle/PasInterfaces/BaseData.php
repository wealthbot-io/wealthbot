<?php

namespace Wealthbot\AdminBundle\PasInterfaces;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;

class BaseData implements DataInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $mongoManager;

    /**
     * @var array
     */
    protected $systemAccountHash = array();

    /**
     * @var array
     */
    protected $securityHash = array();

    /**
     * @param EntityManager $em
     * @param DocumentManager $mongoManager
     * @param $paginator
     * @param $perPage
     */
    public function __construct(EntityManager $em, DocumentManager $mongoManager, $paginator, $perPage)
    {
        $this->em = $em;
        $this->perPage = $perPage;
        $this->paginator = $paginator;
        $this->mongoManager = $mongoManager;
    }

    /**
     * @param \DateTime $date
     * @param int $page
     * @return Array|void
     */
    public function load(\DateTime $date, $page = 0) {}

    /**
     * Method must return FileType, for example "POS"
     */
    public function getFileType() {}

    /**
     * @param string $accountNumber
     * @return null|SystemAccount
     */
    protected function getSystemAccountByAccountNumber($accountNumber)
    {
        if (empty($accountNumber)) {
            return null;
        }

        if (isset($this->systemAccountHash[$accountNumber])) {
            return $this->systemAccountHash[$accountNumber];
        }

        return $this->systemAccountHash[$accountNumber] = $this
            ->em
            ->getRepository('WealthbotClientBundle:SystemAccount')
            ->findOneBy(array('account_number' => $accountNumber))
        ;
    }

    /**
     * @param $symbol
     * @return null|Security
     */
    protected function getSecurityBySymbol($symbol)
    {
        if (empty($symbol)) {
            return null;
        }

        if (isset($this->securityHash[$symbol])) {
            return $this->securityHash[$symbol];
        }

        return $this->securityHash[$symbol] = $this
            ->em
            ->getRepository('WealthbotAdminBundle:Security')
            ->findOneBy(array('symbol' => $symbol))
        ;
    }
}