<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 19.07.13
 * Time: 17:37
 * To change this template use File | Settings | File Templates.
 */

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Event\ClientEvents;
use App\Event\WorkflowEvent;
use App\Manager\WorkflowManager;

class WorkflowEventListener implements EventSubscriberInterface
{
    /** @var \App\Manager\WorkflowManager */
    private $wm;

    public function __construct(WorkflowManager $wm)
    {
        $this->wm = $wm;
    }

    public static function getSubscribedEvents()
    {
        return [
            ClientEvents::CLIENT_WORKFLOW => 'createWorkflow',
        ];
    }

    public function createWorkflow(WorkflowEvent $event)
    {
        $workflow = $this->wm->createWorkflow(
            $event->getClient(),
            $event->getObject(),
            $event->getType(),
            $event->getSignatures(),
            $event->getObjectIds()
        );

        $event->setData($workflow);
    }
}
