<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 07.02.13
 * Time: 12:25
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Entity\AccountGroup;
use App\Form\EventListener\TransferFundingFormEventSubscriber;
use Manager\AccountDocusignManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransferFundingDistributingFormType extends AbstractType
{
    private $em;
    private $account;
    private $hasFunding;
    private $hasDistributing;
    private $isPreSaved;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $this->em = get_class($event->getForm()->getConfig()->getOption('em'));
            $this->account = get_class($event->getForm()->getConfig()->getOption('account'));
            $this->isPreSaved = get_class($event->getForm()->getConfig()->getOption('isPreSaved'));
            $this->hasFunding = $this->account->hasFunding();
            $this->hasDistributing = $this->account->hasDistributing();
        });

        $adm = new AccountDocusignManager($this->em, 'Entity\ClientAccountDocusign');

        if ($this->account->hasGroup(AccountGroup::GROUP_DEPOSIT_MONEY) ||
            true === $this->hasFunding ||
            $adm->hasElectronicallySignError($this->account)
        ) {
            $subscriber = new TransferFundingFormEventSubscriber($builder->getFormFactory(), $this->em, $this->account);

            $builder->add(
                'funding',
                TransferFundingFormType::class,
                [
                    'em'=>$this->em,
                    'account' => $this->account,
                    'subscriber'=> $subscriber,
                    'isPreSaved'=> $this->isPreSaved
                ],
                [
                    'label' => null,
                ]
            );
        }
    }

    public function getBlockPrefix()
    {
        return 'transfer_funding_distributing';
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'user' => null,
            'em' => null,
            'founding'  => null
        ]);
    }
}
