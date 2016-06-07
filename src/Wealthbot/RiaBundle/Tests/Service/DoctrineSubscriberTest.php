<?php

namespace Wealthbot\RiaBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Wealthbot\AdminBundle\Entity\BillingSpec;
use Wealthbot\AdminBundle\Entity\Fee;
use Wealthbot\UserBundle\Entity\User;
use Wealthbot\UserBundle\TestSuit\ExtendedWebTestCase;

class DoctrineSubscriberTest extends ExtendedWebTestCase
{
    /** @var Router */
    protected $router;

    /** @var  User */
    protected $riaUser;

    public function setUp()
    {
        parent::setUp();
        $this->router = $this->container->get('router');
    }

    protected function createBillingSpec($name)
    {
        $fee = new Fee();
        $fee->setFeeWithoutRetirement(500);

        $spec = new BillingSpec();
        $spec->setMaster(true);
        $spec->setMinimalFee(300);
        $spec->setName($name);
        $spec->setOwner($this->riaUser);
        $spec->setType(BillingSpec::TYPE_FLAT);
        $spec->setFees([$fee]);
        $fee->setBillingSpec($spec);

        $this->em->persist($spec);
        $this->em->persist($fee);

        return $spec;
    }

    protected function createAdminBillingSpec($name)
    {
        $fee = new Fee();
        $fee->setFeeWithoutRetirement(500);

        $spec = new BillingSpec();
        $spec->setMaster(true);
        $spec->setMinimalFee(300);
        $spec->setName($name);
        $spec->setOwner(null);
        $spec->setType(BillingSpec::TYPE_FLAT);
        $spec->setFees([$fee]);
        $fee->setBillingSpec($spec);

        $this->em->persist($spec);
        $this->em->persist($fee);

        return $spec;
    }

    public function testAddMaster()
    {
        $this->riaUser = $this->authenticateUser('ria', ['ROLE_RIA', 'ROLE_RIA_BASE', 'ROLE_ADMIN']);

        $specs = $this->em->getRepository('WealthbotAdminBundle:BillingSpec')->findBy(['owner' => $this->riaUser]);
        $message = "\n ALL SPECS BEFORE:\n";
        foreach ($specs as $spec) {
            $message .= 'Spec, id:'.$spec->getId().', name:'.$spec->getName()
                .', master: '.($spec->getMaster() ? 'true' : 'false')."\n";
        }

        $spec1 = $this->em->getRepository('WealthbotAdminBundle:BillingSpec')->findOneBy(['name' => 'Flat spec']);
        $spec2 = $this->em->getRepository('WealthbotAdminBundle:BillingSpec')->findOneBy(['name' => 'Tier spec']);

        $spec3 = $this->createBillingSpec('TestSpec3');

        $this->em->flush();

        $specs = $this->em->getRepository('WealthbotAdminBundle:BillingSpec')->findBy(['owner' => $this->riaUser]);
        $message .= "\n ALL SPECS AFTER:\n";
        foreach ($specs as $spec) {
            $message .= 'Spec, id:'.$spec->getId().', name:'.$spec->getName()
                .', master: '.($spec->getMaster() ? 'true' : 'false')."\n";
        }

        $this->assertNotNull($spec3, 'was not created TestSpec3');
        $this->assertTrue($spec3->getMaster(), 'TestSpec3 must be a master. '.$message);

        $this->assertNotNull($spec2, 'was not loaded Tier spec');
        $this->assertFalse($spec2->getMaster(), 'Tier spec must NOT be a master. '.$message);

        $this->assertNotNull($spec1, 'was not loaded Flat spec');
        $this->assertFalse($spec1->getMaster(), 'Flat spec must NOT be a master. '.$message);
    }

