<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 29.08.13
 * Time: 15:38
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;


use Doctrine\ORM\EntityManager;
use Wealthbot\ClientBundle\Entity\TransferInformation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AccountTransferInformationFormType extends AbstractType
{
    /** @var \Doctrine\ORM\EntityManager  */
    private $em;

    /** @var  FormFactoryInterface */
    private $factory;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('is_firm_not_appear', 'checkbox', array('mapped' => false, 'required' => false))
            ->add('transfer_custodian_id', 'hidden', array('property_path' => false));

        $this->factory = $builder->getFormFactory();

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        $builder->addEventListener(FormEvents::PRE_BIND, array($this, 'onPreBind'));
        $builder->addEventListener(FormEvents::BIND, array($this, 'onBind'));
    }

    /**
     * On PreSetDate event handler
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) return;

        $transferCustodian = $data->getTransferCustodian();

        $form->add(
            $this->factory->createNamed('transfer_custodian_id', 'hidden', null, array(
                'data' => $transferCustodian ? $transferCustodian->getId() : null,
                'property_path' => false
            ))
        )->add(
            $this->factory->createNamed('is_firm_not_appear', 'checkbox', null, array(
                'mapped' => false,
                'data' => $transferCustodian ? false : true,
                'required' => false
            ))
        );
    }

    /**
     * On PreBind event handler
     *
     * @param FormEvent $event
     */
    public function onPreBind(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        $transferCustodian = null;

        if (null === $data) return;

        if (isset($data['transfer_custodian_id'])) {
            $transferCustodian = $this->em->getRepository('WealthbotClientBundle:TransferCustodian')->find(
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

    public function onBind(FormEvent $event)
    {
        /** @var TransferInformation $data */
        $data = $event->getData();
        $form = $event->getForm();

        $isFirmNotAppear = $form->get('is_firm_not_appear')->getData();
        if ($isFirmNotAppear) {
            $data->setTransferCustodian(null);
            //$data->setClientAccountId(null);
            $data->setIsIncludePolicy(null);
            $data->setQuestionnaireAnswer(array());
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\ClientBundle\Entity\TransferInformation'
        ));
    }

    public function getName()
    {
        return 'transfer_information';
    }
}