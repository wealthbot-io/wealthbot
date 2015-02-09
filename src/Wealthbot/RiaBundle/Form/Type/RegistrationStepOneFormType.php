<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 20.08.13
 * Time: 16:29
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Type;


use Wealthbot\UserBundle\Form\Type\RiaDocumentsFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RegistrationStepOneFormType extends RiaCustodianFormType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);


        $builder->add('allow_non_electronically_signing', 'choice', array(
                'choices' => array(true => 'Yes', false => 'No'),
                'expanded' => true
            ))
            ->add('documents', new RiaDocumentsFormType(), array('mapped' => false))
            ->add('signature', 'text', array('mapped' => false));

        $builder->addEventListener(FormEvents::BIND, function (FormEvent $event) {
            $form = $event->getForm();
            $documents = $form->get('documents')->getData();

            foreach ($documents as $key => $file) {
                if (!($file instanceof UploadedFile)) {
                    $form->get('documents')->get($key)->addError(new FormError('Required.'));
                }
            }
        });
    }

    public function getName()
    {
        return 'step_one';
    }
}