<?php

namespace Wealthbot\ClientBundle\Tests\Controller;


use Wealthbot\UserBundle\TestSuit\ExtendedWebTestCase;
use Symfony\Component\Routing\Router;

class ClientDashboardTest extends ExtendedWebTestCase
{
    /** @var Router */
    protected $router;

    public function setUp()
    {
        parent::setUp();

        $this->router = $this->container->get('router');
    }

    /**
     * Test holdings page.
     */
    public function testHoldingsAction()
    {
        $this->authenticateUser('bot@wealthbot.io', array('ROLE_CLIENT'));
        $crawler = $this->client->request('GET', $this->router->generate('wealthbot_client_holdings'));
        $content = $this->client->getResponse()->getContent();

        $this->assertEquals(
            $this->client->getResponse()->getStatusCode(),
            200,
            'Something wrong. Page returned not 200 status. H1: ' . ($crawler->filter('h1')->first()->count() ?
                $crawler->filter('h1')->first()->text() : '')
        );

        $this->assertContains('Holdings', $content, 'No Holdings text in content. May be wrong page returned? ');
        $this->assertContains('Barbara', $content, 'No Barbara\'s account in content. May be wrong page returned? ');
        $this->assertContains('client_holdings_table_test', $content, 'Not found client_holdings_table_test table in content. May be wrong page returned? ');
    }
}