<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Wealthbot\AdminBundle\Entity\CeModel;
use Wealthbot\AdminBundle\Repository\CeModelEntityRepository;
use Wealthbot\AdminBundle\Repository\CeModelRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormError;
use Wealthbot\UserBundle\Entity\User;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Doctrine\ORM\EntityManager;

class RiaCompanyInformationThreeType extends AbstractType
{
    /** @var \Doctrine\ORM\EntityManager $em */
    private $em;

    /** @var \Wealthbot\UserBundle\Entity\User $user */
    private $user;

    /** @var bool $isPreSave */
    private $isPreSave;

    /** @var bool $isModels */
    private $isModels;

    /** @var bool $isModels */
    private $isChangeProfile;

    public function __construct(
        EntityManager $em,
        User $user,
        $isPreSave = false,
        $isModels = true,
        $isChangeProfile = false
    ) {
        $this->em = $em;
        $this->user = $user;
        $this->isPreSave = $isPreSave;
        $this->isModels = $isModels;
        $this->isChangeProfile = $isChangeProfile;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var $repo CeModelRepository */
        $repo = $this->em->getRepository('WealthbotAdminBundle:CeModel');

        $session = $options['session'];
        /** @var $data RiaCompanyInformation */
        $data = $builder->getData();

        if ($this->isModels) {
            $portfolio = $data->getPortfolioModel();

            $strategyParentModels = $repo->getStrategyParentModels();
            $strategyChoices = array();
            foreach ($strategyParentModels as $item) {
                $strategyChoices[$item->getId()] = $item->getName();
            }

            $builder
                ->add('model_type', 'choice', array(
                    'choices'       => array(
                        //code_v2: NOT DELETE THIS CODE
                        //CeModel::TYPE_STRATEGY => 'Use a Strategists Models',
                        CeModel::TYPE_CUSTOM => 'Create your own models'
                    ),
                    'multiple'      => false,
                    'expanded'      => true,
                    'required'      => false,
                    'property_path' => false
                ))
                ->add('strategy_model', 'choice', array(
                    'choices'       => $strategyChoices,
                    'multiple'      => false,
                    'expanded'      => true,
                    'property_path' => false,
                    'required'      => false,
                    'data'          => ($portfolio && $portfolio->isStrategy() ? $portfolio->getId() : null)
                ));
        }

        if (!$this->isChangeProfile) {
            $builder->add('portfolio_processing', 'choice', array(
                'choices'  => RiaCompanyInformation::getPortfolioProcessingChoices(),
                'required' => false,
                'expanded' => true
            ));
        }

        $builder
            ->add('is_allow_retirement_plan', 'choice', array(
                'choices' => array(1 => 'Yes', 0 => 'No'),
                'required' => false,
                'expanded' => true,
                //#code_v2: NOT DELETE THIS CODE #
                'data' => 0
            ))
            ->add('account_managed', 'choice', array(
                'choices'  => $data->getAccountManagedChoices(),
                'required' => false,
                'expanded' => true
            ))
            ->add('is_use_qualified_models', 'choice', array(
                'choices'   => array('No', 'Yes'),
                'expanded'  => true,
                'multiple'  => false,
                'label'     => 'For clients who do not hold outside retirement accounts, will you be offering qualified and non-qualified models depending on the account type?',
                'required'  => false,
            ))
            ->add('rebalanced_method', 'choice', array(
                'choices'     => RiaCompanyInformation::$rebalanced_method_choices,
                'required'    => true,
                'empty_value' => 'Choose an Option',
                'expanded'    => false
            ))
            ->add('rebalanced_frequency', 'choice', array(
                'choices'     => RiaCompanyInformation::$rebalanced_frequency_choices,
                'required'    => true,
                'empty_value' => 'Choose an Option',
                'expanded'    => false
            ))
            ->add('use_municipal_bond', 'choice', array(
                'choices'  => array(1 => 'Yes', 0 => 'No'),
                'expanded' => true
            ))
            ->add('clients_tax_bracket', 'percent', array(
                'precision' => 0,
                'required'  => false
            ))
            ->add('transaction_amount', 'number', array(
                'precision' => 2,
                'required' => true
            ))
            ->add('transaction_amount_percent', 'percent', array(
                'precision' => 2,
                'required' => true
            ))
            ->add('is_transaction_fees', 'choice', array(
                'choices' => array(1 => 'Yes', 0 => 'No'),
                'required' => true,
                'expanded' => true,
                'data' => ($data->getId() && $data->getIsTransactionFees() !== null ? $data->getIsTransactionFees() : 1)
            ))
            ->add('is_transaction_minimums', 'choice', array(
                'choices' => array(1 => 'Yes', 0 => 'No'),
                'required' => true,
                'expanded' => true,
                'data' => ($data->getId() && $data->getIsTransactionMinimums() !== null ? $data->getIsTransactionMinimums() : 1)
            ))
            ->add('is_transaction_redemption_fees', 'choice', array(
                'choices' => array(1 => 'Yes', 0 => 'No'),
                'required' => true,
                'expanded' => true,
                'data' => ($data->getId() && $data->getIsTransactionRedemptionFees() !== null ? $data->getIsTransactionRedemptionFees() : 1)
            ))
            ->add('is_tax_loss_harvesting', 'choice', array(
                'choices' => array(1 => 'Yes', 0 => 'No'),
                'required' => true,
                'expanded' => true,
                'data' => (($data->getId() && $data->getIsTaxLossHarvesting() !== null) ? $data->getIsTaxLossHarvesting() : 1)
            ))
            ->add('tax_loss_harvesting', 'number', array(
                'precision' => 2,
                'required' => false,
                'grouping' => true
            ))
            ->add('stop_tlh_value', 'number', array(
                'precision' => 2,
                'required' => false,
                'grouping' => true,
            ))
            ->add('tax_loss_harvesting_percent', 'percent', array(
                'precision' => 2,
                'required' => false,
            ))
            ->add('tax_loss_harvesting_minimum', 'number', array(
                'precision' => 2,
                'grouping' => true,
                'required' => false,
            ))
            ->add('tax_loss_harvesting_minimum_percent', 'percent', array(
                'precision' => 2,
                'required' => false,
            ))
            ->add('tlh_buy_back_original', 'choice', array(
                'choices' => array(1 => 'Yes', 0 => 'No'),
                'data' => 0,
                'disabled' => true,
                'required' => true,
                'expanded' => true
            ))
        ;

        $factory = $builder->getFormFactory();
        $em = $this->em;
        $user = $this->user;

        $refreshSubclasses = function(FormInterface $form, $model) use ($factory, $user, $em){
            if($model){
                $riaSubclasses = $em->getRepository('WealthbotAdminBundle:Subclass')->findRiaSubclasses($user->getId());

                if(!$riaSubclasses){
                    $subclasses = $em->getRepository('WealthbotAdminBundle:Subclass')->findDefaultsByModelId($model->getid());

                    foreach ($subclasses as $item) {
                        $clone = $item->getCopy();
                        $clone->setOwner($user);

                        $riaSubclasses[] = $clone;
                    }
                }

                $form->add($factory->createNamed('subclasses', 'collection', null, array(
                    'type'          => new RiaSubclassType(),
                    'by_reference'  => false,
                    'property_path' => false,
                    'data'          => $riaSubclasses
                )));
            } else {
                $form->add($factory->createNamed('subclasses', 'collection', null, array(
                    'type'          => new RiaSubclassType(),
                    'by_reference'  => false,
                    'property_path' => false
                )));
            }
        };

        if (!$this->isChangeProfile) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($refreshSubclasses, $repo){
                $form = $event->getForm();
                $data = $event->getData();

                if($data === null || ($data && !$data->getPortfolioModelId())){
                    $refreshSubclasses($form, null);
                } else {
                    $refreshSubclasses($form, $repo->find($data->getPortfolioModelId()));
                }
            });
        }
        if (!$this->isPreSave) {
            if (!$this->isChangeProfile) {
                $this->addSubclassBindListener($builder, $refreshSubclasses, $repo);
            }
            $this->addOnBindValidator($builder);
            $this->addWarningFlashPreBindListener($builder, $session);
        }
    }


    protected function addWarningFlashPreBindListener(FormBuilderInterface $builder, $session)
    {
        /** @var FlashBag $flashBag */
        $flashBag = $session->getFlashBag();

        $builder->addEventListener(FormEvents::PRE_BIND, function (FormEvent $event) use ($flashBag) {
            $form = $event->getForm();
            $data = $event->getData();
            // Need show Alert message when RIA change Portfolio Managed
            if(isset($data['account_managed']) && $data['account_managed'] != 1
                && ( $form->getData()->getAccountManaged() == 1 && !$form->getData()->getIsAllowRetirementPlan())) {
                $flashBag->set('warning_change_profile', 'You may now set in which account types assets should be held.');
            }
            // Show alert message when RIA change Expected Asset
//            if(isset($data['is_show_client_expected_asset_class']) && $data['is_show_client_expected_asset_class'] == 1
//                && $form->getData()->getIsShowClientExpectedAssetClass() == 0) {
//                $flashBag->set('warning_change_profile', 'You may now set expected performance in the Categories section.');
//            }
        });
    }

    protected function addSubclassBindListener(FormBuilderInterface $builder, $refreshSubclasses, $repo)
    {
        $builder->addEventListener(FormEvents::PRE_BIND, function (FormEvent $event) use ($refreshSubclasses, $repo) {
            $form = $event->getForm();
            $data = $event->getData();

            if(isset($data['strategy_model'])) {
                $selectedModel = $repo->find($data['strategy_model']);
                if($selectedModel){
                    $refreshSubclasses($form, $selectedModel);
                }
            }
        });
    }

    protected function addOnBindValidator(FormBuilderInterface $builder)
    {
        $em = $this->em;
        $ria = $this->user;
        $isModels = $this->isModels;

        $builder->addEventListener(FormEvents::BIND, function (FormEvent $event) use ($em, $ria, $isModels) {

            /** @var $form */
            $form = $event->getForm();
            /** @var \Wealthbot\RiaBundle\Entity\RiaCompanyInformation $data */
            $data = $event->getData();

            /** @var $repo CeModelRepository */
            $repo = $em->getRepository('WealthbotAdminBundle:CeModel');

            /** @var $ceModelEntityRepo CeModelEntityRepository */
            $ceModelEntityRepo = $em->getRepository('WealthbotAdminBundle:CeModelEntity');

            if($isModels){
                $modelType = $form->get('model_type')->getData();

                if (!$modelType) {
                    $form->get('model_type')->addError(new FormError('Required.'));
                } else {

                    switch ($modelType) {
                        case CeModel::TYPE_STRATEGY:
                            $strategyParentModelId = $form->get('strategy_model')->getData();
                            if (!$strategyParentModelId) {
                                $form->get('strategy_model')->addError(new FormError('Please specify the model.'));
                            } else {
                                /** @var $strategyParentModel CeModel */
                                $strategyParentModel = $repo->find($strategyParentModelId);
                                if (!$strategyParentModel) {
                                    $form->get('strategy_model')->addError(new FormError('Model does not exist'));
                                }
                            }
                            break;
                        case CeModel::TYPE_CUSTOM:
                            break;
                        default:
                            $form->get('model_type')->addError(new FormError('Type does not exist.'));
                    }
                }
            }

            if ($form->has('portfolio_processing')) {
                $portfolioProcessing = $data->getPortfolioProcessing();
                if (is_null($portfolioProcessing) ||
                    !array_key_exists($portfolioProcessing, RiaCompanyInformation::getPortfolioProcessingChoices())) {

                    $form->get('portfolio_processing')->addError(new FormError('Invalid.'));
                }
            }

            if (!$data->getAccountManaged()) {
                $form->get('account_managed')->addError(new FormError('Required.'));
            } elseif ($data->isClientByClientManagedLevel() && $data->isStraightThroughProcessing()) {
                $form->get('account_managed')->addError(
                    new FormError('Must be Account or Household level for Straight Through portfolio processing.')
                );
            }

            if (!$data->getRebalancedMethod()) {
                $form->get('rebalanced_method')->addError(new FormError('Required.'));
            }

            if (!$data->getRebalancedFrequency()) {
                $form->get('rebalanced_frequency')->addError(new FormError('Required.'));
            }

            if ($data->getUseMunicipalBond() && !$data->getClientsTaxBracket()) {
                $form->get('clients_tax_bracket')->addError(new FormError('Required.'));
            }

//            if (!is_numeric($data->getIsShowClientExpectedAssetClass())) {
//                $form->get('is_show_client_expected_asset_class')->addError(new FormError('Required.'));
//            }

            if ($data->getIsTaxLossHarvesting() && !$data->getTaxLossHarvestingMinimumPercent()) {
                $form->get('tax_loss_harvesting_minimum_percent')->addError(new FormError('Required.'));
            }

            if (is_null($data->getIsAllowRetirementPlan())) {
                $form->get('is_allow_retirement_plan')->addError(new FormError('Required.'));
            }

            if (!$data->getUseMunicipalBond() && $ceModelEntityRepo->isMuniBondSecuritiesInRiaModels($ria->getId())) {
                $form->get('use_municipal_bond')->addError(new FormError('You have "municipal substitution" securities in model.'));
            }
//            isTaxLossHarvestingSecuritiesInModels
            if (!$data->getIsTaxLossHarvesting() && $ceModelEntityRepo->isTaxLossHarvestingSecuritiesInModels($ria->getId())) {
                $form->get('is_tax_loss_harvesting')->addError(new FormError('You have "tax loss harvesting" securities in model.'));
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'         => 'Wealthbot\RiaBundle\Entity\RiaCompanyInformation',
                'cascade_validation' => true
            )
        );
        $resolver->setRequired(array('session'));
    }

    public function getName()
    {
        return 'wealthbot_riabundle_riacompanyinformationtype';
    }
}
