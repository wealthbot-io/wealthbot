<?php

namespace Wealthbot\RiaBundle\Tests\Controller;

use Wealthbot\AdminBundle\Entity\BillingSpec;
use Wealthbot\UserBundle\Entity\User;
use Wealthbot\UserBundle\TestSuit\ExtendedWebTestCase;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Client;

class BillingSpecControllerTest extends ExtendedWebTestCase
{
    /** @var Router */
    protected $router;

    public function setUp()
    {
        parent::setUp();

        $this->router = $this->container->get('router');
    }

    /**
     * Test list of billing specs
     */
    public function testIndexAction()
    {
        $this->authenticateUser('ria', array('ROLE_RIA', 'ROLE_RIA_BASE', 'ROLE_ADMIN'));
        $this->client->request('GET', $this->router->generate('rx_ria_api_billing_specs_rest'));

        $this->assertEquals($this->client->getResponse()->getStatusCode(), 200);

        $json = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('name', $json[0]);
        $this->assertArrayHasKey('master', $json[0]);
        $this->assertArrayHasKey('type', $json[0]);
    }


    public function testCreateAction()
    {
        $this->authenticateUser('ria', array('ROLE_RIA', 'ROLE_RIA_BASE', 'ROLE_ADMIN'));
        $crawler = $this->client->request('POST', $this->router->generate('rx_ria_api_billing_specs_rest'), array(
            'billing_spec' => array(
                'minimalFee' => '100',
                'name'       => 'Test name',
                'type'       => BillingSpec::TYPE_TIER,
                'fees'       => array(
                    0 => array(
                        'fee_without_retirement' => 23
                    )
                )
            )
        ));

        $billingSpec = $this->getLastBilling();

        try {
            echo $crawler->filter('h1')->text();

        } catch (\Exception $e) {

        }

        $this->assertEquals('Test name', $billingSpec->getName());

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateAction()
    {
        $billingSpec = $this->getLastBilling();
        $this->assertNotEquals('Test name', $billingSpec->getName());

        $this->authenticateUser('ria', array('ROLE_RIA', 'ROLE_RIA_BASE', 'ROLE_ADMIN'));
        $crawler = $this->client->request('POST', $this->router->generate('rx_ria_api_billing_specs_rest'), array(
            'billing_spec' => array(
                'minimalFee' => '100',
                'name'       => 'Test name',
                'type'       => BillingSpec::TYPE_TIER,
                'fees'       => array(
                    0 => array(
                        'fee_without_retirement' => 23
                    )
                )
            )
        ));

        $billingSpec = $this->getLastBilling();

        $this->assertEquals('Test name', $billingSpec->getName());

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    protected function getLastBilling()
    {
        return $this->em->getRepository('WealthbotAdminBundle:BillingSpec')->createQueryBuilder('f')
            ->orderBy('f.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }

}