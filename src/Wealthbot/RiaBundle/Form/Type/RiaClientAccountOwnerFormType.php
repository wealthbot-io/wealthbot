<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 13.05.13
 * Time: 14:55
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\RiaBundle\Form\EventListener\RiaClientAccountOwnerFormEventSubscriber;
use Wealthbot\UserBundle\Entity\User;

class RiaClientAccountOwnerFormType extends AbstractType
{
    private $client;
    private $account;
    private $isJoint;

    public function __construct(User $client, ClientAccount $account = null, $isJoint = false)
    {
        $this->client = $client;
        $this->account = $account;
        $this->isJoint = $isJoint;
    }

    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $subscriber = new RiaClientAccountOwnerFormEventSubscriber(
            $builder->getFormFactory(),
            $this->client,
            $this->account,
            $this->isJoint
        );

        $builder->addEventSubscriber($subscriber);
    }

    public function getBlockPrefix()
    {
        return 'account_owners';
    }
}
