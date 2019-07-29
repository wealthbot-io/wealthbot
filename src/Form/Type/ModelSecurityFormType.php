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
            ->add('expense_ratio', HiddenType::class, ['mapped' => false])

            ->add('subclass_id', EntityType::class, [
            'class' => 'App\\Entity\\Subclass',
            'property_path' => 'name',
            'placeholder' => 'Choose an option',
            'auto_initialize' => false,
            'mapped' => false
        ])

        ->add('asset_class_id', EntityType::class, [
            'class' => 'App\\Entity\\AssetClass',
            'placeholder' => 'Choose an option',
            'mapped' => false,
            'auto_initialize' => false,
        ]);
        $factory = $builder->getFormFactory();

       // if ($model->getOwner() && $model->getOwner()->hasRole('ROLE_RIA') && $model->getOwner()->getRiaCompanyInformation()->getUseMunicipalBond()) {
            $builder->add('muniSubstitution', CheckboxType::class, ['required' => false, 'mapped'=>false]);
      ///  };

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
