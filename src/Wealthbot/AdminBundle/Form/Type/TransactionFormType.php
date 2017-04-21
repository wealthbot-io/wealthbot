<?php

namespace Wealthbot\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('closingMethod', 'text', ['required' => false])
            ->add('qty', 'text')
            ->add('grossAmount', 'text', ['required' => false])
            ->add('netAmount', 'text')
            ->add('txDate', 'text')
            ->add('settleDate', 'text')
            ->add('notes', 'textarea')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\AdminBundle\Document\Transaction',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_adminbundle_transaction_type';
    }
}
