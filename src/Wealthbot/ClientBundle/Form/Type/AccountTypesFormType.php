<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 21.12.12
 * Time: 15:07
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Wealthbot\ClientBundle\Entity\AccountGroup;
use Wealthbot\UserBundle\Entity\User;

class AccountTypesFormType extends AbstractType
{
    private $group;
    private $client;

    public function __construct(User $client, $group = AccountGroup::GROUP_DEPOSIT_MONEY)
    {
        $this->group = $group;
        $this->client = $client;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $riaCompanyInformation = $this->client->getProfile()->getRia()->getRiaCompanyInformation();

        $isAllowRetirementPlan = $riaCompanyInformation->getIsAllowRetirementPlan();
        $group = $this->group;

        $builder->add('group_type', 'entity', [
                'class' => 'Wealthbot\\ClientBundle\\Entity\\AccountGroupType',
                'query_builder' => function (EntityRepository $er) use ($group, $isAllowRetirementPlan) {
                    $qb = $er->createQueryBuilder('gt');
                    $qb
                        ->leftJoin('gt.group', 'g')
                        ->where('g.name = :group')
                        ->setParameter('group', $group)
                    ;

                    return $qb;
                },
                'multiple' => false,
                'expanded' => true,
                'property' => 'type.name',
            ])
            ->add('groups', 'hidden', [
                'data' => $group,
            ]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'client_account_types';
    }
}
