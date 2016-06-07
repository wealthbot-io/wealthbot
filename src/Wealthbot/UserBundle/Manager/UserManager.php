<?php

namespace Wealthbot\UserBundle\Manager;

use FOS\UserBundle\Doctrine\UserManager as BaseManager;
use Wealthbot\UserBundle\Entity\User;

class UserManager extends BaseManager
{
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
