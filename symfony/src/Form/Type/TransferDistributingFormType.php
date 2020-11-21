<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 06.02.13
 * Time: 18:26
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\AccountContribution;

class TransferDistributingFormType extends AbstractType
{
    private $isPreSaved;


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $this->isPreSaved = get_class($event->getForm()->getConfig()->getOption('isPreSaved'));
        });

        $builder
            ->add('distribution_method', ChoiceType::class, [
                'choices' => AccountContribution::getDistributionMethodChoices(),
                'expanded' => true,
                'multiple' => false,
                'required' => false,
            ])
            ->add('has_federal_withholding', ChoiceType::class, [
                'choices' => [1 => 'Yes', 0 => 'No'],
                'expanded' => true,
                'multiple' => false,
                'required' => false,
            ])
            ->add('percent_tax_rate', TextType::class, [
                'required' => false,
            ])
            ->add('money_tax_rate', TextType::class, [
                'required' => false,
            ])
        ;

        if (!$this->isPreSaved) {
            $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
        }
    }

    public function onSubmit(FormEvent $event)
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\AccountContribution',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'transfer_distributing_type';
    }
}
