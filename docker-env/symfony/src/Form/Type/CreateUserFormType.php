<?php

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;

class CreateUserFormType extends AbstractType
{
    private $ria;

    private $class;

    const TYPE_ADMIN = 'admin';
    const TYPE_USER = 'user';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->ria = $options['ria'];
        $this->class = $options['class'];


        $factory = $builder->getFormFactory();
        $choices = [
            self::TYPE_ADMIN => 'Admin',
            self::TYPE_USER => 'User',
        ];

        $ria = $this->ria;
        $builder
            ->add('profile', CreateUserProfileFormType::class, ['ria'=> $this->ria])
            ->add('email')
            ->add('groups', EntityType::class, [
                'multiple' => true,   // Multiple selection allowed
             //   'property_path' => 'groups', // Assuming that the entity has a "name" property
                'label' => 'Groups:',
                'class' => 'App\Entity\Group',
                'query_builder' => function (EntityRepository $er) use ($ria) {
                    return $er->createQueryBuilder('g')
                        ->andWhere('g.owner = :owner')
                        ->orWhere('g.owner is null')
                        ->setParameter('owner', $ria);
                },
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($factory, $choices) {
            $form = $event->getForm();
            $user = $event->getData();

            $type = CreateUserFormType::TYPE_ADMIN;
            if ($user && !$user->hasRole('ROLE_RIA_ADMIN')) {
                $type = CreateUserFormType::TYPE_USER;
            }
            if (!($user && $user->hasRole('ROLE_RIA'))) {
                $form->add($factory->createNamed('type', ChoiceType::class, $type, [
                    'choices' => $choices,
                    'mapped' => false,
                    'label' => 'Type:',
                    'auto_initialize' => false,
                ]));
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            /** @var $user User */
            $user = $event->getData();
            $form = $event->getForm();

            $user->setUsername($user->getEmail());
            $user->setEnabled(true);

            if ($form->has('type')) {
                $type = $form->get('type')->getData();
                if (CreateUserFormType::TYPE_ADMIN === $type) {
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
            'data_class' => 'App\Entity\User',
            'ria' => null,
            'class' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'create_user';
    }
}
