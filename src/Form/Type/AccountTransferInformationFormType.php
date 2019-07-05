<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 29.08.13
 * Time: 15:38
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\TransferInformation;

class AccountTransferInformationFormType extends AbstractType
{
    /** @var \Doctrine\ORM\EntityManager */
    private $em;

    /** @var FormFactoryInterface */
    private $factory;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->em = $options['em'];

        $builder->add('is_firm_not_appear', CheckboxType::class, ['mapped' => false, 'required' => false])
            ->add('transfer_custodian_id', HiddenType::class, []);

        $this->factory = $builder->getFormFactory();

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
    }

    /**
     * On PreSetDate event handler.
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $this->em = $form->getConfig()->getOption('em');

        if (null === $data) {
            return;
        }

        $transferCustodian = $data->getTransferCustodian();

        $form->add(
            $this->factory->createNamed('transfer_custodian_id', 'hidden', null, [
                'data' => $transferCustodian ? $transferCustodian->getId() : null,
                'mapped' => false,
                'auto_initialize' => false,
            ])
        )->add(
            $this->factory->createNamed('is_firm_not_appear', 'checkbox', null, [
                'mapped' => false,
                'data' => $transferCustodian ? false : true,
                'required' => false,
                'auto_initialize' => false,
            ])
        );
    }

    /**
     * On PreBind event handler.
     *
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $transferCustodian = null;

        if (null === $data) {
            return;
        }

        if (isset($data['transfer_custodian_id'])) {
            $transferCustodian = $this->em->getRepository('App\Entity\TransferCustodian')->find(
                $data['transfer_custodian_id']
            );
        }

        $isFirmNotAppear = isset($data['is_firm_not_appear']) ? (bool) $data['is_firm_not_appear'] : false;
        if (!$isFirmNotAppear && !$transferCustodian) {
            $form->get('transfer_custodian_id')->addError(
                new FormError('Select firm name from list or click checkbox above and enter the name of the unavailable firm.')
            );
        }
    }

    public function onSubmit(FormEvent $event)
    {
        /** @var TransferInformation $data */
        $data = $event->getData();
        $form = $event->getForm();

        $isFirmNotAppear = $form->get('is_firm_not_appear')->getData();
        if ($isFirmNotAppear) {
            $data->setTransferCustodian(null);
            //$data->setClientAccountId(null);
            $data->setIsIncludePolicy(null);
            $data->setQuestionnaireAnswer([]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\TransferInformation',
            'em' => null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'transfer_information';
    }
}
