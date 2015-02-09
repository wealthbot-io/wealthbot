<?php
/**
 * Created by PhpStorm.
 * User: virtustilus
 * Date: 04.02.14
 * Time: 1:35
 */

namespace Wealthbot\AdminBundle\Tests\Controller;


use Wealthbot\UserBundle\TestSuit\ExtendedWebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Router;

class PasInterfacesControllerTest extends ExtendedWebTestCase {

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
            'admin',
            array('ROLE_SUPER_ADMIN'),
            'backend_auth'
        );
    }

    public function testIndexAction()
    {
        $this->adminAuth();

        $uri = $this->router->generate('rx_admin_pas_interfaces_file_index');

        $crawler = $this->client->request('GET', $uri);
        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode(), 'Status code of "admin-pas-interfaces" must be 200.');
    }

} 