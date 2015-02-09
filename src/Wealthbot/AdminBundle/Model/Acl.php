<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 07.08.13
 * Time: 16:55
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Model;


use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class Acl
{
    const PERMISSION_VIEW = 'view';
    const PERMISSION_EDIT = 'edit';
    const PERMISSION_CREATE_USER = 'create_user';
    const PERMISSION_LOGIN_AS = 'login_as';

    private $securityContext;

    private $permissionRoles = array(
        self::PERMISSION_VIEW => array('ROLE_ADMIN'),
        self::PERMISSION_EDIT => array('ROLE_ADMIN_MASTER', 'ROLE_ADMIN_PM'),
        self::PERMISSION_CREATE_USER => array('ROLE_ADMIN_MASTER'),
        self::PERMISSION_LOGIN_AS => array('ROLE_ALLOWED_TO_SWITCH'),
    );

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * Check if user has permission.
     *
     * @param string $permission
     * @param UserInterface $user If is null then taken from security context
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function isPermitted($permission, UserInterface $user = null)
    {
        if (!array_key_exists($permission, $this->permissionRoles)) {
            throw new \InvalidArgumentException(sprintf('Invalid permission value: %s', $permission));
        }

        $roles = $this->permissionRoles[$permission];

        if (null === $user) {
            $user = $this->getUser();
        }

        return $this->securityContext->isGranted($roles, $user);
    }

    /**
     * Check user access.
     * If user does not have access then throw AccessDeniedException
     *
     * @param string $permission
     * @param UserInterface $user
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function checkAccess($permission, UserInterface $user = null)
    {
        if (!$this->isPermitted($permission, $user)) {
            throw new AccessDeniedException(sprintf('Access Denied. You does not have "%s" permission.', $permission));
        }
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->securityContext->getToken()->getUser();
    }
}