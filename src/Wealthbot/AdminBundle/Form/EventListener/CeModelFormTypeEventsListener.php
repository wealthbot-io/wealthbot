<?php

namespace Wealthbot\AdminBundle\Form\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Wealthbot\AdminBundle\Entity\CeModel;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;

class CeModelFormTypeEventsListener implements EventSubscriberInterface
{
    /** @var FormFactory */
    private $factory;

    /** @var EntityManager */
    private $em;

    public function __construct(FormFactoryInterface $factory, EntityManager $em)
    {
        $this->factory = $factory;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::BIND => 'bind'
        );
    }

    public function preSetData(FormEvent $event)
    {
        /** @var $data CeModel */
        $data = $event->getData();
        $form = $event->getForm();

        if (null === $data) {
            return;
        }
        // check if the product object is not "new"
        if ($data->getId()) {

            //$modelsCount = $this->em->getRepository('WealthbotAdminBundle:CeModel')->getModelsCountByParentIdAndOwnerId($data->getParent()->getId(), $data->getOwnerId());

            $form->add($this->factory->createNamed('risk_rating', 'choice', $data->getRiskRating(), array(
                'empty_value' => 'Select Risk Rating',
                'choices' => array_combine(range(1, 100), range(1, 100))
            )));
        }
    }

    public function bind(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var $data CeModel */
        $data = $event->getData();

        if ($data) {
            if($form->has('risk_rating')){
                $riskRating = $form->get('risk_rating')->getData();

                $isExistRiskRating = $this->em->getRepository('WealthbotAdminBundle:CeModel')->isExistRiskRating(
                    $data->getParent()->getId(),
                    $data->getOwnerId(),
                    $data->getRiskRating(),
                    $data->getId()
                );

                if ($isExistRiskRating) {
                    $form->get('risk_rating')->addError(new FormError('The risk with parameter :risk is already exists.', array(':risk' => $riskRating)));
                }
            }
        }
    }
}
