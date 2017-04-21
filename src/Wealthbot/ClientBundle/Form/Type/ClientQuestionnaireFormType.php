<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 12.10.12
 * Time: 14:35
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ClientQuestionnaireFormType extends AbstractType
{
    /** @var \Wealthbot\RiaBundle\Entity\RiskQuestion $question */
    private $question;

    public function __construct(\Wealthbot\RiaBundle\Entity\RiskQuestion $question)
    {
        $this->question = $question;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_client_questionnaire_answer_type';
    }
}
