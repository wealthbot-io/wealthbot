<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 06.08.13
 * Time: 12:23
 * To change this template use File | Settings | File Templates.
 */

namespace App\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use App\Entity\UserHistory;
use App\Entity\User;

class UserHistoryManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var string
     */
    private $class;

    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    private $repository;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var AuthorizationChecker
     */
    private $authorizationChecker;

    public function __construct(ObjectManager $om, $class, TokenStorage $tokenStorage, AuthorizationChecker $authorizationChecker)
    {
        $this->om = $om;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;

        $this->repository = $om->getRepository($class);

        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    /**
     * Find history item.
     *
     * @param int $id
     *
     * @return object
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }

    /**
     * Find history item by criteria.
     *
     * @param array $criteria
     *
     * @return object
     */
    public function findOneBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * Find history items by criteria.
     *
     * @param array $criteria
     * @param array $orderBy
     * @param null  $limit
     * @param null  $offset
     *
     * @return mixed
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * Save history item.
     *
     * @param User $user
     * @param $description
     *
     * @return UserHistory
     *
     * @throws \Exception
     */
    public function save(User $user, $description)
    {
        $updater = $this->getUpdatedBy();
        if (!($updater instanceof User)) {
            throw new \Exception(sprintf('The User must be instance %s.', get_class(new User())));
        }

        $historyItem = new UserHistory();

        $historyItem->setUser($user);
        $historyItem->setUpdater($updater);
        $historyItem->setDescription($description);
        $historyItem->setUpdaterType($this->getUpdaterType());

        $this->om->persist($historyItem);
        $this->om->flush();

        return $historyItem;
    }

    /**
     * Get user from security context.
     * If user switched from another user then returns original user.
     *
     * @return mixed
     */
    private function getUpdatedBy()
    {
        $token = $this->tokenStorage->getToken();

        foreach ($token->getRoles() as $role) {
            if ($role instanceof SwitchUserRole) {
                $original = $role->getSource()->getUser();

                return $this->findUser($original->getId());
            }
        }

        return $token->getUser();
    }

    /**
     * Find user in database.
     *
     * @param int $id
     *
     * @return User
     */
    private function findUser($id)
    {
        return $this->om->getRepository('App\Entity\User')->find($id);
    }

    /**
     * Get updater type by roles.
     *
     * @return int
     */
    private function getUpdaterType()
    {
        if ($this->authorizationChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
            $type = UserHistory::UPDATER_TYPE_ADMIN;
        } elseif ($this->authorizationChecker->isGranted('ROLE_RIA')) {
            $type = UserHistory::UPDATER_TYPE_RIA;
        } else {
            $type = UserHistory::UPDATER_TYPE_CLIENT;
        }

        return $type;
    }
}
