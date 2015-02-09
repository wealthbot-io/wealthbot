<?php

namespace Wealthbot\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TransactionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('advisorCode', 'text')
            ->add('accountNumber', 'text')
            ->add('transactionCode', 'text')
            ->add('symbol', 'text')
            ->add('securityCode', 'text')
            ->add('closingMethod', 'text', array('required' => false))
            ->add('qty', 'text')
            ->add('grossAmount', 'text', array('required' => false))
            ->add('netAmount', 'text')
            ->add('txDate', 'text')
            ->add('settleDate', 'text')
            ->add('notes', 'textarea')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\AdminBundle\Document\Transaction'
        ));
    }

    public function getName()
    {
        return 'wealthbot_adminbundle_transaction_type';
    }
}