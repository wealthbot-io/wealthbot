<?php

namespace App\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\CeModel;
use Repository\CeModelEntityRepository;
use Repository\CeModelRepository;
use App\Entity\RiaCompanyInformation;
use App\Entity\User;

class RiaCompanyInformationThreeType extends AbstractType
{
    /** @var \Doctrine\ORM\EntityManager $em */
    private $em;

    /** @param \App\Entity\User $user */
    private $user;

    /** @var bool $isPreSave */
    private $isPreSave;

    /** @var bool $isModels */
    private $isModels;

    /** @var bool $isModels */
    private $isChangeProfile;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->em = $options['em'];
        $this->user = $options['user'];
        $this->isPreSave = $options['isPreSave'];
        $this->isModels = $options['isModels'];
        $this->isChangeProfile = $options['isChangeProfile'];

        /** @var $repo CeModelRepository */
        $repo = $this->em->getRepository('App\Entity\CeModel');

        $session = $options['session'];
        /** @var $data RiaCompanyInformation */
        $data = $builder->getData();

        if ($this->isModels) {
            $portfolio = $data->getPortfolioModel();

            $strategyParentModels = $repo->getStrategyParentModels();
            $strategyChoices = [];
            foreach ($strategyParentModels as $item) {
                $strategyChoices[$item->getId()] = $item->getName();
            }

            $builder
                ->add('model_type', ChoiceType::class, [
                    'choices' => [
                        //code_v2: NOT DELETE THIS CODE
                        //CeModel::TYPE_STRATEGY => 'Use a Strategists Models',
                        CeModel::TYPE_CUSTOM => 'Create your own models',
                    ],
                    'multiple' => false,
                    'expanded' => true,
                    'required' => false,
                    'mapped' => false,
                ])
                ->add('strategy_model', ChoiceType::class, [
                    'choices' => $strategyChoices,
                    'multiple' => false,
                    'expanded' => true,
                    'mapped' => false,
                    'required' => false,
                    'data' => ($portfolio && $portfolio->isStrategy() ? $portfolio->getId() : null),
                ]);
        }

        if (!$this->isChangeProfile) {
            $builder->add('portfolio_processing', ChoiceType::class, [
                'choices' => RiaCompanyInformation::getPortfolioProcessingChoices(),
                'required' => false,
                'expanded' => true,
            ]);
        }

        $builder
            ->add('is_allow_retirement_plan', ChoiceType::class, [
                'choices' => ['Yes' => 1,  'No' => 0],
                'required' => false,
                'expanded' => true,
                //#code_v2: NOT DELETE THIS CODE #
                'data' => 0,
            ])
            ->add('account_managed', ChoiceType::class, [
                'choices' => $data->getAccountManagedChoices(),
                'required' => false,
                'expanded' => false,
            ])
            ->add('is_use_qualified_models', ChoiceType::class, [
                'choices' => ['No', 'Yes'],
                'expanded' => true,
                'multiple' => false,
                'label' => 'For clients who do not hold outside retirement accounts, will you be offering qualified and non-qualified models depending on the account type?',
                'required' => false,
            ])
            ->add('rebalanced_method', ChoiceType::class, [
                'choices' => RiaCompanyInformation::$rebalanced_method_choices,
                'required' => true,
                'placeholder' => 'Choose an Option',
                'expanded' => false,
            ])
            ->add('rebalanced_frequency', ChoiceType::class, [
                'choices' => RiaCompanyInformation::$rebalanced_frequency_choices,
                'required' => true,
                'placeholder' => 'Choose an Option',
                'expanded' => false,
            ])
            ->add('use_municipal_bond', ChoiceType::class, [
                'choices' => ['Yes' => 1,  'No' => 0],
                'expanded' => true,
            ])
            ->add('clients_tax_bracket', PercentType::class, [
                'scale' => 0,
                'required' => false,
            ])
            ->add('transaction_amount', MoneyType::class, [
                'scale' => 2,
                'required' => true,
                'currency' => 'USD'
            ])
            ->add('transaction_amount_percent', PercentType::class, [
                'scale' => 2,
                'required' => true,
            ])
            ->add('is_transaction_fees', ChoiceType::class, [
                'choices' => ['Yes' => 1,  'No' => 0],
                'required' => true,
                'expanded' => true,
                'data' => ($data->getId() && null !== $data->getIsTransactionFees() ? $data->getIsTransactionFees() : 1),
            ])
            ->add('is_transaction_minimums', ChoiceType::class, [
                'choices' => ['Yes' => 1,  'No' => 0],
                'required' => true,
                'expanded' => true,
                'data' => ($data->getId() && null !== $data->getIsTransactionMinimums() ? $data->getIsTransactionMinimums() : 1),
            ])
            ->add('is_transaction_redemption_fees', ChoiceType::class, [
                'choices' => ['Yes' => 1,  'No' => 0],
                'required' => true,
                'expanded' => true,
                'data' => ($data->getId() && null !== $data->getIsTransactionRedemptionFees() ? $data->getIsTransactionRedemptionFees() : 1),
            ])
            ->add('is_tax_loss_harvesting', ChoiceType::class, [
                'choices' => ['Yes' => 1,  'No' => 0],
                'required' => true,
                'expanded' => true,
                'data' => (($data->getId() && null !== $data->getIsTaxLossHarvesting()) ? $data->getIsTaxLossHarvesting() : 1),
            ])
            ->add('tax_loss_harvesting', MoneyType::class, [
                'scale' => 2,
                'required' => false,
                'grouping' => true,
                'currency' => 'USD'
            ])
            ->add('stop_tlh_value', MoneyType::class, [
                'scale' => 2,
                'required' => false,
                'currency' => 'USD',
                'grouping' => true,
            ])
            ->add('tax_loss_harvesting_percent', PercentType::class, [
                'scale' => 2,
                'required' => false,
            ])
            ->add('tax_loss_harvesting_minimum', MoneyType::class, [
                'scale' => 2,
                'grouping' => true,
                'required' => false,
                'currency' => 'USD'
            ])
            ->add('tax_loss_harvesting_minimum_percent', PercentType::class, [
                'scale' => 2,
                'required' => false,
            ])
            ->add('tlh_buy_back_original', ChoiceType::class, [
                'choices' => ['Yes' => 1,  'No' => 0],
                'data' => 0,
                'disabled' => true,
                'required' => true,
                'expanded' => true,
            ])

