<?php

namespace App\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\EventListener\ClientAccountOwnerFormEventSubscriber;
use App\Entity\User;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientAccountOwnerFormType extends AbstractType
{
    private $client;
    private $em;
    private $isJoint;

    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $this->client = $options['client'];
        $this->em = $options['em'];
        $this->isJoint = $options['isJoint'];


        $builder->addEventSubscriber(
            new ClientAccountOwnerFormEventSubscriber($builder->getFormFactory(), $this->client, $this->em, $this->isJoint)
        );
    }

    public function getBlockPrefix()
    {
        return 'account_owner';
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'client' => null,
            'em' => null,
            'isJoint' => null
        ]);
    }
}
