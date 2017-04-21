<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 27.03.13
 * Time: 18:30
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Wealthbot\ClientBundle\Entity\SystemAccount;

class AccountContributionFormType extends  TransferFundingFormType
{
    public function __construct(EntityManager $em, SystemAccount $account, EventSubscriberInterface $subscriber = null)
    {
        parent::__construct($em, $account->getClientAccount(), $subscriber, false);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('type');
        $builder->remove('transaction_frequency');
    }
}
