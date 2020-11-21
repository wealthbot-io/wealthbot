<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 21.12.12
 * Time: 15:07
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\AccountGroup;
use App\Entity\User;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountTypesFormType extends AbstractType
{
    private $group;
    private $client;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->group = $options['group'];
        $this->client = $options['user'];


        $riaCompanyInformation = $this->client->getProfile()->getRia()->getRiaCompanyInformation();

        $isAllowRetirementPlan = false;
        $group = $this->group;

        $builder->add('group_type', EntityType::class, [
                'class' => 'App\Entity\AccountGroupType',
                'query_builder' => function (EntityRepository $er) use ($group) {
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
                'property_path' => '[type][name]',
            ])
            ->add('groups', HiddenType::class, [
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'group' => null,
            'user' => null
        ]);
    }
}
