<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 10.04.13
 * Time: 13:45
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\ClientBundle\Form\EventListener\ClientAccountOwnerFormEventSubscriber;
use Wealthbot\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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

    public function buildForm(FormBuilderInterface $builder, array $options = array())
    {
        $builder->addEventSubscriber(
            new ClientAccountOwnerFormEventSubscriber($builder->getFormFactory(), $this->client, $this->em, $this->isJoint)
        );
    }

    public function getName()
    {
        return 'account_owner';
    }
}