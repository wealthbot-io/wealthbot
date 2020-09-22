<?php
namespace App\Model;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class Acl
{
    const PERMISSION_VIEW = 'view';
    const PERMISSION_EDIT = 'edit';
    const PERMISSION_CREATE_USER = 'create_user';
    const PERMISSION_LOGIN_AS = 'login_as';
    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;
    /**
     * @var TokenStorage
     */
    private $tokenStorage;
    private $permissionRoles = [
        self::PERMISSION_VIEW => ['ROLE_ADMIN'],
        self::PERMISSION_EDIT => ['ROLE_ADMIN_MASTER', 'ROLE_ADMIN_PM'],
        self::PERMISSION_CREATE_USER => ['ROLE_ADMIN_MASTER'],
        self::PERMISSION_LOGIN_AS => ['ROLE_ALLOWED_TO_SWITCH'],
    ];
    /**
     * @param TokenStorage         $tokenStorage
     * @param AuthorizationChecker $authorizationChecker
     */
    public function __construct(TokenStorage $tokenStorage, AuthorizationChecker $authorizationChecker)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }
    /**
     * Check if user has permission.
     *
     * @param string        $permission
     * @param UserInterface $user       If is null then taken from security context
     *
     * @return bool
     *
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
        return $this->authorizationChecker->isGranted($roles, $user);
    }
    /**
     * Check user access.
     * If user does not have access then throw AccessDeniedException.
     *
     * @param string        $permission
     * @param UserInterface $user
     *
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
        return $this->tokenStorage->getToken()->getUser();
    }
}
