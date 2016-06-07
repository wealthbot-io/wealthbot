<?php

namespace Wealthbot\RiaBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateClientType extends BaseType
{
    private $ria;

    public function __construct($class, $ria)
    {
        $this->ria = $ria;
        parent::__construct($class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $data = $builder->getData();

        $builder
            ->remove('username', 'text')
            ->remove('plainPassword')
            ->add('profile', new CreateClientProfileType($this->ria, $data))
        ;

        $plainPassword = $this->generatePlainPassword();

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($plainPassword) {
            $user = $event->getData();
            $user->setUsername($user->getEmail());
            $user->setPlainPassword($plainPassword);
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
        return 'wealthbot_riabundle_riacreateclienttype';
    }

    private function generatePlainPassword($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $count = mb_strlen($chars);

        for ($i = 0, $result = ''; $i < $length; ++$i) {
            $index = rand(0, $count - 1);
            $result .= mb_substr($chars, $index, 1);
        }

        return $result;
    }
}
