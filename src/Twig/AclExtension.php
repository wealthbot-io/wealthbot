<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 07.08.13
 * Time: 17:41
 * To change this template use File | Settings | File Templates.
 */

namespace App\Twig;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Model\Acl;

class AclExtension extends \Twig\Extension\AbstractExtension
{
    private $acl;

    public function __construct(Acl $acl)
    {
        $this->acl = $acl;
    }

    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('is_permitted', [$this, 'isPermitted']),
        ];
    }

    /**
     * Check if user has permission.
     *
     * @param string        $permission
     * @param UserInterface $user
     *
     * @return bool
     */
    public function isPermitted($permission, UserInterface $user = null)
    {
        return $this->acl->isPermitted($permission, $user);
    }

    public function getName()
    {
        return 'acl_extension';
    }
}
