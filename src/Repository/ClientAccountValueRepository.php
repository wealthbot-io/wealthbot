<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\QueryBuilder;
use App\Entity\ClientAccount;
use App\Entity\ClientAccountValue;
use App\Entity\ClientPortfolio;
use App\Entity\ClientPortfolioValue;
use App\Entity\SystemAccount;
use App\Entity\RiaCompanyInformation;
use App\Entity\User;

/**
 * ClientAccountValueRepository.
 *
 * ClientAccountValue - daily history of client accounts values (how much cash, in securities, billing cash...)
 */
class ClientAccountValueRepository extends EntityRepository
{
    public function findLatestValuesForClientPortfolio($clientPortfolioId)
    {
        $sql = 'SELECT cav.* FROM client_account_values cav
          LEFT JOIN client_portfolio cp ON cav.client_portfolio_id = cp.id
          WHERE cav.id = (SELECT id FROM client_account_values cav1
                            WHERE cav1.system_client_account_id = cav.system_client_account_id
                            ORDER BY cav1.date DESC
                            LIMIT 1)
          AND cav.client_portfolio_id = :clientPortfolioId';

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addEntityResult('App\Entity\ClientAccountValue', 'cav');
        $rsm->addFieldResult('cav', 'id', 'id');
        $rsm->addFieldResult('cav', 'client_portfolio_id', 'client_portfolio_id');
        $rsm->addFieldResult('cav', 'system_client_account_id', 'system_client_account_id');
        $rsm->addFieldResult('cav', 'source', 'source');
        $rsm->addFieldResult('cav', 'total_value', 'total_value');
        $rsm->addFieldResult('cav', 'total_in_securities', 'total_in_securities');
        $rsm->addFieldResult('cav', 'total_cash_in_account', 'total_cash_in_account');
        $rsm->addFieldResult('cav', 'total_cash_in_money_market', 'total_cash_in_money_market');
        $rsm->addFieldResult('cav', 'date', 'date');
        $rsm->addFieldResult('cav', 'sas_cash', 'sas_cash');
        $rsm->addFieldResult('cav', 'cash_buffer', 'cash_buffer');
        $rsm->addFieldResult('cav', 'billing_cash', 'billing_cash');
        $rsm->addFieldResult('cav', 'required_cash', 'required_cash');
        $rsm->addFieldResult('cav', 'model_deviation', 'model_deviation');
        $rsm->addFieldResult('cav', 'investable_cash', 'investable_cash');

        $query = $this->_em->createNativeQuery($sql, $rsm);
        $query->setParameter('clientPortfolioId', $clientPortfolioId);

        return $query->getResult();
    }

    public function findLatestValuesForClientsQuery(array $clientPortfolios)
    {
        $ids = [];
        /** @var ClientPortfolio $clientPortfolio */
        foreach ($clientPortfolios as $clientPortfolio) {
            $clientAccounts = $this->findLatestValuesForClientPortfolio($clientPortfolio->getId());
            foreach ($clientAccounts as $clientAccount) {
                if ($clientAccount) {
                    $ids[] = $clientAccount->getId();
                }
            }
        }

        $qb = $this->createQueryBuilder('cav')
            ->where('cav.id IN(:ids)')
            ->leftJoin('cav.clientPortfolio', 'cp')
            ->leftJoin('cp.client', 'c')
            ->leftJoin('c.groups', 'cg')
            ->leftJoin('c.profile', 'p')
            ->leftJoin('cp.portfolio', 'po')
            ->leftJoin('p.ria', 'r')
            ->leftJoin('r.riaCompanyInformation', 'rci')
            ->leftJoin('cav.systemClientAccount', 'sca')
            ->leftJoin('sca.clientAccount', 'ca')
            ->leftJoin('ca.groupType', 'gt')
            ->leftJoin('gt.type', 't')
            ->leftJoin('cav.rebalancerActions', 'ra')
            ->setParameter('ids', $ids);

        return $qb;
    }

