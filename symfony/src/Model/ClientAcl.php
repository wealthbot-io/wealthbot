<?php
namespace App\Model;

use FOS\UserBundle\Model\User;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Class Acl.
 */
class ClientAcl
{
    /**
     * @var Container
     */
    private $container;
    /**
     * @var object
     */
    private $em;
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationChecker
     */
    private $authorizationChecker;
    /**
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage
     */
    private $tokenStorage;
    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->em = $container->get('doctrine')->getManager();
        $this->authorizationChecker = $container->get('security.authorization_checker');
        $this->tokenStorage = $container->get('security.token_storage');
    }
    /**
     * Is user has ROLE_SLAVE_CLIENT role.
     *
     * @return bool
     */
    public function isSlaveClient()
    {
        if ($this->authorizationChecker->isGranted('ROLE_SLAVE_CLIENT')) {
            return true;
        }
        return false;
    }
    /**
     * Is user has ROLE_RIA and ROLE_CLIENT_VIEW roles.
     *
     * @return bool
     */
    public function isRiaClientView()
    {
        if ($this->authorizationChecker->isGranted('ROLE_RIA') && $this->authorizationChecker->isGranted('ROLE_CLIENT_VIEW')) {
            $clientId = $this->tokenStorage->getToken()->getAttribute('ria.client_view.client_id');
            if ($clientId) {
                return true;
            }
        }
        return false;
    }
    /**
     * Set client id for ria client view and add ROLE_CLIENT_VIEW to ria.
     *
     * @param User $ria
     * @param int  $clientId
     *
     * @throws \InvalidArgumentException
     */
    public function setClientForRiaClientView(User $ria, $clientId)
    {
        $this->checkIsRiaUser($ria);
        $previousRoles = $this->tokenStorage->getToken()->getRoles();
        $previousRoles[] = 'ROLE_CLIENT_VIEW';
        //$ria->addRole('ROLE_CLIENT_VIEW');
        //$token = new UsernamePasswordToken($ria, null, 'main', $ria->getRoles());
        $token = new UsernamePasswordToken($ria, null, 'main', $previousRoles);
        $token->setAttribute('ria.client_view.client_id', $clientId);
        $this->tokenStorage->setToken($token);
    }
    /**
     * Get user for ria client view.
     *
     * @param User $ria
     *
     * @return mixed|null
     *
     * @throws \InvalidArgumentException
     */
    public function getClientForRiaClientView(User $ria)
    {
        $this->checkIsRiaUser($ria);
        $clientId = $this->tokenStorage->getToken()->getAttribute('ria.client_view.client_id');
        if (null === $clientId) {
            return;
        }
        $repository = $this->em->getRepository('App\Entity\User');
        return $repository->getClientByIdAndRiaId($clientId, $ria->getId());
    }
    /**
     * Reset ria client view.
     *
     * @param User $ria
     *
     * @throws \InvalidArgumentException
     */
    public function resetRiaClientView(User $ria)
    {
        $this->checkIsRiaUser($ria);
        $ria->removeRole('ROLE_CLIENT_VIEW');
        $this->tokenStorage->getToken()->setAttribute('ria.client_view.client_id', null);
    }
    /**
     * Get from security context user.
     *
     * @return mixed
     */
    public function getUser()
    {
        return $this->tokenStorage->getToken()->getUser();
    }
    /**
     * Throw exception if ria user has not ROLE_RIA role.
     *
     * @param User $ria
     *
     * @throws \InvalidArgumentException
     */
    private function checkIsRiaUser(User $ria)
    {
        if (!$ria->hasRole('ROLE_RIA')) {
            throw new \InvalidArgumentException('User does not have ria permissions.');
        }
    }
}
