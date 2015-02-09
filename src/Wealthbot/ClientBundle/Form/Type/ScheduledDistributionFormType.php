<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 18.02.14
 * Time: 15:41
 */

namespace Wealthbot\ClientBundle\Form\Type;


use Doctrine\ORM\EntityRepository;
use Wealthbot\ClientBundle\Entity\SystemAccount;
use Wealthbot\ClientBundle\Entity\Distribution;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ScheduledDistributionFormType extends AbstractType
{
    /** @var \Wealthbot\ClientBundle\Entity\SystemAccount */
    protected $account;

    /** @var \Symfony\Component\EventDispatcher\EventSubscriberInterface */
    protected $subscriber;

    /** @var  \Symfony\Component\Form\FormFactory */
    protected $factory;

    public function __construct(SystemAccount $account, EventSubscriberInterface $subscriber = null)
    {
        $this->account = $account;
        $this->subscriber = $subscriber;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->factory = $builder->getFormFactory();
        $client = $this->account->getClient();

        $builder->add('bankInformation', 'entity', array(
                'class' => 'WealthbotClientBundle:BankInformation',
                'query_builder' => function(EntityRepository $er) use ($client) {
                    return $er->createQueryBuilder('bi')
                        ->where('bi.client_id = :client_id')
                        ->setParameter('client_id', $client->getId());
                },
                'expanded' => true,
                'multiple' => false
            ))
            ->add('amount', 'number', array(
                'precision' => 2,
                'grouping' => true,
                'required' => false
            ));

        if (null !== $this->subscriber) {
            $builder->addEventSubscriber($this->subscriber);
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();

        if ($this->account->isRothIraType() || $this->account->isTraditionalIraType()) {
            $form->add($this->factory->createNamed('distribution_method', 'choice', null, array(
                    'choices' => Distribution::getDistributionMethodChoices(),
                    'expanded' => true,
                    'multiple' => false,
                    'required' => false
                )))
                ->add($this->factory->createNamed('federal_withholding', 'choice', null, array(
                    'choices' => Distribution::getFederalWithholdingChoices(),
                    'expanded' => true,
                    'multiple' => false,
                    'required' => false
                )))
                ->add($this->factory->createNamed('state_withholding', 'choice', null, array(
                    'choices' => Distribution::getStateWithholdingChoices(),
                    'expanded' => true,
                    'multiple' => false,
                    'required' => false
                )))
                ->add($this->factory->createNamed('federal_withhold_percent', 'percent', null, array(
                    'required' => false
                )))
                ->add($this->factory->createNamed('federal_withhold_money', 'number', null, array(
                    'precision' => 2,
                    'grouping' => true,
                    'required' => false
                )))
                ->add($this->factory->createNamed('state_withhold_percent', 'percent', null, array(
                    'required' => false
                )))
                ->add($this->factory->createNamed('state_withhold_money', 'number', null, array(
                    'precision' => 2,
                    'grouping' => true,
                    'required' => false
                )))
                ->add($this->factory->createNamed('residenceState', 'entity', null, array(
                    'class' => 'WealthbotAdminBundle:State',
                    'label' => 'State',
                    'empty_value' => 'Select a State',
                    'required' => false
                )));
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\ClientBundle\Entity\Distribution'
        ));
    }

    public function getName()
    {
        return 'distribution';
    }

} 