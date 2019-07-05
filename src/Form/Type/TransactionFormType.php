<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('advisorCode', TextType::class)
            ->add('accountNumber', TextType::class)
            ->add('transactionCode', TextType::class)
            ->add('symbol', TextType::class)
            ->add('securityCode', TextType::class)
            ->add('closingMethod', TextType::class, ['required' => false])
            ->add('qty', TextType::class)
            ->add('grossAmount', TextType::class, ['required' => false])
            ->add('netAmount', TextType::class)
            ->add('txDate', TextType::class)
            ->add('settleDate', TextType::class)
            ->add('notes', TextareaType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Document\Transaction',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_adminbundle_transaction_type';
    }
}
