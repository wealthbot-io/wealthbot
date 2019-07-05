<?php

namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Entity\CeModel;

class ParentCeModelFormTypeEventsListener implements EventSubscriberInterface
{
    public function __construct()
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::SUBMIT => 'bind',
        ];
    }

    public function bind(FormEvent $event)
    {
        /** @var $data CeModel */
        $data = $event->getData();
    }
}
