<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\Security;
use App\Entity\Lot;
use App\Entity\Position;
use App\Entity\SystemAccount;
use App\Entity\User;

/**
 * LotRepository.
 *
 * Repository for trade Lots (by clientSystemAccount, Security, date).
 * Aggregated Lots by security and account on date is making Position on date.
 */
class LotRepository extends EntityRepository
{
    //======= For fixtures =======
    //Returns all opened lots on date.
    public function getOpenedOnDate(\DateTime $date)
    {
        return $this->createQueryBuilder('l')
            ->where('l.date <= :date')
            ->andWhere('l.wasClosed = false')
            ->andWhere('l.status = :status')
            ->setParameter('status', Lot::LOT_INITIAL)
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }

    //======= END For fixtures =======

    public function getRealizedLots($year, $accounts = [], $sort = 'securities.name', $direction = 'DESC')
    {
        $qb = $this->createQueryBuilder('lots');
        $qb
            ->join('lots.position', 'positions')
            ->join('lots.initial', 'initial_lots')
            ->leftJoin('positions.security', 'securities')

            ->where($qb->expr()->in('positions.clientSystemAccount', ':accounts'))
            ->setParameter('accounts', $accounts)

            ->andWhere('lots.status = :status')
            ->setParameter('status', Lot::LOT_CLOSED)

            ->andWhere('positions.date BETWEEN :date1 AND :date2')
            ->setParameter('date1', "$year-01-01")
            ->setParameter('date2', "$year-12-31")

            ->orderBy($sort, $direction)
        ;

        return $qb->getQuery()->execute();
    }

    public function getInitialLot($position)
    {
        $lot = $this->createQueryBuilder('lots')
            ->where('lots.position = :position')
            ->setParameter('position', $position)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult()
        ;

        if ($lot) {
            if (Lot::LOT_INITIAL === $lot->getStatus()) {
                return $lot;
            } else {
                return $this->createQueryBuilder('lots')

                    ->where('lots.id = :lot')
                    ->setParameter('lot', $lot->getInitial())

                    ->setMaxResults(1)
                    ->getQuery()->getOneOrNullResult()
                ;
            }
        }

        return;
    }

    public function getTradeRecon(\DateTime $dateFrom, \DateTime $dateTo, User $ria = null, $filteredLots = [], $clientName = '')
    {
        $qb = $this->createQueryBuilder('lots');

        $orX = $qb->expr()->orX()
            ->add('lots.status = :statusInitial')
            ->add('lots.status = :statusClosed');

        $qb
            ->join('lots.clientSystemAccount', 'systemAccount')
            ->join('systemAccount.client', 'client')

            ->where($orX)

            ->andWhere('lots.date >= :dateFrom')
            ->setParameter('dateFrom', $dateFrom)

            ->andWhere('lots.date <= :dateTo')
            ->setParameter('dateTo', $dateTo)

            ->setParameter('statusInitial', Lot::LOT_INITIAL)
            ->setParameter('statusClosed', Lot::LOT_CLOSED)
        ;

        if (!empty($filteredLots)) {
            $qb->andWhere($qb->expr()->notIn('lots.id', $filteredLots));
        }

        if ($ria) {
            $qb
                ->join('client.profile', 'profile')
                ->andWhere('profile.ria = :ria')
                ->setParameter(':ria', $ria)
            ;
        }

        if ($clientName) {
            $nameArray = preg_replace("~\W~", '', explode(' ', $clientName, 2));

            foreach ($nameArray as $key => $value) {
                $orX = $qb->expr()->orX();

                $orX->add($qb->expr()->like('profile.first_name', '?'.($key * 2 + 1)));
                $orX->add($qb->expr()->like('profile.last_name', '?'.($key * 2 + 2)));

                $qb->setParameter($key * 2 + 1, '%'.$value.'%');
                $qb->setParameter($key * 2 + 2, '%'.$value.'%');

                $qb->andWhere($orX);
            }
        }

        return $qb->getQuery()->execute();
    }

    public function isReconciled(\DateTime $date, SystemAccount $account = null)
    {
        $isReconciled = true;

        $fromDate = clone $date;
        $fromDate->setTime(0, 0, 0);
        $toDate = clone $date;
        $toDate->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('lots');

        $qb
            ->where('lots.date >= :fromDate')
            ->setParameter('fromDate', $fromDate)

            ->andWhere('lots.date <= :toDate')
            ->setParameter('toDate', $toDate)
        ;

        if ($account) {
            $qb
                ->andWhere('lots.clientSystemAccount = :account')
                ->setParameter('account', $account)
            ;
        }

        $lots = $qb->getQuery()->execute();

        foreach ($lots as $lot) {
            /* @param \App\Entity\Lot $lot */
            if ($lot->getWasRebalancerDiff()) {
                $isReconciled = false;
            }
        }

        return $isReconciled;
    }
}
