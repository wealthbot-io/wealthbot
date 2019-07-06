<?php

namespace App\Form\EventListener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use App\Entity\CeModel;
use App\Repository\SecurityAssignmentRepository;

class ModelAssumptionFormTypeEventListener implements EventSubscriberInterface
{
    /** @var $factory FormFactoryInterface */
    private $factory;

    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    public function __construct(FormFactoryInterface $factory, EntityManager $em)
    {
        $this->factory = $factory;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SET_DATA => 'preSetData',
            FormEvents::SUBMIT => 'bind',
        ];
    }

    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var $data CeModel */
        $data = $event->getData();
        $owner = $data->getOwner();

        /** @var SecurityAssignmentRepository $repo */
        $repo = $this->em->getRepository('App\Entity\SecurityAssignment');

        if ($data->getParent()) {
            $parentModelId = $data->getParent()->getId();
        } else {
            $parentModelId = $data->getId();
        }

        $riaCompanyInformation = $owner->getRiaCompanyInformation();

        if ($data->getParent() && ($riaCompanyInformation && $riaCompanyInformation->getIsShowExpectedCosts())) {
            $commissions = $repo->findMinAndMaxTransactionFeeForModel($parentModelId);

            $form->add($this->factory->createNamed('commission_min', NumberType::class, null, [
                'label' => 'Commissions:',
                'scale' => 2,
                'grouping' => true,
                'data' => isset($commissions['minimum']) ? $commissions['minimum'] : 0.00,
                'disabled' => true,
                'auto_initialize' => false,
            ]));

            $form->add($this->factory->createNamed('commission_max', NumberType::class, null, [
                'label' => '',
                'scale' => 2,
                'grouping' => true,
                'data' => isset($commissions['maximum']) ? $commissions['maximum'] : 0.00,
                'disabled' => true,
                'auto_initialize' => false,
            ]));
        }

        if ($owner->hasRole('ROLE_RIA') && $owner->getRiaCompanyInformation()->getIsShowClientExpectedAssetClass()) {
            $form->add($this->factory->createNamed('forecast', NumberType::class, null, [
                'label' => 'Forecast:',
                'data' => ($data && $data->getForecast() ? $data->getForecast() : 0),
                'auto_initialize' => false,
            ]));
        }
    }

    public function bind(FormEvent $event)
    {
        /** @var $data CeModel */
        $data = $event->getData();

        if (null === $data) {
            return;
        }

        $form = $event->getForm();

//        if ($form->has('generous_market_return') && ($data->getGenerousMarketReturn() < 1 || $data->getGenerousMarketReturn() >= 2)) {
//            $form->get('generous_market_return')->addError(new FormError('The value must be between 1 and 2'));
//        }

        if ($form->has('low_market_return') && ($data->getLowMarketReturn() < 0 || $data->getLowMarketReturn() >= 1)) {
            $form->get('low_market_return')->addError(new FormError('The value must be between 0 and 1'));
        }
    }
}
