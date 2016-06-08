<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 02.09.13
 * Time: 16:47
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Wealthbot\ClientBundle\Entity\AccountGroup;
use Wealthbot\ClientBundle\Entity\AccountGroupType;
use Wealthbot\UserBundle\Entity\User;

class TypedClientAccountFormType extends ClientAccountFormType
{
    private $em;
    private $groupType;

    public function __construct(EntityManager $em, User $client, AccountGroupType $groupType = null, $group = AccountGroup::GROUP_EMPLOYER_RETIREMENT, $validateAdditionalFields = true)
    {
        $this->em = $em;
        $this->groupType = $groupType;

        parent::__construct($client, $group, $validateAdditionalFields);
    }

    protected function buildFormForFinancialInstitution(FormBuilderInterface $builder)
    {
        if ($this->groupType) {
            $builder->add('groupType', 'hidden', [
                'data' => $this->groupType->getId(),
                'mapped' => false,
            ]);
        } else {
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
        }

        $builder
            ->add('financial_institution', 'text', [
                'constraints' => [new NotBlank()],
                'label' => 'Financial Institution:',
            ])
            ->add('transferInformation', new AccountTransferInformationFormType($this->em), [
                'label' => ' ',
            ])
            ->add('value', 'number', [
                'grouping' => true,
                'precision' => 2,
                'label' => 'Estimated Deposit:',
                'constraints' => [new NotBlank()],
            ]);
    }

    protected function buildFormForDepositMoney(FormBuilderInterface $builder)
    {
        if ($this->groupType) {
            $builder->add('groupType', 'hidden', [
                'data' => $this->groupType->getId(),
                'mapped' => false,
            ]);
        } else {
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
        }

        $builder->add('value', 'number', [
            'grouping' => true,
            'precision' => 2,
            'label' => 'Estimated Deposit:',
            'constraints' => [new NotBlank()],
        ]);
    }
}
