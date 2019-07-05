<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 27.08.13
 * Time: 17:50
 * To change this template use File | Settings | File Templates.
 */

namespace App\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Entity\SecurityPrice;

class SecurityPriceEventListener
{
    public function prePersist(LifecycleEventArgs $args)
    {
        $object = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if (($object instanceof SecurityPrice)) {
            $repository = $entityManager->getRepository('App\Entity\SecurityPrice');
            $repository->resetIsCurrentFlagBySecurityId($object->getSecurity()->getId());
        }
    }
}