    public function testSetMaster()
    {
        $this->riaUser = $this->authenticateUser('ria', ['ROLE_RIA', 'ROLE_RIA_BASE', 'ROLE_ADMIN']);

        $spec1 = $this->em->getRepository('WealthbotAdminBundle:BillingSpec')->findOneBy(['name' => 'Flat spec']);
        $spec2 = $this->em->getRepository('WealthbotAdminBundle:BillingSpec')->findOneBy(['name' => 'Tier spec']);

        $spec2->setMaster(true);

        $this->em->flush();

        $this->assertNotNull($spec2, 'was not loaded Tier spec');
        $this->assertTrue($spec2->getMaster(), 'spec2 must be a master');

        $this->assertNotNull($spec1, 'was not loaded Flat spec');
        $this->assertFalse($spec1->getMaster(), 'spec1 must NOT be a master');
    }

    public function testNoMaster()
    {
        $this->riaUser = $this->authenticateUser('ria', ['ROLE_RIA', 'ROLE_RIA_BASE', 'ROLE_ADMIN']);

        $spec1 = $this->em->getRepository('WealthbotAdminBundle:BillingSpec')->findOneBy(['name' => 'Flat spec']);
        $spec2 = $this->em->getRepository('WealthbotAdminBundle:BillingSpec')->findOneBy(['name' => 'Tier spec']);

        $spec1->setMaster(false);

        $this->em->flush();

        $this->assertNotNull($spec1, 'was not loaded Flat spec');
        $this->assertTrue($spec1->getMaster(), 'spec1 must be a master');

        $this->assertNotNull($spec2, 'was not loaded Tier spec');
        $this->assertFalse($spec2->getMaster(), 'spec2 must NOT be a master');
    }

    public function testDeleteMaster()
    {
        $this->riaUser = $this->authenticateUser('ria', ['ROLE_RIA', 'ROLE_RIA_BASE', 'ROLE_ADMIN']);

        $spec1 = $this->em->getRepository('WealthbotAdminBundle:BillingSpec')->findOneBy(['name' => 'Flat spec']);
        $spec2 = $this->em->getRepository('WealthbotAdminBundle:BillingSpec')->findOneBy(['name' => 'Tier spec']);

        $this->em->remove($spec1);
        $this->em->flush();

        $this->assertNotNull($spec2, 'was not loaded Tier spec');
        $this->assertTrue($spec2->getMaster(), 'spec2 must be a master');
    }

    public function testAddThreeMasters()
    {
        $this->riaUser = $this->authenticateUser('ria', ['ROLE_RIA', 'ROLE_RIA_BASE', 'ROLE_ADMIN']);

        $spec1 = $this->createBillingSpec('TestSpec1');
        $spec2 = $this->createBillingSpec('TestSpec2');
        $spec3 = $this->createBillingSpec('TestSpec3');
        $this->em->flush();

        $spec3->setMaster(true);
        $this->em->flush();

        $this->assertNotNull($spec3, 'was not created TestSpec3');
        $this->assertTrue($spec3->getMaster(), 'TestSpec3 must be a master');

        $this->assertNotNull($spec2, 'was not loaded Tier spec');
        $this->assertFalse($spec2->getMaster(), 'Tier spec must NOT be a master');

        $this->assertNotNull($spec1, 'was not loaded Flat spec');
        $this->assertFalse($spec1->getMaster(), 'Flat spec must NOT be a master');
    }

    public function testAdminFee()
    {
        $this->riaUser = $this->authenticateUser('ria', ['ROLE_RIA', 'ROLE_RIA_BASE', 'ROLE_ADMIN']);

        $spec1 = $this->createBillingSpec('TestSpec1');

        $specA = $this->createAdminBillingSpec('TestAdminSpecA');
        $specB = $this->createAdminBillingSpec('TestAdminSpecB');

        $this->em->flush();

        $specA->setMaster(true);
        $this->em->flush();

        $this->assertNotNull($specB, 'was not created TestAdminSpecB');
        $this->assertFalse($specB->getMaster(), 'TestAdminSpecB must NOT be a master');

        $this->assertNotNull($specA, 'was not loaded Tier spec');
        $this->assertTrue($specA->getMaster(), 'TestAdminSpecA must be a master');

        $this->assertNotNull($spec1, 'was not loaded TestSpec1');
        $this->assertTrue($spec1->getMaster(), 'TestSpec1 must be a master');
    }
}
