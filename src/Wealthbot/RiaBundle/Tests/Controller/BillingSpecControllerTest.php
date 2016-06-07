<?php

namespace Wealthbot\RiaBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Wealthbot\AdminBundle\Entity\BillingSpec;
use Wealthbot\UserBundle\TestSuit\ExtendedWebTestCase;

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
     * Test list of billing specs.
     */
    public function testIndexAction()
    {
        $this->authenticateUser('ria', ['ROLE_RIA', 'ROLE_RIA_BASE', 'ROLE_ADMIN']);
        $this->client->request('GET', $this->router->generate('rx_ria_api_billing_specs_rest'));

        $this->assertSame($this->client->getResponse()->getStatusCode(), 200);

        $json = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('name', $json[0]);
        $this->assertArrayHasKey('master', $json[0]);
        $this->assertArrayHasKey('type', $json[0]);
    }

    public function testCreateAction()
    {
        $this->authenticateUser('ria', ['ROLE_RIA', 'ROLE_RIA_BASE', 'ROLE_ADMIN']);
        $crawler = $this->client->request('POST', $this->router->generate('rx_ria_api_billing_specs_rest_post'), [
            'billing_spec' => [
                'minimalFee' => '100',
                'name' => 'Test name',
                'type' => BillingSpec::TYPE_TIER,
            ],
        ]);

        $billingSpec = $this->getLastBilling();

        try {
            echo $crawler->filter('h1')->text();
        } catch (\Exception $e) {
        }

        $this->assertSame('Test name', $billingSpec->getName());

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUpdateAction()
    {
        $billingSpec = $this->getLastBilling();
        $this->assertNotSame('Test name', $billingSpec->getName());

        $this->authenticateUser('ria', ['ROLE_RIA', 'ROLE_RIA_BASE', 'ROLE_ADMIN']);
        $crawler = $this->client->request('PUT', $this->router->generate('rx_ria_api_billing_specs_rest_update_item', ['id' => 2]), [
            'billing_spec' => [
                'minimalFee' => '100',
                'name' => 'Test name',
                'type' => BillingSpec::TYPE_TIER,
                'fees' => [
                    0 => [
                        'fee_without_retirement' => 23,
                    ],
                ],
            ],
        ]);

        $billingSpec = $this->em->getRepository('WealthbotAdminBundle:BillingSpec')->find(2);

        $this->assertSame('Test name', $billingSpec->getName());

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
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
