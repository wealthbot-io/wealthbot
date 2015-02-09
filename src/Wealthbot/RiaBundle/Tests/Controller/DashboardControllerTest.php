<?php

namespace Wealthbot\RiaBundle\Tests\Controller;

use Wealthbot\UserBundle\Entity\User;
use Wealthbot\UserBundle\TestSuit\ExtendedWebTestCase;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Client;

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
        $this->authenticateUser('ria', array('ROLE_RIA', 'ROLE_RIA_BASE', 'ROLE_ADMIN'));
        $crawler = $this->client->request('GET', $this->router->generate('rx_ria_dashboard'));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), 'Dashboard is available to see');

        $this->assertEquals(1,
            $crawler->filter('html:contains("You must complete the following steps before wealthbot.io will be ready to use")')->count(),
            'Dashboard have notification area');

        $this->assertEquals(1,
            $crawler->filter('html:contains("Create billing specs")')->count(),
            'There is a billing information in notication area');
    }

}