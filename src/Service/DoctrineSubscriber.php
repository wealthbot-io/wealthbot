<?php

namespace App\Service;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use App\Entity\BillingSpec;

class DoctrineSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [Events::onFlush];
    }

    /**
     * This method is executing in FLUSH action before it change data in database.
     *
     * Logic: leave only 1 master BillingSpec for every user that specs was changed.
     *
     * Processing: create, update and delete specs.
     *
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();
        $repo = $em->getRepository('App\Entity\BillingSpec');

        $allActions = [];
        $deletedSpecs = [];
        foreach ($uow->getScheduledEntityDeletions() as $deleted) {
            if ($deleted instanceof BillingSpec) {
                $deletedSpecs[$deleted->getId()] = $deleted;
                $allActions[$deleted->getId()] = $deleted;
            }
        }

        $insertSpecs = [];
        foreach ($uow->getScheduledEntityInsertions() as $internalId => $insertion) {
            if ($insertion instanceof BillingSpec) {
                $insertSpecs[$internalId] = $insertion;
                $allActions[$internalId] = $insertion;
            }
        }

        $changedSpecs = [];
        foreach ($uow->getScheduledEntityUpdates() as $changed) {
            if ($changed instanceof BillingSpec) {
                $changedSpecs[$changed->getId()] = $changed;
                $allActions[$changed->getId()] = $changed;
            }
        }

        $usersById = [];
        $userMasterSpecs = [];
        $userSpecs = [];
        foreach ($allActions as $id => $spec) {
            /* @var BillingSpec $spec */
            $owner = $spec->getOwner();
            if (null === $owner) {
                $userId = 0;
            } else {
                $userId = $owner->getId();
            }
            $usersById[$userId] = $owner;
            if (!isset($userMasterSpecs[$userId])) {
                $userMasterSpecs[$userId] = [];
                $userSpecs[$userId] = [];
            }
            if (!isset($deletedSpecs[$id])) {
                if ($spec->getMaster()) {
                    $userMasterSpecs[$userId][$id] = $spec;
                }
                $userSpecs[$userId][$id] = $spec;
            }
        }

        //was anyone billing spec changed?
        if (count($userMasterSpecs)) {
            $meta = $em->getClassMetadata($repo->getClassName());

            foreach ($userMasterSpecs as $userId => $masterSpecs) {
                /** @var BillingSpec[] $specs */
                $specs = $userSpecs[$userId];

                $foundMasterId = null;
                //1 new
                if ($masterSpecs) {
                    $ids = array_keys($masterSpecs);
                    $foundMasterId = $ids[0];
                }
                //2 old
                $anotherSpecs = $repo->findBy(['owner' => $usersById[$userId]]);
                foreach ($anotherSpecs as $spec) {
                    $id = $spec->getId();
                    if (isset($changedSpecs[$id]) || isset($deletedSpecs[$id]) || $foundMasterId === $id) {
                        continue;
                    }
                    if (!$foundMasterId && $spec->getMaster()) {
                        $foundMasterId = $id;
                    }
                    $specs[$id] = $spec;
                }
                //3 get any
                if (!$foundMasterId) {
                    $ids = array_keys($specs);
                    $foundMasterId = $ids[0];
                }
                //4 set another and save
                foreach ($specs as $id => $spec) {
                    $spec->setMaster(($id === $foundMasterId));
                    if (isset($changedSpecs[$id]) || isset($insertSpecs[$id])) {
                        $uow->recomputeSingleEntityChangeSet($meta, $spec);
                    } else {
                        $uow->computeChangeSet($meta, $spec);
                    }
                }
            }
        }
    }
}
