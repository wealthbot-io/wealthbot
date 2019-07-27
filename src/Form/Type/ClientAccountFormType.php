<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 05.10.12
 * Time: 16:16
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Entity\AccountGroup;
use App\Entity\ClientAccount;
use App\Entity\User;

class ClientAccountFormType extends AbstractType
{
    /** @var \App\Entity\User $client */
    protected $client;

    protected $group;

    protected $validateAdditionalFields;

    protected $isAllowRetirementPlan;

    protected $contributionTypes;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->client = $options['client'];
        $this->group = $options['group'] ? $options['group']  : AccountGroup::GROUP_EMPLOYER_RETIREMENT;
        $this->isAllowRetirementPlan = $this->client->getProfile()->getRia()->getRiaCompanyInformation()->getIsAllowRetirementPlan();
        $this->validateAdditionalFields = true;
        $this->contributionTypes = [
            'contributions' => 'Contributions',
            'distributions' => 'Distributions',
            'neither' => 'Neither',
        ];

        $gtid = (string) $options['group'] ?? (string) $this->group ??  (string) $this->group->getTypeId();
        
        if ($gtid) {
            $builder->add('groupType', HiddenType::class, [
                'data' => $gtid,
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
                        ->setParameter('group', AccountGroup::GROUP_DEPOSIT_MONEY);
                    return $qb;
                },
                'property_path' => 'type',
                'label' => 'Account Type:',
                'placeholder' => 'Select Type',
                'constraints' => [new NotBlank()],
            ]);
        };

        $builder->add('value', NumberType::class, [
            'grouping' => true,
            'scale' => 2,
            'label' => 'Estimated Deposit',
            'constraints' => [new NotBlank()],
        ]);

        $builder->add('monthly_contributions', NumberType::class, [
                'grouping' => true,
                'scale' => 2,
                'label' => 'Estimated Monthly Contributions',
                'required' => false,
                'attr' => [],
                'auto_initialize' => false,
        ]);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\ClientAccount',
            'group' => null,
            'em' => null,
            'validateAdditionalFields' => null,
            'client' => null,
            'isAllowRetirementPlan' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'client_account_type';
    }
}
