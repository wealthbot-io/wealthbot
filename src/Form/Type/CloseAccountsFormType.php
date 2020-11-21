<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 05.04.13
 * Time: 16:22
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CloseAccountsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $builder->add('accounts_ids', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('messages', EntityType::class, [
                'mapped' => false,
                'class' => 'App\\Entity\\ClosingAccountMessage',
                'property' => 'message',
                'label' => 'Why are you closing the account? Please check all that apply:',
                'expanded' => true,
                'multiple' => true,
            ])
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            /** @var ArrayCollection $messages */
            $messages = $form->get('messages')->getData();

            if (null === $messages || !$messages->count()) {
                $form->get('messages')->addError(new FormError('Select at least one answer.'));
            }
        });
    }

    public function getBlockPrefix()
    {
        return 'close_accounts';
    }
}
