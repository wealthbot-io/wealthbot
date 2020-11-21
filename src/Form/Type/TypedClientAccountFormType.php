<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 02.09.13
 * Time: 16:47
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Entity\AccountGroup;
use App\Entity\AccountGroupType;
use App\Entity\User;

class TypedClientAccountFormType extends ClientAccountFormType
{
    protected $em;
    private $groupType;

    /** @param \App\Entity\User $client */
    protected $client;
    protected $group;
    protected $validateAdditionalFields;

    protected $isAllowRetirementPlan;
    protected $contributionTypes;

    protected function buildFormForFinancialInstitution(FormBuilderInterface $builder, array $options = null)
    {
        $this->em = $options['em'];
        $this->groupType = $options['group'];
        $this->client = $options['user'];
        $this->group = $options['group'];
        $this->isAllowRetirementPlan = $this->client->getProfile()->getRia()->getRiaCompanyInformation()->getIsAllowRetirementPlan();
        $this->validateAdditionalFields = true;

        $this->contributionTypes = [
            'contributions' => 'Contributions',
            'distributions' => 'Distributions',
            'neither' => 'Neither',
        ];


        if ($this->groupType) {
            $builder->add('groupType', HiddenType::class, [
                'data' => $this->group->getType()->getId(),
                'mapped' => false,
            ]);
        } else {
            $group = $this->group;
            $isAllowRetirementPlan = $this->isAllowRetirementPlan;

            $builder->add('groupType', EntityType::class, [
                'class' => 'App\\Entity\\AccountGroupType',
                'query_builder' => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('gt');
                    $qb
                        ->leftJoin('gt.group', 'g')
                        ->where('g.name = :group')
                        ->setParameter('group', AccountGroup::GROUP_DEPOSIT_MONEY)
                    ;

                    return $qb;
                },
                'property_path' => 'type.name',
                'label' => 'Account Type',
                'placeholder' => 'Select Type',
            ]);
        }

        $builder
            ->add('financial_institution', TextType::class, [
                'constraints' => [new NotBlank()],
                'label' => 'Financial Institution:',
            ])
            ->add('transferInformation', AccountTransferInformationFormType::class, [
                'label' => ' ',
                'em' => $this->em
            ])
            ->add('value', NumberType::class, [
                'grouping' => true,
                'scale' => 2,
                'label' => 'Estimated Deposit:',
                'constraints' => [new NotBlank()],
            ]);
    }

    protected function buildFormForDepositMoney(FormBuilderInterface $builder, array $options = null)
    {
        if ($this->groupType) {
            $builder->add('groupType', HiddenType::class, [
                'data' => $this->group->getType()->getId(),
                'mapped' => false,
            ]);
        } else {
            $group = $this->group;
            $isAllowRetirementPlan = $this->isAllowRetirementPlan;

            $builder->add('groupType', EntityType::class, [
                'class' => 'App\\Entity\\AccountGroupType',
                'query_builder' => function (EntityRepository $er) {
                    $qb = $er->createQueryBuilder('gt');
                    $qb
                        ->leftJoin('gt.group', 'g')
                        ->where('g.name = :group')
                        ->setParameter('group', AccountGroup::GROUP_DEPOSIT_MONEY)
                    ;

                    return $qb;
                },
                'property_path' => 'type.name',
                'label' => 'Account Type',
                'placeholder' => 'Select Type',
            ]);
        }

        $builder->add('value', NumberType::class, [
            'grouping' => true,
          //  'scale' => 2,
            'label' => 'Estimated Deposit:',
            'constraints' => [new NotBlank()],
        ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\ClientAccount',
            'em' => null,
            'user' => null,
            'group' => null,
            'client' => null,
            'validateAdditionalFields' => null
        ]);
    }
}
