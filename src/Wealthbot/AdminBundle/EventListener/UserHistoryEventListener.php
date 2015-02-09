<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 06.08.13
 * Time: 12:36
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\EventListener;


use Wealthbot\AdminBundle\AdminEvents;
use Wealthbot\AdminBundle\Event\UserHistoryEvent;
use Wealthbot\AdminBundle\Manager\UserHistoryManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserHistoryEventListener implements EventSubscriberInterface
{
    private $manager;

    public function __construct(UserHistoryManager $manager)
    {
        $this->manager = $manager;
    }

    public static function getSubscribedEvents()
    {
        return array(
            AdminEvents::USER_HISTORY => 'createHistoryItem'
        );
    }

    public function createHistoryItem(UserHistoryEvent $event)
    {
        $this->manager->save($event->getUser(), $event->getDescription());
    }

}