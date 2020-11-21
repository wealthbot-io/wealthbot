<?php

namespace App\Manager;

use Doctrine\ORM\EntityManager;
use App\Entity\BillingSpec;
use App\Entity\User;

class BillingSpecManager
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @TODO Add for RIA in name?!
     * @TODO Restrict by requirements!!! Master only
     *
     * @param User $ria
     */
    public function getSpecs(User $ria)
    {
        return $ria->getBillingSpecs();
    }

    /**
     * @param BillingSpec $billingSpec
     */
    public function remove(BillingSpec $billingSpec)
    {
        $this->em->remove($billingSpec);
        $this->em->flush();
    }

    /**
     * Create default Admin Spec for RIA.
     *
     * @param User $ria
     *
     * @return BillingSpec
     */
    public function initDefaultAdminSpec(User $ria)
    {
        $billingSpec = new BillingSpec();
        $billingSpec->setType(BillingSpec::TYPE_TIER);
        $billingSpec->setName('Admin spec for '.$ria->getRiaCompanyInformation()->getName());
        $billingSpec->addAppointedUser($ria);
        $billingSpec->setMinimalFee(0.0);

        return $billingSpec;
    }
}
