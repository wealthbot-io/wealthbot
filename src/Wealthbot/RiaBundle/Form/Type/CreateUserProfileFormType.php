<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 23.11.12
 * Time: 16:57
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use FOS\UserBundle\Form\Type\ProfileFormType as BaseProfile;

class CreateUserProfileFormType extends BaseProfile {

    private $ria;

    public function __construct($ria)
    {
        $this->ria = $ria;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('first_name', null, array(
                'label' => 'First name:'
            ))
            ->add('last_name', null, array(
                'label' => 'Last name:'
            ))
        ;

        $ria = $this->ria;

        $builder->addEventListener(\Symfony\Component\Form\FormEvents::BIND, function(\Symfony\Component\Form\FormEvent $event) use ($ria) {

            /** @var $profile \Wealthbot\UserBundle\Entity\Profile */
            $profile = $event->getData();
            $profile->setRia($ria);
            $profile->setRegistrationStep(5); // Registration is complete, confirm ?
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\UserBundle\Entity\Profile'
        ));
    }

    public function getName()
    {
        return 'wealthbot_riabundle_createuserprofiletype';
    }
}