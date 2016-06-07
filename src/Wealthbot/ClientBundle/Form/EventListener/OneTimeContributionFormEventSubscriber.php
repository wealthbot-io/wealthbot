<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 02.04.13
 * Time: 15:50
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\EventListener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Wealthbot\ClientBundle\Entity\OneTimeContribution;
use Wealthbot\ClientBundle\Entity\SystemAccount;
use Wealthbot\ClientBundle\Model\BaseContribution;

class OneTimeContributionFormEventSubscriber extends TransferFundingFormEventSubscriber
{
    private $systemAccount;

    public function __construct(FormFactoryInterface $factory, EntityManager $em, SystemAccount $systemAccount)
    {
        $this->systemAccount = $systemAccount;

        parent::__construct($factory, $em, $systemAccount->getClientAccount());
    }

    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();

        $typeChoices = [
            OneTimeContribution::TYPE_FUNDING_MAIL => 'Mail Check',
            OneTimeContribution::TYPE_FUNDING_BANK => 'Bank Transfer',
            OneTimeContribution::TYPE_FUNDING_WIRE => 'Wire Transfer',
            OneTimeContribution::TYPE_NOT_FUNDING => 'I will not be funding my account at this time',
        ];

        $form->add(
            $this->factory->createNamed('type', 'choice', null, [
                'choices' => $typeChoices,
                'data' => false,
                'expanded' => true,
                'multiple' => false,
                'required' => false,
                'auto_initialize' => false,
            ])
        );

        $form->add(
            $this->factory->createNamed('transaction_frequency', 'choice', null, [
                'choices' => [BaseContribution::TRANSACTION_FREQUENCY_ONE_TIME => 'One-time'],
                'expanded' => true,
                'multiple' => false,
                'data' => 1,
                'mapped' => false,
                'required' => false,
                'auto_initialize' => false,
            ])
        );

        parent::preSetData($event);
    }

    protected function addContributionYearField(FormInterface $form)
    {
        if ($this->systemAccount->isRothIraType() || $this->systemAccount->isTraditionalIraType()) {
            $form->add($this->factory->createNamed('contribution_year', 'text', null, ['required' => false, 'auto_initialize' => false]));
        }
    }
}
