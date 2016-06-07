<?php

namespace Wealthbot\AdminBundle\Tests\Controller;

use Symfony\Component\Routing\Router;
use Wealthbot\UserBundle\TestSuit\ExtendedWebTestCase;

class PasInterfacesControllerTest extends ExtendedWebTestCase
{
    /**
     * @var Router
     */
    private $router;

    public function setUp()
    {
        parent::setUp();
        $this->router = $this->container->get('router');
    }

    public function adminAuth()
    {
        $this->authenticateUser(
            'webo',
            ['ROLE_SUPER_ADMIN'],
            'backend_auth'
        );
    }

    public function testIndexAction()
    {
        $this->adminAuth();

        $uri = $this->router->generate('rx_admin_pas_interfaces_file_index');

        $crawler = $this->client->request('GET', $uri);
        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), 'Status code of "admin-pas-interfaces" must be 200.');
    }
}
