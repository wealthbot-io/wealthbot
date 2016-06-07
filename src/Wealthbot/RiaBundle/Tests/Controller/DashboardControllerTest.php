<?php

namespace Wealthbot\RiaBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Wealthbot\UserBundle\TestSuit\ExtendedWebTestCase;

class DashboardControllerTest extends ExtendedWebTestCase
{
    /** @var Router */
    protected $router;

    public function setUp()
    {
        parent::setUp();

        $this->router = $this->container->get('router');
    }

    public function testIndexThereIsRequiredStepsAction()
    {
        $this->authenticateUser('ria', ['ROLE_RIA', 'ROLE_RIA_BASE', 'ROLE_ADMIN']);
        $crawler = $this->client->request('GET', $this->router->generate('rx_ria_dashboard'));

        $this->assertSame(200, $this->client->getResponse()->getStatusCode(), 'Dashboard is available to see');

        $this->assertSame(1,
            $crawler->filter('html:contains("You must complete the following steps before wealthbot.io will be ready to use")')->count(),
            'Dashboard have notification area');

        $this->assertSame(1,
            $crawler->filter('html:contains("Create billing specs")')->count(),
            'There is a billing information in notication area');
    }
}
