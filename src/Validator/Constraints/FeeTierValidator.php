<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 25.09.12
 * Time: 12:13
 * To change this template use File | Settings | File Templates.
 */

namespace App\Validator\Constraints;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class FeeTierValidator extends ConstraintValidator
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param \App\Entity\Fee                       $fee
     * @param \Symfony\Component\Validator\Constraint $constraint
     */
    public function validate($fee, Constraint $constraint)
    {
        // Todo: Code is not working now, refactoring?

//        if ($fee->getTierBottom() >= $fee->getTierTop()) {
//            $this->context->addViolationAtSubPath('tier_top', 'This value must be greater than tier bottom.');
//        }
//
//        $repository = $this->em->getRepository('App\Entity\Fee');
//
//        if (!$fee->getId()) {
//            $query = $repository->createQueryBuilder('f')
//                ->where('f.billingSpec = :billingSpec')
//                ->orderBy('f.tier_top', 'DESC')
//                ->setMaxResults(1)
//                ->setparameter('billingSpec', $fee->getBillingSpec());
//
//            $exist = $query->getQuery()->getOneOrNullResult();
//
//            if ($exist) {
//                $minValue = $exist->getTierTop() + 0.01;
//            } else {
//                $minValue = 0;
//            }
//
//            if ($fee->getTierBottom() != $minValue) {
//                $this->context->addViolationAtSubPath('tier_bottom', 'This value must be equal to %minValue%.', array('%minValue%' => $minValue), null);
//            }
//        } else {
//            $query = $repository->createQueryBuilder('f')
//                ->where('f.tier_bottom <= :value AND f.tier_top >= :value AND f.id != :id')
//                ->setParameter('id', $fee->getId())
//                ->setMaxResults(1)
//                ->getQuery();
//
//            $tierBottomInRange = $query->setParameter('value', $fee->getTierBottom())->getOneOrNullResult();
//            if ($tierBottomInRange) {
//                $this->context->addViolationAtSubPath('tier_bottom', 'This value must be equal to %minValue%.', array('%minValue%' => $tierBottomInRange->getTierTop() + 0.01), null);
//            }
//
//            $tierTopInRange = $query->setParameter('value', $fee->getTierTop())->getOneOrNullResult();
//            if ($tierTopInRange) {
//                $this->context->addViolationAtSubPath('tier_top', 'This value must be equal to %minValue%.', array('%minValue%' => $tierTopInRange->getTierBottom() - 0.01), null);
//            }
//
//            $prevTop = $repository->createQueryBuilder('f')
//                ->where('f.tier_top < :value AND f.id != :id')
//                ->orderBy('f.tier_top', 'DESC')
//                ->setParameter('id', $fee->getId())
//                ->setParameter('value', $fee->getTierBottom())
//                ->setMaxResults(1)
//                ->getQuery()
//                ->getOneOrNullResult();
//            if ($prevTop && ($prevTop->getTierTop() + 0.01) != $fee->getTierBottom()) {
//                $this->context->addViolationAtSubPath('tier_bottom', 'This value must be equal to %minValue%.', array('%minValue%' => $prevTop->getTierTop() + 0.01), null);
//            }
//
//            $nextBottom = $repository->createQueryBuilder('f')
//                ->where('f.tier_bottom > :value AND f.id != :id')
//                ->orderBy('f.tier_top', 'ASC')
//                ->setParameter('id', $fee->getId())
//                ->setParameter('value', $fee->getTierTop())
//                ->setMaxResults(1)
//                ->getQuery()
//                ->getOneOrNullResult();
//            if ($nextBottom && ($nextBottom->getTierBottom() - 0.01) != $fee->getTierTop()) {
//                $this->context->addViolationAtSubPath('tier_top', 'This value must be equal to %minValue%.', array('%minValue%' => $nextBottom->getTierBottom() - 0.01), null);
//            }
//        }
    }
}
