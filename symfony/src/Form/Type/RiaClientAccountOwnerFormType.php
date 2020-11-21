<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 13.05.13
 * Time: 14:55
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\ClientAccount;
use App\Form\EventListener\RiaClientAccountOwnerFormEventSubscriber;
use App\Entity\User;

class RiaClientAccountOwnerFormType extends AbstractType
{
    private $client;
    private $account;
    private $isJoint;

    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $this->client =  $options['client'];
        $this->account = $options['account'];
        $this->isJoint = $options['isJoint'] ?? false;


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
