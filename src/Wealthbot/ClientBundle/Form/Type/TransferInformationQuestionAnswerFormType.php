<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 02.09.13
 * Time: 17:49
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class TransferInformationQuestionAnswerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('value', 'choice', array(
            'choices' => array(true => 'Yes', false => 'No'),
            'expanded' => true,
            'constraints' => array(new NotBlank())
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\ClientBundle\Entity\TransferCustodianQuestionAnswer'
        ));
    }

    public function getName()
    {
        return 'answer';
    }
}