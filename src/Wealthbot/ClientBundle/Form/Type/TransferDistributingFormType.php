<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 06.02.13
 * Time: 18:26
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;


use Wealthbot\ClientBundle\Entity\AccountContribution;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TransferDistributingFormType extends AbstractType
{
    private $isPreSaved;

    public function __construct($isPreSaved = false)
    {
        $this->isPreSaved = $isPreSaved;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('distribution_method', 'choice', array(
                'choices' => AccountContribution::getDistributionMethodChoices(),
                'expanded' => true,
                'multiple' => false,
                'required' => false
            ))
            ->add('has_federal_withholding', 'choice', array(
                'choices' => array(1 => 'Yes', 0 => 'No'),
                'expanded' => true,
                'multiple' => false,
                'required' => false
            ))
            ->add('percent_tax_rate', 'text', array(
                'required' => false
            ))
            ->add('money_tax_rate', 'text', array(
                'required' => false
            ))
        ;

        if (!$this->isPreSaved) {
            $builder->addEventListener(FormEvents::BIND, array($this, 'onBind'));
        }
    }

    public function onBind(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var $data AccountContribution */
        $data = $event->getData();

        $data->setType(AccountContribution::TYPE_DISTRIBUTING);

        if (!$data->getHasFederalWithholding()) {
            $data->setPercentTaxRate(null);
            $data->setMoneyTaxRate(null);
        }

        $data->setAccountOwnerFirstName(null);
        $data->setAccountOwnerMiddleName(null);
        $data->setAccountOwnerLastName(null);

        $data->setJointAccountOwnerFirstName(null);
        $data->setJointAccountOwnerMiddleName(null);
        $data->setJointAccountOwnerLastName(null);

        $data->setBankName(null);
        $data->setBankAccountTitle(null);
        $data->setBankPhoneNumber(null);

        $data->setStartTransferDate(null);
        $data->setAmount(null);
        $data->setTransactionFrequency(null);
        $data->setRoutingNumber(null);
        $data->setAccountNumber(null);
        $data->setAccountType(null);
        $data->setPdfCopy(null);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\ClientBundle\Entity\AccountContribution'
        ));
    }

    public function getName()
    {
        return 'transfer_distributing_type';
    }
}