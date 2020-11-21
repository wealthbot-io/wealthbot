<?php
/**
 * Created by JetBrains PhpStorm.
 * User: root
 * Date: 17.12.12
 * Time: 21:23
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;

class RiskQuestionsFormType extends AbstractType
{
    /** @var EntityManager $em */
    protected $em;

    /** @var User $user */
    protected $user;

    /** @var bool $isPreSave */
    private $isPreSave;

    /** @var ArrayCollection */
    public $questions;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->user = $options['user'];
        $this->em = $options['em'];
        $this->isPreSave = $options['is_pre_save'];

        $riaId = $this->user->getId();
        $this->questions = $this->em->getRepository('App\Entity\RiskQuestion')->getOwnerQuestionsOrAdminIfNotExists($riaId);



        foreach ($this->questions as $question) {
            if ($question->getIsWithdrawAgeInput()) {
                $builder->add('client_birth_date', DateType::class, [
                        'widget' => 'text',
                        'pattern' => '{{ month }}-{{ day }}-{{ year }}',
                        'required' => true,
                    ])
                    ->add('answer_'.$question->getId(), TextType::class, [
                        'label' => $question->getTitle(),
                        'mapped' => false,
                        'required' => true,
                        'data' => $this->user->getProfile()->getWithdrawAge(),
                    ])
                ;
            } else {
                $userAnswer = $this->em->getRepository('App\Entity\ClientQuestionnaireAnswer')->createQueryBuilder('ua')
                    ->where('ua.client_id = :client_id AND ua.question_id = :question_id')
                    ->setParameters(['client_id' => $this->user->getId(), 'question_id' => $question->getId()])
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();

                $userAnswer = $userAnswer ? $userAnswer->getAnswer() : $userAnswer;

                $builder
                    ->add('answer_'.$question->getId(), EntityType::class, [
                            'class' => 'App\\Entity\\RiskAnswer',
                            'query_builder' => function (EntityRepository $er) use ($question) {
                                return $er->createQueryBuilder('a')
                                    ->where('a.risk_question_id = :question_id')
                                    ->setParameter('question_id', $question->getId());
                            },
                            'placeholder' => $userAnswer ? false : 'Choose an Option',
                            'property_path' => 'title',
                            'mapped' => false,
                            'required' => true,
                            'label' => $question->getTitle(),
                            'preferred_choices' => $userAnswer ? [$userAnswer] : [],
                        ]);
            }
        }

        if (!$this->isPreSave) {
            $this->addOnSubmitValidator($builder);
        }
    }

    protected function addOnSubmitValidator(FormBuilderInterface $builder)
    {
        $questions = $this->questions;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($questions) {
            $form = $event->getForm();

            foreach ($questions as $question) {
                $key = 'answer_'.$question->getId();
                $value = $form->get($key)->getData();

                if ($question->getIsWithdrawAgeInput()) {
                    if (!is_numeric($value)) {
                        $form->get('withdraw_age')->addError(new FormError('Enter correct age.'));
                    }
                } else {
                    if (!$value) {
                        $form->get($key)->addError(new FormError('Required.'));
                    }
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'em' => null,
            'user' => null,
            'is_pre_save' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'risk_questions';
    }
}
