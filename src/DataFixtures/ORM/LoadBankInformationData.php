<?php

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\BankInformation;

class LoadBankInformationData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $client = $this->getReference('user-client');

        $bankInfo = new BankInformation();
        $bankInfo->setClient($client);
        $bankInfo->setAccountOwnerFirstName('Patty');
        $bankInfo->setAccountOwnerMiddleName('Ann');
        $bankInfo->setAccountOwnerLastName('Killington');
        $bankInfo->setJointAccountOwnerFirstName('Bob');
        $bankInfo->setJointAccountOwnerMiddleName('The Destroyer');
        $bankInfo->setJointAccountOwnerLastName('Killington');
        $bankInfo->setName('First bank of NY');
        $bankInfo->setAccountTitle('Killington Bank Account');
        $bankInfo->setPhoneNumber('954-555-5555');
        $bankInfo->setRoutingNumber('01210000');
        $bankInfo->setAccountNumber('66666666');
        $bankInfo->setAccountType(BankInformation::ACCOUNT_TYPE_CHECK);
        $bankInfo->setPdfCopy('true');
        $date = new \DateTime('2013-01-08');
        $bankInfo->setUpdated($date);
        $this->addReference('bank-info', $bankInfo);
        $manager->persist($bankInfo);
        $manager->flush();
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 8;
    }
}
