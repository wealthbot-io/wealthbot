<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 05.02.13
 * Time: 18:00
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Entity\Beneficiary;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BeneficiariesCollectionFormType extends AbstractType
{
    private $isPreSaved;
    private $showSsn;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->isPreSaved = $options['isPreSaved'] ?? false;
        $this->showSsn = $options['showSsn'] ?? false;



        $builder->add('beneficiaries', CollectionType::class, [
            'label' => '',
            'entity_type' => BeneficiaryFormType::class,
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

        if ($primaryCount > 0 && 100 !== $primaryShare) {
            $form->addError(new FormError('Beneficiary share must add up to 100% for the primary beneficiary.'));
        }

        if ($contingentCount > 0 && 100 !== $contingentShare) {
            $form->addError(new FormError('Beneficiary share must add up to 100% for the contingent beneficiary.'));
        }
    }

    public function getBlockPrefix()
    {
        return 'beneficiaries_collection';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'showSsn' => null,
            'isPreSaved' => null
        ]);
    }
}
