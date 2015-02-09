<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Wealthbot\AdminBundle\Entity\BillingSpec;
use Wealthbot\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BillingSpecFormType extends AbstractType
{

    /**
     * @var FormFactory
     */
    protected $factory;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->factory = $builder->getFormFactory();

        $this->builder = $builder;

        $builder
            ->add('name', 'text')
            ->add('master', 'checkbox')
        //@TODO need custom validator by values
            ->add('type', 'integer')
            ->add('minimalFee', 'integer')
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'addFees'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\AdminBundle\Entity\BillingSpec',
        ));
    }

    public function addFees(FormEvent $event)
    {
        /* @var $billingSpec BillingSpec */
        $billingSpec = $event->getData();

        //Attach a tier form
        if( $billingSpec->getType() == BillingSpec::TYPE_TIER )
        {
            $event->getForm()->add(
                $this->factory->createNamed('fees', 'collection', $billingSpec->getFees(), array(
                    'type' => new TierFeeFormType(),
                    'allow_add' => true,
                    'by_reference' => false,
                ))
            );

        }
        //Attach float form
        elseif( $billingSpec->getType() == BillingSpec::TYPE_FLAT ) {
            $event->getForm()->add(
                $this->factory->createNamed('fees', 'collection', $billingSpec->getFees(), array(
                    'type' => new FlatFeeFormType(),
                    'allow_add' => true,
                    'by_reference' => false,
                ))
            );
        }

    }

    public function getName()
    {
        return 'billing_spec';
    }
}
