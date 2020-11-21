<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 09.04.13
 * Time: 14:39
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Entity\AssetClass;

class AssetClassWithSubclassesFormType extends AbstractType
{
    private $user;

    private $em;

    private $allSubclasses;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->user = $options['user'];
        $this->em = $options['em'];
        $this->allSubclasses = $options['allSubclasses'];

        $em = $this->em;
        $user = $this->user;
        $allSubclasses = $this->allSubclasses;

        $builder
            ->add('name', TextType::class, ['constraints' => [new NotBlank()]]);

        if ($this->user->hasRole('ROLE_RIA') &&
                $user->getRiaCompanyInformation()->isRebalancedFrequencyToleranceBand()) {
            $builder->add('tolerance_band', NumberType::class, ['scale' => 2]);
        }

        $factory = $builder->getFormFactory();

        $refreshSubclasses = function ($form, $allSubclasses) use ($factory, $user, $em) {
            $form->add($factory->createNamed('subclasses', CollectionType::class, null, [
                'entry_type' => SubclassFormType::class, null,  ['user'=>$user, 'em'=>$em, 'allSubclasses'=>$allSubclasses],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                'auto_initialize' => false,
            ]));
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($refreshSubclasses, $allSubclasses) {
            $form = $event->getForm();
            $data = $event->getData();

            if (null === $data) {
                return;
            }

            if ($data instanceof AssetClass) {
                $refreshSubclasses($form, $allSubclasses);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($refreshSubclasses, $allSubclasses) {
            $form = $event->getForm();
            $data = $event->getData();

            if (null === $data) {
                return;
            }

            $refreshSubclasses($form, $allSubclasses);
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $data->setType(AssetClass::TYPE_STOCKS);
            $data->setSubclasses($data->getSubclasses());
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\AssetClass',
            'cascade_validation' => true,
            'em'=> null,
            'user' => null,
            'allSubclasses' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'asset';
    }
}
