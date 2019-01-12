<?php

namespace Wealthbot\UserBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Doctrine\UserManager as BaseManager;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use Wealthbot\UserBundle\Entity\User;

class UserManager extends BaseManager
{

    public function __construct(PasswordUpdaterInterface $passwordUpdater, CanonicalFieldsUpdater $canonicalFieldsUpdater, ObjectManager $om, string $class)
    {
        parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $om, $class);
    }


    /**
     * @return User
     */
    public function getAdmin()
    {
        return $this->repository->createQueryBuilder('ru')
            ->where('ru.roles LIKE :role')
            ->setParameter('role', '%"ROLE_SUPER_ADMIN"%')
            ->getQuery()
            ->getSingleResult();
    }

    public function findClientsByRia(User $ria)
    {
        return $this->repository->findClientsByRia($ria);
    }

    public function findClientsByRelationsType($relationsType)
    {
        return $this->repository->findClientsByRelationsType($relationsType);
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }
}
