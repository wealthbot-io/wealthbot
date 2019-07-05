<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;

class RiaSearchClientsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('search', SearchType::class, ['required' => false]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_riabundle_ria_find_clients_form_type';
    }
}
