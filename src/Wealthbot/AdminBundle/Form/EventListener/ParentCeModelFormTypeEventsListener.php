<?php

namespace Wealthbot\AdminBundle\Form\EventListener;

use Doctrine\ORM\QueryBuilder;
use Wealthbot\AdminBundle\Entity\CeModel;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;

class ParentCeModelFormTypeEventsListener implements EventSubscriberInterface
{
    public function __construct()
    {
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::BIND => 'bind'
        );
    }

    public function bind(FormEvent $event)
    {
        /** @var $data CeModel */
        $data = $event->getData();
    }
}
