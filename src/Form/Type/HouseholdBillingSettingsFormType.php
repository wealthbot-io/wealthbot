<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 14.03.14
 * Time: 16:58.
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Profile;

class HouseholdBillingSettingsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $client = $builder->getData();

        $builder
            ->add('billingSpec', EntityType::class, [
                'class' => 'App\\Entity\\BillingSpec',
                'label' => 'Billing Spec: ',
                'property_path' => 'appointedBillingSpec',
                'query_builder' => function (EntityRepository $er) use ($client) {
                    return $er->createQueryBuilder('b')
                        ->where('b.owner = :ria')
                        ->setParameter('ria', $client->getRia());
                },
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'label' => 'Payment Method: ',
                'property_path' => 'profile.paymentMethod',
                'choices' => [
                    'Direct Debit' => Profile::PAYMENT_METHOD_DIRECT_DEBIT,
                    'Outside Payment' => Profile::PAYMENT_METHOD_OUTSIDE_PAYMENT,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\User',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'client_billing_settings';
    }
}
