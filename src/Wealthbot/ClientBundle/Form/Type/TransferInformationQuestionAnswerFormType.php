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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TransferInformationQuestionAnswerFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('value', 'choice', [
            'choices' => [true => 'Yes', false => 'No'],
            'expanded' => true,
            'constraints' => [new NotBlank()],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\ClientBundle\Entity\TransferCustodianQuestionAnswer',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'answer';
    }
}
