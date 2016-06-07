<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 26.12.13
 * Time: 20:22.
 */

namespace Wealthbot\ClientBundle\Service;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Wealthbot\ClientBundle\Document\Activity;
use Wealthbot\ClientBundle\Entity\ClientPortfolio;
use Wealthbot\ClientBundle\Entity\Distribution;
use Wealthbot\ClientBundle\Entity\SystemAccount;
use Wealthbot\ClientBundle\Entity\Workflow;
use Wealthbot\ClientBundle\Model\ActivityInterface;
use Wealthbot\ClientBundle\Model\BaseContribution;
use Wealthbot\ClientBundle\Repository\WorkflowRepository;
use Wealthbot\SignatureBundle\Entity\DocumentSignature;
use Wealthbot\UserBundle\Entity\User;

class DoctrineSubscriber implements EventSubscriber
{
    private $container,                         /* @var \Symfony\Component\DependencyInjection\ContainerInterface */
        $inserted = [],
        $updated = [],
        $messages = []
    ;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postFlush,
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $this->updated = [];

        $activityManager = $this->container->get('wealthbot.activity.manager');
        $workflowManager = $this->container->get('wealthbot.workflow.manager');

        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        /** @var WorkflowRepository $repository */
        $repository = $em->getRepository('WealthbotClientBundle:Workflow');
        $meta = $em->getClassMetadata($repository->getClassName());

        $paperworkMessages = Workflow::getPaperworkMessageChoices();
        $this->messages = [];

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            $this->inserted[] = $entity;
        }

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof ClientPortfolio) {
                $objectType = $em->getClassMetadata(get_class($entity))->getName();
                $workflow = $repository->findOneBy([
                    'object_id' => $entity->getId(),
                    'object_type' => $objectType,
                    'message_code' => $entity->getWorkflowMessageCode(),
                ]);

                if ($workflow) {
                    $workflowManager->updateClientStatusByClientPortfolio($workflow, $entity);
                    $em->persist($workflow);

                    $uow->computeChangeSet($meta, $workflow);

                    // If status modified to 'client accepted'
                    $changeSet = $uow->getEntityChangeSet($entity);
                    if (isset($changeSet['status']) &&
                        $changeSet['status'][0] !== ClientPortfolio::STATUS_CLIENT_ACCEPTED &&
                        $changeSet['status'][1] === ClientPortfolio::STATUS_CLIENT_ACCEPTED
                    ) {
                        $activity = $activityManager->createActivity($workflow);
                        $activityManager->updateActivity($activity);
                    }
                }
            } elseif ($entity instanceof SystemAccount) {
                $objectType = $em->getClassMetadata(get_class($entity))->getName();
                $workflow = $repository->findOneBy([
                    'object_id' => $entity->getId(),
                    'object_type' => $objectType,
                    'message_code' => $entity->getWorkflowMessageCode(),
                ]);

                if ($workflow) {
                    $workflowManager->updateClientStatusBySystemAccount($workflow, $entity);
                    $em->persist($workflow);

                    $uow->computeChangeSet($meta, $workflow);
                }
            } elseif ($entity instanceof DocumentSignature) {
                $workflow = $repository->findOneByDocumentSignatureId($entity->getId());
                if ($workflow && $workflow->isPaperwork()) {
                    $workflowManager->updateClientStatusByDocumentSignatures($workflow);
                    $em->persist($workflow);

                    $uow->computeChangeSet($meta, $workflow);
                }
            } elseif (($entity instanceof Distribution) || ($entity instanceof BaseContribution)) {
                $this->updated[$entity->getId()] = $entity;
                $message = str_replace('New/Update', 'Update', $paperworkMessages[$entity->getWorkflowMessageCode()]);
                $this->messages[$entity->getId()] = substr($message, 0, -1);
            }
        }

        foreach ($this->updated as $id => $item) {
            $client = $item->getClientAccount()->getClient();

            $activity = $this->createActivity($client, $this->messages[$id], $item->getAmount());
            $activityManager->updateActivity($activity);
        }
    }

    public function PostFlush(PostFlushEventArgs $eventArgs)
    {
        $activityManager = $this->container->get('wealthbot.activity.manager');
        $paperworkMessages = Workflow::getPaperworkMessageChoices();

        foreach ($this->inserted as $entity) {
            if (($entity instanceof ActivityInterface)) {
                $activityManager->saveActivityByObject($entity);
            }

            if (($entity instanceof Distribution) || ($entity instanceof BaseContribution)) {
                $client = $entity->getClientAccount()->getClient();
                $message = str_replace('New/Update', 'New', $paperworkMessages[$entity->getWorkflowMessageCode()]);
                $message = substr($message, 0, -1);

                $activity = $this->createActivity($client, $message, $entity->getAmount());
                $activityManager->updateActivity($activity);
            }
        }
    }

    private function createActivity(User $client, $message, $amount)
    {
        $activity = new Activity();
        $activity->setClientUserId($client->getId());
        $activity->setClientStatus($client->getProfile()->getClientStatus());
        $activity->setFirstName($client->getFirstName());
        $activity->setLastName($client->getLastName());
        $activity->setRiaUserId($client->getRia()->getId());
        $activity->setAmount($amount);
        $activity->setMessage($message);
        $activity->setCreatedAt(new \DateTime());

        return $activity;
    }
}