    public function findLatestClientAccountValuesByPortfolioValue(ClientPortfolioValue $clientPortfolioValue)
    {
        $qb = $this->findLatestValuesForClientsQuery([$clientPortfolioValue->getClientPortfolio()]);

        return $qb->getQuery()->getResult();
    }

    public function findHistoryForAdminQuery($filters = [])
    {
        $qb = $this->createQueryBuilder('cav')
            ->leftJoin('cav.clientPortfolio', 'cp')
            ->leftJoin('cp.client', 'c')
            ->leftJoin('c.profile', 'p')
            ->leftJoin('c.groups', 'cg')
            ->leftJoin('cp.portfolio', 'po')
            ->leftJoin('p.ria', 'r')
            ->leftJoin('r.riaCompanyInformation', 'rci')
            ->leftJoin('cav.systemClientAccount', 'sca')
            ->leftJoin('sca.clientAccount', 'ca')
            ->leftJoin('ca.groupType', 'gt')
            ->leftJoin('gt.type', 't')
            ->where('rci.relationship_type = :relationsType')
            ->setParameter('relationsType', RiaCompanyInformation::RELATIONSHIP_TYPE_TAMP)
        ;

        $this->addHistoryFilterQueryPart($qb, $filters);

        return $qb;
    }

    public function findHistoryForRiaClientsQuery(User $ria, $filters = [])
    {
        $qb = $this->createQueryBuilder('cav')
            ->leftJoin('cav.clientPortfolio', 'cp')
            ->leftJoin('cp.client', 'c')
            ->leftJoin('c.profile', 'p')
            ->leftJoin('c.groups', 'cg')
            ->leftJoin('cp.portfolio', 'po')
            ->leftJoin('p.ria', 'r')
            ->leftJoin('r.riaCompanyInformation', 'rci')
            ->leftJoin('cav.systemClientAccount', 'sca')
            ->leftJoin('sca.clientAccount', 'ca')
            ->leftJoin('ca.groupType', 'gt')
            ->leftJoin('gt.type', 't')
            ->where('p.ria_user_id = :riaId')
            ->setParameter('riaId', $ria->getId())
        ;

        $this->addHistoryFilterQueryPart($qb, $filters);

        return $qb;
    }

    private function addHistoryFilterQueryPart(QueryBuilder $qb, $filters = [])
    {
        if (!empty($filters)) {
            if (isset($filters['client_id']) && $filters['client_id']) {
                $qb
                    ->andWhere('c.id = :clientId')
                    ->setParameter('clientId', $filters['client_id']);
            } elseif (isset($filters['client']) && $filters['client']) {
                $name = explode(',', $filters['client']);
                $lname = trim($name[0]);
                $fname = isset($name[1]) && $name[1] ? trim($name[1]) : null;

                if ($fname) {
                    $qb
                        ->andWhere('p.last_name = :lname AND p.first_name LIKE :fname')
                        ->setParameter('lname', $lname)
                        ->setParameter('fname', '%'.$fname.'%')
                    ;
                } else {
                    $qb
                        ->andWhere('p.last_name LIKE :searchStr OR p.first_name LIKE :searchStr')
                        ->setParameter('searchStr', '%'.$lname.'%')
                    ;
                }
            }

            if (isset($filters['date_from']) && $filters['date_from']) {
                $date = \DateTime::createFromFormat('m-d-Y', $filters['date_from']);

                $qb
                    ->andWhere('cav.date >= :dateFrom')
                    ->setParameter('dateFrom', $date->format('Y-m-d'));
            }

            if (isset($filters['date_to']) && $filters['date_to']) {
                $date = \DateTime::createFromFormat('m-d-Y', $filters['date_to']);

                $qb
                    ->andWhere('cav.date <= :dateTo')
                    ->setParameter('dateTo', $date->format('Y-m-d'));
            }

            if (isset($filters['set_id']) && $filters['set_id']) {
                //TODO: set condition
            }
        }
    }

