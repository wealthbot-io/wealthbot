<?php

namespace App\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class AdminProfileType extends ProfileType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->remove('company')
            ->remove('user')
        ;
    }
}
