<?php
/**
 * User: Maxim O. Belyakov
 * Date: 10.09.12
 * Time: 18:08.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Wealthbot\RiaBundle\Form\Type\RiskQuestionsFormType as BaseType;

class ClientQuestionsFormType extends BaseType
{
    public function __construct(\Doctrine\ORM\EntityManager $em, \Wealthbot\UserBundle\Entity\User $user, $isPreSave = false)
    {
        parent::__construct($em, $user, false);

        $riaId = $this->user->getProfile()->getRiaUserId();
        $this->questions = $this->em->getRepository('WealthbotRiaBundle:RiskQuestion')->getOwnerQuestionsOrAdminIfNotExists($riaId);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->questions as $question) {
            if ($question->getIsWithdrawAgeInput()) {
                $builder->add('answer_'.$question->getId(), 'text', [
                    'label' => $question->getTitle(),
                    'mapped' => false,
                    'required' => false,
                    'data' => $this->user->getProfile()->getWithdrawAge(),
                ]);
            } else {
                $userAnswer = $this->em->getRepository('WealthbotClientBundle:ClientQuestionnaireAnswer')->createQueryBuilder('ua')
                    ->where('ua.client_id = :client_id AND ua.question_id = :question_id')
                    ->setParameters(['client_id' => $this->user->getId(), 'question_id' => $question->getId()])
                    ->getQuery()
                    ->getOneOrNullResult();

                $userAnswer = $userAnswer ? $userAnswer->getAnswer() : $userAnswer;

                $builder
                    ->add('answer_'.$question->getId(), 'entity', [
                            'class' => 'Wealthbot\\RiaBundle\\Entity\\RiskAnswer',
                            'query_builder' => function (\Doctrine\ORM\EntityRepository $er) use ($question) {
                                return $er->createQueryBuilder('a')
                                    ->where('a.risk_question_id = :question_id')
                                    ->setParameter('question_id', $question->getId());
                            },
                            'placeholder' => $userAnswer ? false : 'Choose an Option',
                            'property' => 'title',
                            'mapped' => false,
                            'required' => false,
                            'label' => $question->getTitle(),
                            'preferred_choices' => $userAnswer ? [$userAnswer] : [],
                        ]);
            }
        }

        $this->addOnSubmitValidator($builder);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_userbundle_client_questions_type';
    }
}
