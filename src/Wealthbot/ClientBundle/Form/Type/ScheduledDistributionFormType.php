<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 18.02.14
 * Time: 15:41.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\ClientBundle\Entity\Distribution;
use Wealthbot\ClientBundle\Entity\SystemAccount;

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

        $builder->add('bankInformation', 'entity', [
                'class' => 'Wealthbot\\ClientBundle\\Entity\\BankInformation',
                'query_builder' => function (EntityRepository $er) use ($client) {
                    return $er->createQueryBuilder('bi')
                        ->where('bi.client_id = :client_id')
                        ->setParameter('client_id', $client->getId());
                },
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('amount', 'number', [
                'precision' => 2,
                'grouping' => true,
                'required' => false,
            ]);

        if (null !== $this->subscriber) {
            $builder->addEventSubscriber($this->subscriber);
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData']);
    }

    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();

        if ($this->account->isRothIraType() || $this->account->isTraditionalIraType()) {
            $form->add($this->factory->createNamed('distribution_method', 'choice', null, [
                    'choices' => Distribution::getDistributionMethodChoices(),
                    'expanded' => true,
                    'multiple' => false,
                    'required' => false,
                'auto_initialize' => false,
                ]))
                ->add($this->factory->createNamed('federal_withholding', 'choice', null, [
                    'choices' => Distribution::getFederalWithholdingChoices(),
                    'expanded' => true,
                    'multiple' => false,
                    'required' => false,
                    'auto_initialize' => false,
                ]))
                ->add($this->factory->createNamed('state_withholding', 'choice', null, [
                    'choices' => Distribution::getStateWithholdingChoices(),
                    'expanded' => true,
                    'multiple' => false,
                    'required' => false,
                    'auto_initialize' => false,
                ]))
                ->add($this->factory->createNamed('federal_withhold_percent', 'percent', null, [
                    'required' => false,
                    'auto_initialize' => false,
                ]))
                ->add($this->factory->createNamed('federal_withhold_money', 'number', null, [
                    'precision' => 2,
                    'grouping' => true,
                    'required' => false,
                    'auto_initialize' => false,
                ]))
                ->add($this->factory->createNamed('state_withhold_percent', 'percent', null, [
                    'required' => false,
                    'auto_initialize' => false,
                ]))
                ->add($this->factory->createNamed('state_withhold_money', 'number', null, [
                    'precision' => 2,
                    'grouping' => true,
                    'required' => false,
                    'auto_initialize' => false,
                ]))
                ->add($this->factory->createNamed('residenceState', 'entity', null, [
                    'class' => 'Wealthbot\\AdminBundle\\Entity\\State',
                    'label' => 'State',
                    'placeholder' => 'Select a State',
                    'required' => false,
                    'auto_initialize' => false,
                ]));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\ClientBundle\Entity\Distribution',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'distribution';
    }
}
