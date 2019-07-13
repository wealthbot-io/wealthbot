<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 01.04.13
 * Time: 11:45
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\EventListener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use App\Entity\AccountContribution;
use App\Entity\SystemAccount;

class AccountContributionFormEventSubscriber extends TransferFundingFormEventSubscriber
{
    private $systemAccount;

    public function __construct(FormFactoryInterface $factory, EntityManager $em, SystemAccount $systemAccount)
    {
        $this->systemAccount = $systemAccount;

        parent::__construct($factory, $em, $systemAccount->getClientAccount());
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preBind',
        ];
    }

    public function preSetData(FormEvent $event)
    {
        /** @var $data AccountContribution */
        $data = $event->getData();
        $form = $event->getForm();

        $frequencyChoices = array_reverse(
            array_slice(AccountContribution::getTransactionFrequencyChoices(), 1, 3, true),
            true
        );

        if ($data && $data->getId()) {
            $frequency = $data->getTransactionFrequency() ? $data->getTransactionFrequency() : null;
        } else {
            $frequency = null;
        }

        $form->add($this->factory->createNamed('transaction_frequency', ChoiceType::class, null, [
            'choices' => $frequencyChoices,
            'expanded' => true,
            'multiple' => false,
            'data' => $frequency,
            'required' => false,
            'auto_initialize' => false,
        ]));

        $data->setType(AccountContribution::TYPE_FUNDING_BANK);

        parent::preSetData($event);
    }

    protected function addContributionYearField(FormInterface $form)
    {
        if ($this->systemAccount->isRothIraType() || $this->systemAccount->isTraditionalIraType()) {
            $form->add($this->factory->createNamed(
                'contribution_year',
                TextType::class,
                null,
                ['required' => false, 'auto_initialize' => false]
            ));
        }
    }
}
