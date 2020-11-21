<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 06.08.13
 * Time: 12:36
 * To change this template use File | Settings | File Templates.
 */

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Event\AdminEvents;
use App\Event\UserHistoryEvent;
use App\Manager\UserHistoryManager;

class UserHistoryEventListener implements EventSubscriberInterface
{
    private $manager;

    public function __construct(UserHistoryManager $manager)
    {
        $this->manager = $manager;
    }

    public static function getSubscribedEvents()
    {
        return [
            AdminEvents::USER_HISTORY => 'createHistoryItem',
        ];
    }

    public function createHistoryItem(UserHistoryEvent $event)
    {
        $this->manager->save($event->getUser(), $event->getDescription());
    }
}
