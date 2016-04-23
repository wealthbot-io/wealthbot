<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 14.03.14
 * Time: 16:58
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Wealthbot\UserBundle\Entity\Profile;

class HouseholdBillingSettingsFormType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $client = $builder->getData();

        $builder
            ->add('billingSpec', 'entity', array(
                'class' => 'WealthbotAdminBundle:BillingSpec',
                'label' => 'Billing Spec: ',
                'property' => 'name',
                'property_path' => 'appointedBillingSpec',
                'query_builder' => function (EntityRepository $er) use ($client) {
                    return $er->createQueryBuilder('b')
                        ->where('b.owner = :ria')
                        ->setParameter('ria', $client->getRia());
                }
            ))
            ->add('paymentMethod', 'choice', array(
                'label' => 'Payment Method: ',
                'property_path' => 'profile.paymentMethod',
                'choices' => array(
                    Profile::PAYMENT_METHOD_DIRECT_DEBIT => 'Direct Debit',
                    Profile::PAYMENT_METHOD_OUTSIDE_PAYMENT => 'Outside Payment'
                )
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\UserBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'client_billing_settings';
    }
}
