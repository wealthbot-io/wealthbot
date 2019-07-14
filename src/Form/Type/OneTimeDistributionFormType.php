<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 20.03.14
 * Time: 18:47.
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Nette\Neon\Entity;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OneTimeDistributionFormType extends AbstractType
{

    private $subscriber;



    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $client = $options['client'];
        $this->subscriber = $options['subscriber'];

        $builder->add('amount', MoneyType::class, [
            'attr' => ['class' => 'input-mini'],
            'currency' => 'USD',
            'label' => 'One Time Distribution',
        ]);

        $builder->add('bankInformation', EntityType::class, [
            'class' => 'App\\Entity\\BankInformation',
            'query_builder' => function (EntityRepository $er) use ($client) {
                return $er->createQueryBuilder('bi')
                    ->where('bi.client_id = :client_id')
                    ->setParameter('client_id', $client->getId());
            },
            'expanded' => true,
            'multiple' => false,
            'required' => false,
        ]);

        if (!is_null($this->subscriber)) {
            $builder->addEventSubscriber($this->subscriber);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Distribution',
            'client' => null,
            'subscriber' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'one_time_distribution_form';
    }
}
