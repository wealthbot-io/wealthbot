<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\Security;
use App\Entity\Position;
use App\Entity\SystemAccount;

/**
 * PositionRepository.
 *
 * Repository for trade positions (by clientSystemAccount, Security, date)
 */
class PositionRepository extends EntityRepository
{
    public function getFirstPosition(SystemAccount $systemAccount, Security $security)
    {
        return $this->createQueryBuilder('p')
            ->where('p.clientSystemAccount = :account')
            ->andWhere('p.security = :security')
            ->setParameter('account', $systemAccount)
            ->setParameter('security', $security)
            ->orderBy('p.date', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get open positions by the systemAccounts array.
     *
     * @param SystemAccount[] $accounts
     *
     * @return Position[]
     */
    public function getOpenPositions($accounts)
    {
        $qb = $this->createQueryBuilder('p');

        /** @var Position[] $positions */
        $positions = $this->createQueryBuilder('p')
            ->add('where', $qb->expr()->in('p.clientSystemAccount', ':accounts'))
            ->setParameter('accounts', $accounts)
            ->groupBy('p.security, p.clientSystemAccount')
            ->getQuery()
            ->getResult();

        $lastPositions = [];
        foreach ($positions as $position) {
            $lastPosition = $this->createQueryBuilder('p')
                ->where('p.security = :security')
                ->andWhere('p.clientSystemAccount = :account')
                ->setParameter('account', $position->getClientSystemAccount())
                ->setParameter('security', $position->getSecurity())
                ->orderBy('p.date', 'DESC')
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();

            if ($lastPosition
                    && Position::POSITION_STATUS_INITIAL === $lastPosition->getStatus()
                    || Position::POSITION_STATUS_IS_OPEN === $lastPosition->getStatus()) {
                $lastPositions[] = $lastPosition;
            }
        }

        return $lastPositions;
    }

    public function getOneByDay(Security $security, SystemAccount $account, \DateTime $date)
    {
        $dateFrom = new \DateTime();
        $dateFrom->setTimestamp($date->getTimestamp());
        $dateTo = new \DateTime();
        $dateTo->setTimestamp($date->getTimestamp());
        $dateFrom->setTime(0, 0, 0);
        $dateTo->setTime(0, 0, 0);
        $dateTo->modify('+1 day');

        return $this->createQueryBuilder('p')
            ->where('p.security = :s')
            ->andWhere('p.clientSystemAccount = :a')
            ->andWhere('p.date >= :dateFrom')
            ->andWhere('p.date < :dateTo')
            ->setParameters([
                    's' => $security,
                    'a' => $account,
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,
                ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getAllocation($user, $positions)
    {
        $qb = $this->createQueryBuilder('positions');
        $qb
            ->select('subclass.name AS label, security_prices.price, SUM(positions.amount) AS amount, securities_assignments.subclass_id')

            ->join('positions.clientSystemAccount', 'system_accounts')
            ->join('positions.security', 'securities')
            ->join('securities.securityPrices', 'security_prices')

            ->join('securities.securityAssignments', 'securities_assignments')
            ->join('securities_assignments.subclass', 'subclass')
            ->join('securities_assignments.ceModelEntity', 'ce_model_entities')
            ->join('ce_model_entities.model', 'ce_model')
            ->join('ce_model.clientPortfolio', 'client_portfolio')

            ->where($qb->expr()->in('positions.id', ':ids'))
            ->setParameter('ids', $positions)

            ->andWhere('client_portfolio.is_active=1')
            ->andWhere('security_prices.is_current = 1')
            ->groupBy('securities_assignments.subclass_id')
        ;

        return $qb->getQuery()->execute();
    }

    public function getSubclasses($user, $account_id = null)
    {
        $max_date = $this->createQueryBuilder('positions1')->select('MAX(positions1.date) AS max_date');
        $qb = $this->createQueryBuilder('positions')
            ->select('subclass.name AS label, security_prices.price, SUM(positions.amount) AS amount, securities_assignments.subclass_id')

            ->join('positions.clientSystemAccount', 'system_accounts')
            ->join('positions.security', 'securities')
            ->join('securities.securityPrices', 'security_prices')

            ->join('securities.securityAssignments', 'securities_assignments')
            ->join('securities_assignments.subclass', 'subclass')
            ->join('securities_assignments.ceModelEntity', 'ce_model_entities')
            ->join('ce_model_entities.model', 'ce_model')
            ->join('ce_model.clientPortfolio', 'client_portfolio')

            ->where($max_date->expr()->in('positions.date', $max_date->getDQL()))
            ->andWhere('positions.status = :status_open OR positions.status = :status_initial')
            ->setParameter('status_open', Position::POSITION_STATUS_IS_OPEN)
            ->setParameter('status_initial', Position::POSITION_STATUS_INITIAL)
            ->andWhere('system_accounts.client = :client')
            ->setParameter('client', $user)
            ->andWhere('client_portfolio.is_active=1')
            ->andWhere('security_prices.is_current = 1')
            ->groupBy('securities_assignments.subclass_id')
        ;

        if ($account_id) {
            $qb
                ->andWhere('system_accounts.client_account_id = :account_id')
                ->setParameter('account_id', $account_id);
        }

        $subclasses = $qb->getQuery()->execute();

        return $subclasses;
    }

    public function getGainLossYears($accounts)
    {
        $qb = $this->createQueryBuilder('position');

        $qb->select('DISTINCT YEAR(position.date) AS year')

            ->where($qb->expr()->in('position.clientSystemAccount', ':accounts'))
            ->setParameter('accounts', $accounts)

            ->andWhere('position.status = :status_closed')
            ->setParameter('status_closed', Position::POSITION_STATUS_IS_CLOSE)

            ->orderBy('year', 'DESC')
        ;

        return $qb->getQuery()->execute();
    }

    public function getModelNameByAccount($account)
    {
        $qb = $this->createQueryBuilder('positions')
            ->select('positions, ceModels.name')
            ->join('positions.security', 'securities')
            ->join('securities.securityAssignments', 'securityAssignments')
            ->join('securityAssignments.ceModelEntity', 'ceModelEntities')
            ->join('ceModelEntities.model', 'ceModels')
            ->join('positions.clientSystemAccount', 'systemAccounts')

            ->where('systemAccounts = :account')
            ->setParameter('account', $account)

            ->groupBy('ceModels.id')
        ;
        $model = $qb->getQuery()->getOneOrNullResult();

        return $model['name'];
    }
}
