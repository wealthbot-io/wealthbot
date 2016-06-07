<?php
/**
 * Created by PhpStorm.
 * User: countzero
 * Date: 14.03.14
 * Time: 16:57.
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\ClientBundle\Entity\ClientPortfolio;
use Wealthbot\UserBundle\Entity\Profile;

class HouseholdPortfolioSettingsFormType extends AbstractType
{
    protected $em, $factory;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $client = $builder->getData();
        $this->factory = $builder->getFormFactory();
        /** @var \Wealthbot\UserBundle\Entity\User $ria */
        $ria = $client->getRia();

        $groups = [];
        foreach ($ria->getOwnGroups() as $group) {
            $groups[$group->getId()] = $group->getName();
        }
        $selectedGroupId = 0;
        $selectedGroup = $client->getGroups()->first();
        if ($selectedGroup) {
            $selectedGroupId = $selectedGroup->getId();
        }

        $builder
            ->add('rebalancingLevel', 'choice', [
                'attr' => ['class' => 'input-medium'],
                'choices' => Profile::$client_account_managed_choices,
                'label' => 'Rebalancing Level: ',
                'property_path' => 'profile.clientAccountManaged',
            ])
        ;

        $builder
            ->add('annualIncome', 'choice', [
                'attr' => ['class' => 'input-large'],
                'choices' => Profile::getAnnualIncomeChoices(),
                'placeholder' => 'Choose an Option',
                'label' => 'Annual Income',
                'property_path' => 'profile.annualIncome',
            ])
            ->add('estimatedIncomeTax', 'percent', [
                'attr' => ['class' => 'input-mini'],
                'precision' => 0,
                'required' => false,
                'label' => 'Income tax bracket',
                'property_path' => 'profile.estimatedIncomeTax',
            ])
            ->add('liquidNetWorth', 'choice', [
                'attr' => ['class' => 'input-large'],
                'choices' => Profile::getLiquidNetWorthChoices(),
                'placeholder' => 'Choose an Option',
                'label' => 'Liquid Net Worth',
                'property_path' => 'profile.liquidNetWorth',
            ])
            ->add('group', 'choice', [
                'attr' => ['class' => 'input-medium'],
                'choices' => $groups,
                'data' => $selectedGroupId,
                'placeholder' => '',
                'label' => 'Advisor Set: ',
                'mapped' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPresetData']);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmitData']);
    }

    public function onPresetData(FormEvent $event)
    {
        $form = $event->getForm();
        $client = $event->getData();
        /** @var \Wealthbot\ClientBundle\Entity\ClientSettings $clientSettings */
        $clientSettings = $client->getClientSettings();

        if ($clientSettings && (null !== $clientSettings->getStopTlhValue())) {
            $stopTlhValue = $clientSettings->getStopTlhValue();
        } else {
            /** @var \Wealthbot\RiaBundle\Entity\RiaCompanyInformation $riaCompanyInfo */
            $riaCompanyInfo = $client->getRiaCompanyInformation();
            $stopTlhValue = $riaCompanyInfo && $riaCompanyInfo->getStopTlhValue() ? $riaCompanyInfo->getStopTlhValue() : 0;
        }

        $performanceInception = new \DateTime();
        /** @var \Wealthbot\ClientBundle\Entity\SystemAccount $systemAccount*/
        foreach ($client->getSystemAccounts() as $systemAccount) {
            if ($systemAccount->getPerformanceInception()) {
                $performanceInception = $systemAccount->getPerformanceInception();
            } else {
                /** @var \Wealthbot\AdminBundle\Entity\RebalancerAction $rebalancerAction */
                $rebalancerAction = $systemAccount->getRebalancerActions()->first();
                if ($rebalancerAction &&
                    $performanceInception > $date = $rebalancerAction->getStartedAt()) {
                    $performanceInception = $date;
                }
            }
        }

        $portfolios = [];
        $activePortfolio = null;
        foreach ($client->getClientPortfolios() as $portfolio) {
            if (ClientPortfolio::STATUS_CLIENT_ACCEPTED === $portfolio->getStatus()) {
                if ($portfolio->getIsActive()) {
                    $activePortfolio = $portfolio;
                }
                $portfolios[$portfolio->getId()] = $portfolio->getPortfolio()->getName();
            }
        }

        $form
            ->add($this->factory->createNamed('stopTlhValue', 'number', $stopTlhValue, [
                'attr' => ['class' => 'input-mini'],
                'label' => 'Tax Loss Harvesting Stop: ',
                'property_path' => 'clientSettings.stopTlhValue',
                'required' => false,
                'precision' => 2,
                'grouping' => true,
                'auto_initialize' => false,
            ]))
            ->add($this->factory->createNamed('performanceInception', 'date', $performanceInception, [
                'attr' => ['class' => 'input-small'],
                'format' => 'MM-dd-yy',
                'label' => 'Performance Inception: ',
                'mapped' => false,
                'read_only' => true,
                'required' => false,
                'widget' => 'single_text',
                'auto_initialize' => false,
            ]))
            ->add($this->factory->createNamed('portfolio', 'choice', $activePortfolio->getId(), [
                'attr' => ['class' => 'input-medium'],
                'choices' => $portfolios,
                'label' => 'Portfolio: ',
                'mapped' => false,
                'auto_initialize' => false,
            ]))
        ;
    }

    public function onSubmitData(FormEvent $event)
    {
        $form = $event->getForm();
        $groupId = $form->get('group')->getData();
        $client = $event->getData();

        foreach ($client->getClientPortfolios() as $portfolio) {
            if ($portfolio->getId() === $form->get('portfolio')->getData() && ClientPortfolio::STATUS_CLIENT_ACCEPTED === $portfolio->getStatus()) {
                $portfolio->setIsActive(true);
            } else {
                $portfolio->setIsActive(false);
            }
        }

        $group = $this->em->getRepository('WealthbotUserBundle:Group')
            ->findOneBy(['id' => $groupId, 'owner' => $client->getRia()]);
        if (null === $group) {
            $group = [];
        }

        $client->setGroups($group);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\UserBundle\Entity\User',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'household_portfolio_settings';
    }
}
