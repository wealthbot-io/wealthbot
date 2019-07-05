<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 29.07.13
 * Time: 17:46
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;

class SlaveClientFormType extends AbstractType
{
    private $class;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->class = 'App\Entity\User';

        $builder
            ->add('email', EmailType::class, array('label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
        ;

        $builder->add('profile', SlaveClientProfileFormType::class);

        $factory = $builder->getFormFactory();
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($factory) {
            $form = $event->getForm();
            $user = $event->getData();

            $access = 'limited';

            if ($user && $user->hasRole('ROLE_CLIENT_FULL')) {
                $access = 'full';
            }

            $form->add($factory->createNamed('access', ChoiceType::class, $access, [
                'choices' => ['full' => 'Full', 'limited' => 'Limited'],
                'mapped' => false,
                'auto_initialize' => false,
            ]));
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $user = $event->getData();

            $access = $form->get('access')->getData();

            if ($user->hasRole('ROLE_CLIENT_FULL') && 'limited' === $access) {
                $user->removeRole('ROLE_CLIENT_FULL');
            }

            if (!$user->hasRole('ROLE_CLIENT_FULL') && 'full' === $access) {
                $user->addRole('ROLE_CLIENT_FULL');
            }

            /* @var User $user */
            $user->setUsername($user->getEmail());
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\User',
            'intention' => 'registration',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'slave_client';
    }
}
