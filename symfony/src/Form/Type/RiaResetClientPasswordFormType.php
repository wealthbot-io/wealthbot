<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 25.07.13
 * Time: 12:34
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\User;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RiaResetClientPasswordFormType extends AbstractType
{
    private $ria;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->ria = $options['ria'];
        $ria = $this->ria;

        $builder->add('user', EntityType::class, [
            'class' => 'App\\Entity\\User',
            'property' => 'email',
            'query_builder' => function (EntityRepository $er) use ($ria) {
                return $er->createQueryBuilder('u')
                    ->leftJoin('u.profile', 'p')
                    ->where('p.ria_user_id = :ria_id')
                    ->andWhere('u.roles LIKE :role')
                    ->setParameters([
                        'ria_id' => $ria->getId(),
                        'role' => '%ROLE_RIA%',
                    ])
                    ->orderBy('u.email', 'ASC');
            },
        ]);
    }

    public function getBlockPrefix()
    {
        return 'reset_password';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'ria' => null
        ]);
    }
}
