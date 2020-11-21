<?php

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Distribution;

class LoadClientScheduledDistributionData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $distribution = new Distribution();
        $distribution->setType(Distribution::TYPE_SCHEDULED);
        $distribution->setSystemClientAccount($this->getReference('system-account'));
        $distribution->setBankInformation($this->getReference('bank-info'));
        $distribution->setResidenceState($this->getReference('state-Florida'));
        $distribution->setTransferMethod(Distribution::TRANSFER_METHOD_BANK_TRANSFER);
        $date = new \DateTime('2014-01-01');
        $distribution->setTransferDate($date);
        $distribution->setAmount(500);
        $distribution->setFrequency(Distribution::FREQUENCY_MONTHLY);
        $distribution->setDistributionMethod(1);
        $distribution->setFederalWithholding(Distribution::FEDERAL_WITHHOLDING_TAXES);
        $distribution->setStateWithholding(Distribution::STATE_WITHHOLDING_TAXES);
        $distribution->setFederalWithholdPercent(0);
        $distribution->setFederalWithholdMoney(0);
        $distribution->setStateWithholdPercent(0);
        $distribution->setStateWithholdMoney(0);

        $manager->persist($distribution);
        $manager->flush();
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 9;
    }
}
