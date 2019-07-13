<?php
/**
 * User: Maxim O. Belyakov
 * Date: 10.09.12
 * Time: 18:08.
 */

namespace App\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Form\Type\RiskQuestionsFormType as BaseType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientQuestionsFormType extends BaseType
{
    protected $em;

    protected $user;

    protected $isPreSave;


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->user = $options['user'];
        $this->isPreSave = $options['is_pre_save'];
        $riaId =  $options['user']->getProfile()->getRiaUserId();
        $this->em = $options['em'];
        $this->questions = $this->em->getRepository('App\Entity\RiskQuestion')->getOwnerQuestionsOrAdminIfNotExists($riaId);

        foreach ($this->questions as $question) {
            if ($question->getIsWithdrawAgeInput()) {
                $builder->add('answer_'.$question->getId(), TextType::class, [
                    'label' => $question->getTitle(),
                    'mapped' => false,
                    'required' => false,
                    'data' => $this->user->getProfile()->getWithdrawAge(),
                ]);
            } else {
                $userAnswer = $this->em->getRepository('App\Entity\ClientQuestionnaireAnswer')->createQueryBuilder('ua')
                    ->where('ua.client_id = :client_id AND ua.question_id = :question_id')
                    ->setParameters(['client_id' => $this->user->getId(), 'question_id' => $question->getId()])
                    ->getQuery()
                    ->getOneOrNullResult();

                $userAnswer = $userAnswer ? $userAnswer->getAnswer() : $userAnswer;

                $builder
                    ->add('answer_'.$question->getId(), EntityType::class, [
                            'class' => 'App\\Entity\\RiskAnswer',
                            'query_builder' => function (\Doctrine\ORM\EntityRepository $er) use ($question) {
                                return $er->createQueryBuilder('a')
                                    ->where('a.risk_question_id = :question_id')
                                    ->setParameter('question_id', $question->getId());
                            },
                            'placeholder' => $userAnswer ? false : 'Choose an Option',
                            'property_path' => 'title',
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\User',
            'user' => null,
            'is_pre_save' => null,
            'em' => null
        ]);
    }
}
