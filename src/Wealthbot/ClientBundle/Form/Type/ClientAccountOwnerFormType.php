<?php

namespace Wealthbot\ClientBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Wealthbot\ClientBundle\Form\EventListener\ClientAccountOwnerFormEventSubscriber;
use Wealthbot\UserBundle\Entity\User;

class ClientAccountOwnerFormType extends AbstractType
{
    private $client;
    private $em;
    private $isJoint;

    public function __construct(User $client, EntityManager $em, $isJoint = false)
    {
        $this->client = $client;
        $this->em = $em;
        $this->isJoint = $isJoint;
    }

    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $builder->addEventSubscriber(
            new ClientAccountOwnerFormEventSubscriber($builder->getFormFactory(), $this->client, $this->em, $this->isJoint)
        );
    }

    public function getBlockPrefix()
    {
        return 'account_owner';
    }
}
