<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 04.02.13
 * Time: 11:53
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\ClientBundle\Model\AccountOwnerInterface;

class TransferBasicFormType extends ClientProfileFormType
{
    private $class;

    public function __construct(AccountOwnerInterface $owner)
    {
        $this->class = get_class($owner);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('citizenship')
            ->remove('marital_status')
            ->remove('spouse')
            ->remove('annual_income')
            ->remove('estimated_income_tax')
            ->remove('liquid_net_worth')
            ->remove('employment_type')
        ;

        $data = $builder->getData();

        $email = '';
        if ($data && $data->getEmail()) {
            $email = $data->getEmail();
        }

        $builder->add('email', 'email', [
            'attr' => ['value' => $email],
            'required' => false,
        ]);
    }

    public function validate(FormEvent $event)
    {
        parent::validate($event);

        $form = $event->getForm();

        if ($form->has('email')) {
            $email = $form->get('email')->getData();

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $form->get('email')->addError(new FormError('Invalid email address.'));
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => $this->class,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'transfer_basic';
    }
}