    public function findLatestValuesForSystemClientAccountIds($systemClientAccountIds)
    {
        $qb = $this->findLatestValuesForSystemClientAccountIdsQuery($systemClientAccountIds);

        return $qb->getQuery()->getResult();
    }

    public function findLatestValuesForSystemClientAccountIdsQuery($systemClientAccountIds)
    {
        $clientAccountValueIds = [];

        foreach ($systemClientAccountIds as $systemClientAccountId) {
            /** @var ClientAccountValue $clientAccountValue */
            $clientAccountValue = $this->getLatestValueForSystemClientAccountId($systemClientAccountId);
            $clientAccountValueIds[] = $clientAccountValue->getId();
        }

        return $this->findValuesByIdsQuery($clientAccountValueIds);
    }

    public function findValuesByIdsQuery(array $ids)
    {
        $qb = $this->createQueryBuilder('cav')
            ->leftJoin('cav.clientPortfolio', 'cp')
            ->leftJoin('cp.client', 'c')
            ->leftJoin('c.profile', 'p')
            ->leftJoin('c.groups', 'cg')
            ->leftJoin('cp.portfolio', 'po')
            ->leftJoin('p.ria', 'r')
            ->leftJoin('r.riaCompanyInformation', 'rci')
            ->leftJoin('cav.systemClientAccount', 'sca')
            ->leftJoin('sca.clientAccount', 'ca')
            ->leftJoin('ca.groupType', 'gt')
            ->leftJoin('gt.type', 't')
            ->where('cav.id IN (:ids)')
            ->setParameter('ids', $ids);

        return $qb;
    }

