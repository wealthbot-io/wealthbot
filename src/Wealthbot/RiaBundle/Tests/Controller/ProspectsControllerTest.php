<?php
/**
 * Created by PhpStorm.
 * User: virtustilus
 * Date: 16.12.13
 * Time: 22:38.
 */

namespace Wealthbot\RiaBundle\Tests\Controller;

use Symfony\Component\Routing\Router;
use Wealthbot\UserBundle\TestSuit\ExtendedWebTestCase;

class ProspectsControllerTest extends ExtendedWebTestCase
{
    /** @var Router */
    protected $router;

    public function setUp()
    {
        parent::setUp();

        $this->router = $this->container->get('router');
    }

    public function testSuggestedPortfolioAction()
    {
        $this->authenticateUser('raiden@wealthbot.io', ['ROLE_RIA']);

        $client = $this->em->getRepository('WealthbotUserBundle:User')->findOneBy(['username' => 'johnny@wealthbot.io']);
        $url = $this->router->generate('rx_ria_prospect_portfolio', [
            'client_id' => $client->getId(),
        ]);

        $crawler = $this->client->request('GET', $url);
        $response = $this->client->getResponse();

        $this->assertSame(200, $response->getStatusCode(), ($crawler->filter('h1')->count() > 0 ? $crawler->filter('h1')->text() : $response->getContent()));
    }

    public function testSuggestedPortfolioWrongUser()
    {
        $this->authenticateUser('raiden@wealthbot.io', ['ROLE_RIA']);

        $client = $this->em->getRepository('WealthbotUserBundle:User')->findOneBy(['username' => 'client']);
        $url = $this->router->generate('rx_ria_prospect_portfolio', [
                'client_id' => $client->getId(),
        ]);

        $crawler = $this->client->request('GET', $url);
        $response = $this->client->getResponse();

        $this->assertSame(404, $response->getStatusCode(), ($crawler->filter('h1')->count() > 0 ? $crawler->filter('h1')->text() : $response->getContent()));
    }
}
