<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 09.10.13
 * Time: 15:58
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;

class WorkflowNoteFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('note', TextareaType::class, ['required' => false]);
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getBlockPrefix()
    {
        return 'workflow';
    }
}
