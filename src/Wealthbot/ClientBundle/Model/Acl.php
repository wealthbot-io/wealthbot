<?php
namespace Wealthbot\ClientBundle\Model;


use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\SecurityContextInterface;

class Acl
{
    private $securityContext;
    private $em;

    public function __construct(SecurityContextInterface $securityContext, EntityManager $em)
    {
        $this->securityContext = $securityContext;
        $this->em = $em;
    }

    /**
     * Is user has ROLE_SLAVE_CLIENT role
     *
     * @return bool
     */
    public function isSlaveClient()
    {
        if ($this->securityContext->isGranted('ROLE_SLAVE_CLIENT')) {
            return true;
        }

        return false;
    }

    /**
     * Is user has ROLE_RIA and ROLE_CLIENT_VIEW roles
     *
     * @return bool
     */
    public function isRiaClientView()
    {
        if ($this->securityContext->isGranted('ROLE_RIA') && $this->securityContext->isGranted('ROLE_CLIENT_VIEW')) {
            $clientId = $this->securityContext->getToken()->getAttribute('ria.client_view.client_id');

            if ($clientId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set client id for ria client view and add ROLE_CLIENT_VIEW to ria
     *
     * @param User $ria
     * @param int $clientId
     * @throws \InvalidArgumentException
     */
    public function setClientForRiaClientView(User $ria, $clientId)
    {
        $this->checkIsRiaUser($ria);

        $previousRoles = $this->securityContext->getToken()->getRoles();
        $previousRoles[] = 'ROLE_CLIENT_VIEW';

        //$ria->addRole('ROLE_CLIENT_VIEW');

        //$token = new UsernamePasswordToken($ria, null, 'main', $ria->getRoles());
        $token = new UsernamePasswordToken($ria, null, 'main', $previousRoles);
        $token->setAttribute('ria.client_view.client_id', $clientId);

        $this->securityContext->setToken($token);
    }

    /**
     * Get user for ria client view
     *
     * @param User $ria
     * @return mixed|null
     * @throws \InvalidArgumentException
     */
    public function getClientForRiaClientView(User $ria)
    {
        $this->checkIsRiaUser($ria);

        $clientId = $this->securityContext->getToken()->getAttribute('ria.client_view.client_id');
        if (null === $clientId) {
            return null;
        }

        $repository = $this->em->getRepository('WealthbotUserBundle:User');

        return $repository->getClientByIdAndRiaId($clientId, $ria->getId());
    }

    /**
     * Reset ria client view
     *
     * @param User $ria
     * @throws \InvalidArgumentException
     */
    public function resetRiaClientView(User $ria)
    {
        $this->checkIsRiaUser($ria);

        $ria->removeRole('ROLE_CLIENT_VIEW');
        $this->securityContext->getToken()->setAttribute('ria.client_view.client_id', null);
    }

    /**
     * Get from security context user
     *
     * @return mixed
     */
    public function getUser()
    {
        return $this->securityContext->getToken()->getUser();
    }

    /**
     * Throw exception if ria user has not ROLE_RIA role
     *
     * @param User $ria
     * @throws \InvalidArgumentException
     */
    private function checkIsRiaUser(User $ria)
    {
        if (!$ria->hasRole('ROLE_RIA')) {
            throw new \InvalidArgumentException('User does not have ria permissions.');
        }
    }
}