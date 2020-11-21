<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\BillingSpec;

class BillingSpecFormType extends AbstractType
{
    /**
     * @var FormFactory
     */
    protected $factory;

    protected $builder;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->factory = $builder->getFormFactory();

        $this->builder = $builder;

        $builder
            ->add('name', TextType::class)
            ->add('master', CheckboxType::class)
        //@TODO need custom validator by values
            ->add('type', IntegerType::class)
            ->add('minimalFee', IntegerType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'addFees']);
    }

    public function addFees(FormEvent $event)
    {
        /* @var $billingSpec BillingSpec */
        $billingSpec = $event->getData();

        //Attach a tier form
        if (BillingSpec::TYPE_TIER === $billingSpec->getType()) {
            $event->getForm()->add(
                $this->factory->createNamed('fees', 'collection', $billingSpec->getFees(), [
                    'type' => new TierFeeFormType(),
                    'allow_add' => true,
                    'by_reference' => false,
                    'auto_initialize' => false,
                ])
            );
        }
        //Attach float form
        elseif (BillingSpec::TYPE_FLAT === $billingSpec->getType()) {
            $event->getForm()->add(
                $this->factory->createNamed('fees', 'collection', $billingSpec->getFees(), [
                    'type' => new FlatFeeFormType(),
                    'allow_add' => true,
                    'by_reference' => false,
                    'auto_initialize' => false,
                ])
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\BillingSpec',
            'validation_groups' => false,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'billing_spec';
    }
}
