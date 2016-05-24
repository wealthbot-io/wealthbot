<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 05.10.12
 * Time: 16:16
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Wealthbot\ClientBundle\Entity\AccountGroup;
use Wealthbot\ClientBundle\Entity\ClientAccount;
use Wealthbot\UserBundle\Entity\User;

class ClientAccountFormType extends AbstractType
{
    /** @var \Wealthbot\UserBundle\Entity\User $client */
    protected $client;
    protected $group;
    protected $validateAdditionalFields;

    protected $isAllowRetirementPlan;
    protected $contributionTypes;

    public function __construct(User $client, $group = AccountGroup::GROUP_EMPLOYER_RETIREMENT, $validateAdditionalFields = true)
    {
        $this->client = $client;
        $this->group = $group;
        $this->isAllowRetirementPlan = $client->getProfile()->getRia()->getRiaCompanyInformation()->getIsAllowRetirementPlan();
        $this->validateAdditionalFields = $validateAdditionalFields;

        $this->contributionTypes = [
            'contributions' => 'Contributions',
            'distributions' => 'Distributions',
            'neither' => 'Neither',
        ];
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $factory = $builder->getFormFactory();

        switch ($this->group) {
            case AccountGroup::GROUP_FINANCIAL_INSTITUTION:
                $this->buildFormForFinancialInstitution($builder);
                break;

            case AccountGroup::GROUP_DEPOSIT_MONEY:
                $this->buildFormForDepositMoney($builder);
                break;

            case AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT:
                $this->buildFormForOldEmployerRetirement($builder);
                break;

            case AccountGroup::GROUP_EMPLOYER_RETIREMENT:
                $this->buildFormForEmployerRetirement($builder);
                break;

            default:
                $this->buildFormForManually($builder);
                break;
        }

        $contributionTypes = $this->contributionTypes;

        $updateFields = function (FormInterface $form, $type, $group, $data = null) use ($factory, $contributionTypes) {
            if ($type === 'contributions') {
                $form->add($factory->createNamed('monthly_contributions', 'number', null, [
                    'grouping' => true,
                    'precision' => 2,
                    'label' => 'Estimated Monthly Contributions',
                    'required' => false,
                    'attr' => ['value' => $data],
                    'auto_initialize' => false,
                ]));
            } elseif ($type === 'distributions' && $group !== AccountGroup::GROUP_EMPLOYER_RETIREMENT) {
                $form->add($factory->createNamed('monthly_distributions', 'number', null, [
                    'grouping' => true,
                    'precision' => 2,
                    'label' => 'Estimated Monthly Distributions',
                    'required' => false,
                    'attr' => ['value' => $data],
                    'auto_initialize' => false,
                ]));
            }

            $form->remove('contribution_type');
        };

        if (in_array($this->group, AccountGroup::getGroupChoices())) {
            $group = $this->group;

            $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($group, $updateFields) {
                $data = $event->getData();
                $form = $event->getForm();

                if (array_key_exists('contribution_type', $data)) {
                    $updateFields($form, $data['contribution_type'], $group);
                } else {
                    if (array_key_exists('monthly_contributions', $data)) {
                        $updateFields($form, 'contributions', $group, $data['monthly_contributions']);
                    } elseif (array_key_exists('monthly_distributions', $data)) {
                        $updateFields($form, 'distributions', $group, $data['monthly_distributions']);
                    }
                }
            });

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($factory, $group, $updateFields, $contributionTypes) {
                $form = $event->getForm();
                $data = $event->getData();

                if ($data && $data->getId()) {
                    if ($data->getMonthlyContributions()) {
                        $type = 'contributions';
                        $value = $data->getMonthlyContributions();
                    } elseif ($data->getMonthlyDistributions()) {
                        $type = 'distributions';
                        $value = $data->getMonthlyDistributions();
                    } else {
                        $type = 'neither';
                        $value = null;
                    }

                    $updateFields($form, $type, $group, $value);
                } else {
                    if ($group === AccountGroup::GROUP_EMPLOYER_RETIREMENT) {
                        unset($contributionTypes['distributions']);
                        $contributionTypes['neither'] = 'None';
                    }

                    $form->add($factory->createNamed('contribution_type', 'choice', null, [
                        'mapped' => false,
                        'choices' => $contributionTypes,
                        'expanded' => true,
                        'multiple' => false,
                        'auto_initialize' => false,
                    ]));
                }
            });
        }

        $this->onBindProcess($builder);
    }

    protected function buildFormForFinancialInstitution(FormBuilderInterface $builder)
    {
        $group = $this->group;
        $isAllowRetirementPlan = $this->isAllowRetirementPlan;

        $builder->add('groupType', 'entity', [
            'class' => 'Wealthbot\\ClientBundle\\Entity\\AccountGroupType',
            'query_builder' => function (EntityRepository $er) use ($group, $isAllowRetirementPlan) {
                $qb = $er->createQueryBuilder('gt');
                $qb
                    ->leftJoin('gt.group', 'g')
                    ->where('g.name = :group')
                    ->setParameter('group', AccountGroup::GROUP_FINANCIAL_INSTITUTION)
                ;

                return $qb;
            },
            'property' => 'type.name',
            'label' => 'Account Type:',
            'placeholder' => 'Select Type',
            'constraints' => [new NotBlank()],
        ]);

        $builder
            ->add('financial_institution', 'text', [
                'constraints' => [new NotBlank()],
                'label' => 'Financial Institution:',
            ])
            ->add('value', 'number', [
                'grouping' => true,
                'precision' => 2,
                'label' => 'Estimated Value',
                'constraints' => [new NotBlank()],
            ]);
    }

    protected function buildFormForDepositMoney(FormBuilderInterface $builder)
    {
        $group = $this->group;
        $isAllowRetirementPlan = $this->isAllowRetirementPlan;

        $builder->add('groupType', 'entity', [
            'class' => 'Wealthbot\\ClientBundle\\Entity\\AccountGroupType',
            'query_builder' => function (EntityRepository $er) use ($group, $isAllowRetirementPlan) {
                $qb = $er->createQueryBuilder('gt');
                $qb
                    ->leftJoin('gt.group', 'g')
                    ->where('g.name = :group')
                    ->setParameter('group', AccountGroup::GROUP_DEPOSIT_MONEY)
                ;

                return $qb;
            },
            'property' => 'type.name',
            'label' => 'Account Type',
            'placeholder' => 'Select Type',
        ]);

        $builder->add('value', 'number', [
            'grouping' => true,
            'precision' => 2,
            'label' => 'Estimated Deposit',
            'constraints' => [new NotBlank()],
        ]);
    }

    protected function buildFormForOldEmployerRetirement(FormBuilderInterface $builder)
    {
        $group = $this->group;
        $isAllowRetirementPlan = $this->isAllowRetirementPlan;

        $builder
            ->add('financial_institution', 'text', [
                'label' => 'Former Employer:',
            ])
            ->add('groupType', 'entity', [
                'class' => 'Wealthbot\\ClientBundle\\Entity\\AccountGroupType',
                'query_builder' => function (EntityRepository $er) use ($group, $isAllowRetirementPlan) {
                    $qb = $er->createQueryBuilder('gt');
                    $qb
                        ->leftJoin('gt.group', 'g')
                        ->leftJoin('gt.type', 't')
                        ->where('g.name = :group')
                        ->setParameter('group', AccountGroup::GROUP_OLD_EMPLOYER_RETIREMENT)
                        ->orderBy('t.id', 'asc')
                    ;

                    return $qb;
                },
                'property' => 'type.name',
                'label' => 'Account Type',
                'placeholder' => 'Select Type',
            ])
            ->add('value', 'number', [
                'grouping' => true,
                'precision' => 2,
                'label' => 'Estimated Value',
            ]);
    }

    protected function buildFormForEmployerRetirement(FormBuilderInterface $builder)
    {
        $group = $this->group;
        $isAllowRetirementPlan = $this->isAllowRetirementPlan;

        /** @var $data ClientAccount */
        $data = $builder->getData();
        if ($data && $data->getId()) {
            $employerFinancialInstitution = explode('(', $data->getFinancialInstitution());

            $provider = trim($employerFinancialInstitution[0]);
            $company = trim($employerFinancialInstitution[1], ' )');
        } else {
            $provider = null;
            $company = null;
        }

        $builder->add('financial_institution', 'text', [
                'label' => 'Employer Name',
                'data' => $company,
            ])
            ->add('plan_provider', 'text', [
                'label' => 'Retirement Plan Provider',
                'mapped' => false,
                'data' => $provider,
            ])
            ->add('groupType', 'entity', [
                'class' => 'Wealthbot\\ClientBundle\\Entity\\AccountGroupType',
                'query_builder' => function (EntityRepository $er) use ($group, $isAllowRetirementPlan) {
                    $qb = $er->createQueryBuilder('gt');
                    $qb
                        ->leftJoin('gt.group', 'g')
                        ->leftJoin('gt.type', 't')
                        ->where('g.name = :group')
                        ->setParameter('group', AccountGroup::GROUP_EMPLOYER_RETIREMENT)
                        ->orderBy('t.id', 'asc')
                    ;

                    return $qb;
                },
                'property' => 'type.name',
                'label' => 'Account Type',
                'placeholder' => 'Select Type',
            ])
            ->add('value', 'number', [
                'grouping' => true,
                'precision' => 2,
                'label' => 'Estimated Value',
            ]);
    }

    protected function buildFormForManually(FormBuilderInterface $builder)
    {
        $group = $this->group;
        $isAllowRetirementPlan = $this->isAllowRetirementPlan;

        $builder->add('financial_institution', 'text', [
                'label' => 'Financial Institution:',
            ])
            ->add('groupType', 'entity', [
                'class' => 'Wealthbot\\ClientBundle\\Entity\\AccountGroupType',
                'query_builder' => function (EntityRepository $er) use ($group, $isAllowRetirementPlan) {
                    $qb = $er->createQueryBuilder('gt');
                    if (!$isAllowRetirementPlan) {
                        $qb
                            ->leftJoin('gt.group', 'g')
                            ->where('g.name = :group_1 OR g.name = :group_2')
                            ->setParameters([
                                'group_1' => AccountGroup::GROUP_FINANCIAL_INSTITUTION,
                                'group_2' => AccountGroup::GROUP_DEPOSIT_MONEY,
                            ])
                        ;
                    }

                    return $qb;
                },
                'property' => 'type.name',
                'label' => 'Account Type:',
                'placeholder' => 'Select Type',
            ])
            ->add('value', 'number', [
                'grouping' => true,
                'precision' => 2,
                'label' => 'Estimated Value',
            ]);
    }

    protected function onBindProcess(FormBuilderInterface $builder)
    {
        $client = $this->client;
        $group = $this->group;
        $validateAdditionalFields = $this->validateAdditionalFields;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($client, $group, $validateAdditionalFields) {
            $form = $event->getForm();
            /** @var $data \Wealthbot\ClientBundle\Entity\ClientAccount */
            $data = $event->getData();

            $data->setClient($client);

            if ($group === AccountGroup::GROUP_EMPLOYER_RETIREMENT) {
                $data->setMonthlyDistributions(null);

                if (floatval($data->getValue()) < 50000) {
                    $form->get('value')->addError(new FormError('Minimum value must be $50,000 for retirement plans.'));
                }

                if ($form->get('plan_provider')->getData()) {
                    $financialInstitution = $data->getFinancialInstitution();
                    $data->setFinancialInstitution($form->get('plan_provider')->getData().' ('.$financialInstitution.')');
                }
            }

            if ($validateAdditionalFields) {
                $min = 1;

                if ($form->has('monthly_contributions')) {
                    $contributions = $data->getMonthlyContributions();

                    if (null === $contributions || trim($contributions === '')) {
                        $form->get('monthly_contributions')->addError(new FormError('This value should not be blank.'));
                    } elseif ($contributions < $min) {
                        $form->get('monthly_contributions')->addError(
                            new FormError(sprintf('This value cannot be less than %s.', $min))
                        );
                    }
                }

                if ($form->has('monthly_distributions')) {
                    $distributions = $data->getMonthlyDistributions();

                    if (null === $distributions || trim($distributions === '')) {
                        $form->get('monthly_distributions')->addError(new FormError('This value should not be blank.'));
                    } elseif ($distributions < $min) {
                        $form->get('monthly_distributions')->addError(
                            new FormError(sprintf('This value cannot be less than %s.', $min))
                        );
                    }
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\ClientBundle\Entity\ClientAccount',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_userbundle_client_account_type';
    }
}
