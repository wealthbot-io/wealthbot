<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 23.11.12
 * Time: 16:57
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use FOS\UserBundle\Form\Type\ProfileFormType as BaseProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateUserProfileFormType extends AbstractType
{
    private $ria;

    private $class;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->ria = $options['ria'];
        $this->class = $options['class'];

        $builder
            ->add('first_name', null, [
                'label' => 'First name:',
            ])
            ->add('last_name', null, [
                'label' => 'Last name:',
            ])
        ;

        $constraintsOptions = array(
            'message' => 'fos_user.current_password.invalid',
        );

        if (!empty($options['validation_groups'])) {
            $constraintsOptions['groups'] = array(reset($options['validation_groups']));
        }

        $builder->add('current_password', PasswordType::class, array(
            'label' => 'form.current_password',
            'translation_domain' => 'FOSUserBundle',
            'mapped' => false,
            'constraints' => array(
                new NotBlank(),
                new UserPassword($constraintsOptions),
            ),
            'attr' => array(
                'autocomplete' => 'current-password',
            ),
        ));

        $ria = $this->ria;

        $builder->addEventListener(\Symfony\Component\Form\FormEvents::SUBMIT, function (\Symfony\Component\Form\FormEvent $event) use ($ria) {
            /** @var $profile \Entity\Profile */
            $profile = $event->getData();
            $profile->setRia($ria);
            $profile->setRegistrationStep(5); // Registration is complete, confirm ?
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Profile',
            'ria' => null,
            'class' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_riabundle_createuserprofiletype';
    }
}
