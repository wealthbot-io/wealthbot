<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 14.04.13
 * Time: 14:37
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Entity\AssetClass;
use App\Entity\SecurityAssignment;

class ModelSecurityFormType extends AbstractType
{
    /** @param \App\Entity\CeModel */
    private $model;
    private $em;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $model = $this->model = $options['selected_model'];
        $em = $this->em = $options['em'];

        $builder
            ->add('fund_symbol', TextType::class, [
                //'mapped' => false /*'fund.symbol'*/
            ])
            ->add('security_id', HiddenType::class, ['constraints' => [new NotBlank(['message' => 'Please choice a Symbol from list.'])]])
            ->add('type', HiddenType::class, ['mapped' => false])
            ->add('expense_ratio', HiddenType::class, ['mapped' => false]);

        $factory = $builder->getFormFactory();

        if ($model->getOwner() && $model->getOwner()->hasRole('ROLE_RIA') && $model->getOwner()->getRiaCompanyInformation()->getUseMunicipalBond()) {
            $builder->add('muniSubstitution', CheckboxType::class, ['required' => false, 'mapped'=>false]);
        }

        $refreshSubclasses = function ($form, $assetClass) use ($factory, $model) {
            $form->add($factory->createNamed('subclass_id', EntityType::class, null, [
                'class' => 'App\\Entity\\Subclass',
                'query_builder' => function (EntityRepository $er) use ($model, $assetClass) {
                    $qb = $er->createQueryBuilder('s')
                        ->leftJoin('s.assetClass', 'ac')
                        ->andWhere('ac.model_id = :model_id')
                        ->andWhere('ac.id = :asset_class_id')
                        ->setParameter('model_id', $model->getId())
                        ->orderBy('s.name');

                    if ($assetClass instanceof AssetClass) {
                        $qb->setParameter('asset_class_id', $assetClass->getId());
                    } elseif (is_numeric($assetClass)) {
                        $qb->setParameter('asset_class_id', $assetClass);
                    } else {
                        $qb->setParameter('asset_class_id', null);
                    }

                    return $qb;
                },
                'property_path' => 'subclass',
                'placeholder' => 'Choose an option',
                'auto_initialize' => false,
            ]));

            $form->add($factory->createNamed('asset_class_id', EntityType::class, null, [
                'class' => 'App\\Entity\\AssetClass',
                'query_builder' => function (EntityRepository $er) use ($model) {
                    return $er->createQueryBuilder('ac')
                        ->where('ac.model_id = :model_id')
                        ->setParameter(':model_id', $model->getId())
                        ->orderBy('ac.name');
                },
                'placeholder' => 'Choose an option',
                //'mapped' => false,
                'data' => $assetClass ? $assetClass : null,
                'auto_initialize' => false,
            ]));
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($refreshSubclasses) {
            $form = $event->getForm();
            $data = $event->getData();

            $this->model = $form->getConfig()->getOption('model');
            $this->em = $form->getConfig()->getOption('model');

            if (null === $data) {
                return;
            }

            if ($data instanceof SecurityAssignment) {
                if ($data->getSubclass()) {
                    $refreshSubclasses($form, $data->getSubclass()->getAssetClass());
                } else {
                    $refreshSubclasses($form, null);
                }
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($refreshSubclasses) {
            $form = $event->getForm();
            $data = $event->getData();

            if (array_key_exists('asset_class_id', $data)) {
                $refreshSubclasses($form, $data['asset_class_id']);
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($em, $model) {
            $form = $event->getForm();
            $data = $event->getData();

            if (null === $data) {
                return;
            }

            $exist = $em->getRepository('App\Entity\SecurityAssignment')->findByModelIdAndSecurityId($model->getId(), $data->getSecurityId());
            if ($exist && $data->getId() !== $exist->getId()) {
                $form->addError(new FormError('This SecurityAssignment already exist for this model.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\SecurityAssignment',
            'em' => null,
            'selected_model' => null,
            'securityAssignment' => null,

        ]);
    }

    public function getBlockPrefix()
    {
        return 'model_security_form';
    }
}
