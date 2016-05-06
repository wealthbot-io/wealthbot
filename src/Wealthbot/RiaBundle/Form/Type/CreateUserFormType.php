<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\UserBundle\Entity\User;

class CreateUserFormType extends BaseType
{
    private $ria;

    const TYPE_ADMIN = 'admin';
    const TYPE_USER = 'user';

    public function __construct($class, User $ria)
    {
        $this->ria = $ria;
        parent::__construct($class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $ria = $this->ria;
        $factory = $builder->getFormFactory();
        $choices = [
            self::TYPE_ADMIN => 'Admin',
            self::TYPE_USER => 'User',
        ];

        $builder
            ->remove('username', 'text')
            ->remove('plainPassword')
            ->add('profile', new CreateUserProfileFormType($ria))
            ->add('groups', 'entity', [
                'multiple' => true,   // Multiple selection allowed
                'property' => 'name', // Assuming that the entity has a "name" property
                'label' => 'Groups:',
                'class' => 'Wealthbot\UserBundle\Entity\Group',
                'query_builder' => function (EntityRepository $er) use ($ria) {
                    return $er->createQueryBuilder('g')
                        ->andWhere('g.owner = :owner')
                        ->orWhere('g.owner is null')
                        ->setParameter('owner', $ria);
                },
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($ria, $factory, $choices) {
            $form = $event->getForm();
            $user = $event->getData();

            $type = CreateUserFormType::TYPE_ADMIN;
            if ($user && !$user->hasRole('ROLE_RIA_ADMIN')) {
                $type = CreateUserFormType::TYPE_USER;
            }
            if (!($user && $user->hasRole('ROLE_RIA'))) {
                $form->add($factory->createNamed('type', 'choice', $type, [
                    'choices' => $choices,
                    'mapped' => false,
                    'label' => 'Type:',
                    'auto_initialize' => false,
                ]));
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($ria) {
            /** @var $user User */
            $user = $event->getData();
            $form = $event->getForm();

            $user->setUsername($user->getEmail());
            $user->setEnabled(true);

            if ($form->has('type')) {
                $type = $form->get('type')->getData();
                if ($type === CreateUserFormType::TYPE_ADMIN) {
                    $user->setRoles(['ROLE_RIA_ADMIN']);
                } else {
                    $user->setRoles(['ROLE_RIA_USER']);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\UserBundle\Entity\User',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_riabundle_createuser';
    }
}
