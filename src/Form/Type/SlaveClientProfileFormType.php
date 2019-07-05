<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 29.07.13
 * Time: 17:43
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use App\Form\Type\ProfileType;

class SlaveClientProfileFormType extends ProfileType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->remove('company')->remove('user');
    }

    public function getBlockPrefix()
    {
        return 'profile';
    }
}
