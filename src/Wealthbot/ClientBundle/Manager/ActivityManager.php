<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 16.01.14
 * Time: 14:51.
 */

namespace Wealthbot\ClientBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use Wealthbot\ClientBundle\Document\Activity;
use Wealthbot\ClientBundle\Entity\ClosingAccountHistory;
use Wealthbot\ClientBundle\Entity\Workflow;
use Wealthbot\ClientBundle\Model\ActivityInterface;
use Wealthbot\ClientBundle\Model\PaymentActivityInterface;
use Wealthbot\UserBundle\Entity\User;

class ActivityManager
{
    /** @var \Doctrine\Common\Persistence\ObjectManager */
    private $dm;

    /** @var \Doctrine\ODM\MongoDB\DocumentRepository */
    private $repository;

    /** @var \Wealthbot\ClientBundle\Manager\WorkflowManager */
    private $workflowManager;

    public function __construct(DocumentManager $dm, $class, WorkflowManager $workflowManager)
    {
        $this->dm = $dm;
        $this->repository = $dm->getRepository($class);
        $this->workflowManager = $workflowManager;
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    public function findByClientQuery(User $client)
    {
        $qb = $this->repository->createQueryBuilder()
            ->field('clientUserId')->equals($client->getId())
            ->sort('createdAt', 'desc');

        return $qb->getQuery();
    }

    public function findByClient(User $client)
    {
        return $this->findByClientQuery($client)->execute();
    }

    public function findByRiaQuery(User $ria, $limit = null)
    {
        $qb = $this->repository->createQueryBuilder()
            ->field('riaUserId')->equals($ria->getId())
            ->field('isShowRia')->equals(true)
            ->limit($limit)
            ->sort('createdAt', 'desc');

        return $qb->getQuery();
    }

    public function findByRia(User $ria, $limit = null)
    {
        return $this->findByRiaQuery($ria, $limit)->execute();
    }

    /**
     * Save activity by workflow.
     *
     * @param ActivityInterface $object
     */
    public function saveActivityByObject(ActivityInterface $object)
    {
        $client = $object->getActivityClient();
        $message = $object->getActivityMessage();

        if ($client && $message) {
            if (($object instanceof Workflow) && is_array($object->getObjectIds()) && count($object->getObjectIds())) {
                $workflowableObjects = $this->workflowManager->getObjects($object);
                foreach ($workflowableObjects as $item) {
                    if ($item instanceof ClosingAccountHistory) {
                        $id = $item->getAccount()->getAccountNumber();
                    } else {
                        $id = $item->getId();
                    }

                    $activity = new Activity();
                    $activity->setClientUserId($client->getId());
                    $activity->setFirstName($client->getFirstName());
                    $activity->setLastName($client->getLastName());
                    $activity->setRiaUserId($client->getRia()->getId());
                    $activity->setMessage(sprintf($message, $id));
                    $activity->setAmount($object->getAmount());
                    $activity->setCreatedAt(new \DateTime());

                    $this->updateActivity($activity);
                }
            } else {
                $activity = $this->createActivity($object);
                $this->updateActivity($activity);
            }
        }
    }

    /**
     * Create activity by object.
     *
     * @param ActivityInterface $object
     *
     * @return null|Activity
     */
    public function createActivity(ActivityInterface $object)
    {
        $client = $object->getActivityClient();
        $message = $object->getActivityMessage();

        $activity = null;
        if ($client && $message) {
            $activity = new Activity();
            $activity->setClientUserId($client->getId());
            $activity->setClientStatus($client->getProfile()->getClientStatus());
            $activity->setFirstName($client->getFirstName());
            $activity->setLastName($client->getLastName());
            $activity->setRiaUserId($client->getRia()->getId());
            $activity->setMessage($message);
            $activity->setCreatedAt(new \DateTime());

            if ($object instanceof PaymentActivityInterface) {
                $activity->setAmount($object->getActivityAmount());
            }
        }

        return $activity;
    }

    /**
     * Update activity.
     *
     * @param Activity $activity
     */
    public function updateActivity(Activity $activity)
    {
        if (null !== $activity) {
            $this->dm->persist($activity);
            $this->dm->flush();
        }
    }
}