            ->add('is_use_qualified_models', ChoiceType::class, [
                'choices' => ['Yes' => 1,  'No' => 0],
                'data' => 0,
                'disabled' => true,
                'required' => true,
                'expanded' => true,
            ])

        ;

        $factory = $builder->getFormFactory();
        $em = $this->em;
        $user = $this->user;

        $refreshSubclasses = function (FormInterface $form, $model) use ($factory, $user, $em) {
            if ($model) {
                $riaSubclasses = $em->getRepository('App\Entity\Subclass')->findRiaSubclasses($user->getId());

                if (!$riaSubclasses) {
                    $subclasses = $em->getRepository('App\Entity\Subclass')->findDefaultsByModelId($model->getid());

                    foreach ($subclasses as $item) {
                        $clone = $item->getCopy();
                        $clone->setOwner($user);

                        $riaSubclasses[] = $clone;
                    }
                }

                $form->add($factory->createNamed('subclasses', 'collection', null, [
                    'type' => new RiaSubclassType(),
                    'by_reference' => false,
                    'mapped' => false,
                    'data' => $riaSubclasses,
                    'auto_initialize' => false,
                ]));
            } else {
                $form->add($factory->createNamed('subclasses', 'collection', null, [
                    'type' => new RiaSubclassType(),
                    'by_reference' => false,
                    'mapped' => false,
                    'auto_initialize' => false,
                ]));
            }
        };

        if (!$this->isChangeProfile) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($refreshSubclasses, $repo) {
                $form = $event->getForm();
                $data = $event->getData();

                if (null === $data || ($data && !$data->getPortfolioModelId())) {
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
            $this->addOnSubmitValidator($builder);
            $this->addWarningFlashPreBindListener($builder, $session);
        }
    }

    protected function addWarningFlashPreBindListener(FormBuilderInterface $builder, $session)
    {
        /** @var FlashBag $flashBag */
        $flashBag = $session->getFlashBag();

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($flashBag) {
            $form = $event->getForm();
            $data = $event->getData();
            // Need show Alert message when RIA change Portfolio Managed
            if (isset($data['account_managed']) && 1 !== $data['account_managed']
                && (1 === $form->getData()->getAccountManaged() && !$form->getData()->getIsAllowRetirementPlan())) {
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
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($refreshSubclasses, $repo) {
            $form = $event->getForm();
            $data = $event->getData();

            if (isset($data['strategy_model'])) {
                $selectedModel = $repo->find($data['strategy_model']);
                if ($selectedModel) {
                    $refreshSubclasses($form, $selectedModel);
                }
            }
        });
    }

    protected function addOnSubmitValidator(FormBuilderInterface $builder)
    {
        $em = $this->em;
        $ria = $this->user;
        $isModels = $this->isModels;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($em, $ria, $isModels) {
            /** @var $form */
            $form = $event->getForm();
            /** @param \App\Entity\RiaCompanyInformation $data */
            $data = $event->getData();

            /** @var $repo CeModelRepository */
            $repo = $em->getRepository('App\Entity\CeModel');

            /** @var $ceModelEntityRepo CeModelEntityRepository */
            $ceModelEntityRepo = $em->getRepository('App\Entity\CeModelEntity');

            if ($isModels) {
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'App\Entity\RiaCompanyInformation',
                'cascade_validation' => true,
                'em'=>null,
                'user'=>null,
                'isPreSave' => null,
                'isModels' => null,
                'isChangeProfile' => null,
                'session' => null
            ]
        );
        $resolver->setRequired(['session']);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_riabundle_riacompanyinformationtype';
    }
}
