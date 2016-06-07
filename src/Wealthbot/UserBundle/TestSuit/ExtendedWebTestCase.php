<?php

namespace Wealthbot\UserBundle\TestSuit;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ExtendedWebTestCase extends WebTestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var \ProjectServiceContainer
     */
    protected $container;

    public function setUp()
    {
        parent::setUp();

        //HTTP client
        $this->client = static::createClient();

        //Service container
        $this->container = $this->client->getContainer();

        //Begin SQL transaction to rollback it
        $this->em = $this->container->get('doctrine.orm.default_entity_manager');
        $this->em->beginTransaction();
    }

    public function tearDown()
    {
        $this->em->rollback();
    }

    /**
     * Authenticate selected user.
     * If you want to test another firewall, look name for it
     * in the security.yml file for "context" value.
     *
     * @param $userName
     * @param $roles
     * @param null $firewallName
     *
     * @return \FOS\UserBundle\Model\UserInterface
     */
    protected function authenticateUser($userName, $roles, $firewallName = null)
    {
        $session = $this->client->getContainer()->get('session');

        if (!$firewallName) {
            $firewallName = $this->getFirewallName();
        }
        $user = $this->container->get('fos_user.user_manager')->findUserByUsername($userName);
        $token = new UsernamePasswordToken($user, null, $firewallName, $roles);
        $session->set('_security_'.$firewallName, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);

        return $user;
    }

    protected function getFirewallName()
    {
        return 'frontend_auth';
    }
}
