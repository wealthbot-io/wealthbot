<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 05.02.13
 * Time: 18:00
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Wealthbot\ClientBundle\Entity\Beneficiary;

class BeneficiariesCollectionFormType extends AbstractType
{
    private $isPreSaved;
    private $showSsn;

    public function __construct($isPreSaved = false, $showSsn = false)
    {
        $this->isPreSaved = $isPreSaved;
        $this->showSsn = $showSsn;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('beneficiaries', 'collection', [
            'label' => '',
            'type' => new BeneficiaryFormType($this->isPreSaved, $this->showSsn),
            'allow_add' => true,
            'allow_delete' => true,
            'prototype' => true,
            'by_reference' => false,
            'mapped' => false,
        ]);

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
    }

    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $beneficiaries = $form->get('beneficiaries')->getData();

        $primaryCount = $primaryShare = 0;
        $contingentCount = $contingentShare = 0;

        /** @var Beneficiary $beneficiary */
        foreach ($beneficiaries as $beneficiary) {
            if ($beneficiary->isPrimary()) {
                ++$primaryCount;
                $primaryShare += round($beneficiary->getShare(), 2);
            } elseif ($beneficiary->isContingent()) {
                ++$contingentCount;
                $contingentShare += round($beneficiary->getShare(), 2);
            }
        }

        if ($primaryCount > 0 && $primaryShare !== 100) {
            $form->addError(new FormError('Beneficiary share must add up to 100% for the primary beneficiary.'));
        }

        if ($contingentCount > 0 && $contingentShare !== 100) {
            $form->addError(new FormError('Beneficiary share must add up to 100% for the contingent beneficiary.'));
        }
    }

    public function getBlockPrefix()
    {
        return 'beneficiaries_collection';
    }
}
