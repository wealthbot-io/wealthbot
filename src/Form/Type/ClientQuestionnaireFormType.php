<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 12.10.12
 * Time: 14:35
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientQuestionnaireFormType extends AbstractType
{
    private $question;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->question = $options['question'];
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_client_questionnaire_answer_type';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'question' => null
        ]);
    }
}