    public function getLatestValueForSystemClientAccountId($systemClientAccountId)
    {
        $qb = $this->createQueryBuilder('cav')
            ->where('cav.system_client_account_id = :systemClientAccountId')
            ->setParameter('systemClientAccountId', $systemClientAccountId)
            ->orderBy('cav.date', 'DESC')
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getFreeCashBeforeDate(SystemAccount $sysAccount, \DateTime $date)
    {
        $x = $this->createQueryBuilder('v')
            ->select('(v.total_cash_in_account + v.total_cash_in_money_market) as value')
            ->where('v.date < :date')
            ->andWhere('v.systemClientAccount = :sysAccount')
            ->orderBy('v.date', 'DESC')
            ->setMaxResults(1)
            ->setParameter('date', $date)
            ->setParameter('sysAccount', $sysAccount)
            ->getQuery()
            ->getArrayResult();

        return $x ? $x[0]['value'] : 0;
    }

    public function getSumBeforeDate(SystemAccount $sysAccount, \DateTime $date)
    {
        $x = $this->createQueryBuilder('v')
            ->select('(v.total_in_securities + v.total_cash_in_account + v.total_cash_in_money_market) as value_total')
            ->where('v.date < :date')
            ->andWhere('v.systemClientAccount = :sysAccount')
            ->orderBy('v.date', 'DESC')
            ->setMaxResults(1)
            ->setParameter('date', $date)
            ->setParameter('sysAccount', $sysAccount)
            ->getQuery()
            ->getArrayResult();

        return $x ? $x[0]['value_total'] : 0;
    }

    /**
     * Returning first (by date) history element of this account.
     *
     * @param ClientAccount $account
     *
     * @return ClientAccountValue|null
     */
    public function getFirstActivityDate(ClientAccount $account)
    {
        $systemAccount = $account->getSystemAccount();
        if (!$systemAccount) {
            return;
        }
        $result = $this->createQueryBuilder('v')
            ->where('v.systemClientAccount = :sysAccount')
            ->orderBy('v.date', 'ASC')
            ->setParameter('sysAccount', $systemAccount)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        return count($result) ? $result[0] : null;
    }

    public function getAllActivityByAccount(ClientAccount $account, \DateTime $dateFrom, \DateTime $dateTo)
    {
        $systemAccount = $account->getSystemAccount();
        if (!$systemAccount) {
            return [];
        }

        return $this->createQueryBuilder('v')
            ->where('v.systemClientAccount = :sysAccount')
            ->andWhere('v.date >= :dateFrom')
            ->andWhere('v.date < :dateTo')
            ->orderBy('v.date', 'ASC')
            ->setParameter('sysAccount', $systemAccount)
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns array with:
     *  avg_value, count_values.
     *
     * @param ClientAccount $account
     * @param \DateTime     $dateFrom
     * @param \DateTime     $dateTo
     *
     * @return array
     */
    public function getAverageAccountValues(ClientAccount $account, \DateTime $dateFrom, \DateTime $dateTo)
    {
        $systemAccount = $account->getSystemAccount();

        if (!$systemAccount) {
            return [
                'avg_value' => 0,
                'count_values' => 0,
            ];
        }

        $r = $this->createQueryBuilder('v')
            ->select('AVG(v.total_value) as AV, COUNT(v.total_value) as CV')
            ->where('v.systemClientAccount = :sysAccount')
            ->andWhere('v.date >= :dateFrom')
            ->andWhere('v.date < :dateTo')
            ->orderBy('v.date', 'ASC')
            ->setParameter('sysAccount', $systemAccount)
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)
            ->getQuery()
            ->getArrayResult()
        ;

        if (count($r)) {
            return [
                'avg_value' => $r[0]['AV'],
                'count_values' => $r[0]['CV'],
            ];
        } else {
            return [
                'avg_value' => 0,
                'count_values' => 0,
            ];
        }
    }

    /**
     * @param ClientAccount $account
     * @param \DateTime     $dateFrom
     * @param \DateTime     $dateTo
     * @param string        $order
     *
     * @return ClientPortfolioValue|null
     */
    public function getExtreme(ClientAccount $account, \DateTime $dateFrom, \DateTime $dateTo, $order = 'ASC')
    {
        $qb = $this->createQueryBuilder('accountValues');

        return $qb
            ->leftJoin('accountValues.systemClientAccount', 'systemAccounts')
            ->leftJoin('accountValues.clientPortfolio', 'portfolio')

            ->where('systemAccounts.clientAccount = :account')
            ->andWhere('portfolio.is_active = true')
            ->andWhere($qb->expr()->between('accountValues.date', ':dateFrom', ':dateTo'))

            ->setParameter('account', $account)
            ->setParameter('dateFrom', $dateFrom)
            ->setParameter('dateTo', $dateTo)

            ->orderBy('accountValues.date', $order)

            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param ClientAccount $account
     * @param \DateTime     $dateFrom
     * @param \DateTime     $dateTo
     *
     * @return ClientPortfolioValue|null
     */
    public function getFirstByDate(ClientAccount $account, \DateTime $dateFrom, \DateTime $dateTo)
    {
        return $this->getExtreme($account, $dateFrom, $dateTo);
    }

    /**
     * @param ClientAccount $account
     * @param \DateTime     $dateFrom
     * @param \DateTime     $dateTo
     *
     * @return ClientPortfolioValue|null
     */
    public function getLastByDate(ClientAccount $account, \DateTime $dateFrom, \DateTime $dateTo)
    {
        return $this->getExtreme($account, $dateFrom, $dateTo, 'DESC');
    }

    public function getLastDayByPeriod(SystemAccount $systemAccount, \DateTime $dateFrom, \DateTime $dateTo)
    {
        return $this
            ->createQueryBuilder('v')
            ->where('v.systemClientAccount = :sysAccount')
            ->setParameter('sysAccount', $systemAccount)
            ->andWhere('v.date >= :dateFrom')
            ->setParameter('dateFrom', $dateFrom)
            ->andWhere('v.date <= :dateTo')
            ->setParameter('dateTo', $dateTo)
            ->orderBy('v.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;
    }
}
