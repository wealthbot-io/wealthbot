<?php
/**
 * Created by JetBrains PhpStorm.
 * User: root
 * Date: 17.12.12
 * Time: 21:23
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Wealthbot\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;

class RiskQuestionsFormType extends AbstractType
{

    /** @var EntityManager $em */
    protected  $em;

    /** @var User $user */
    protected  $user;

    /** @var bool $isPreSave */
    private $isPreSave;

    /** @var ArrayCollection */
    public $questions;

    public function __construct(EntityManager $em, User $user, $isPreSave = false)
    {
        $this->user = $user;
        $this->em = $em;
        $this->isPreSave = $isPreSave;

        $riaId = $this->user->getId();
        $this->questions = $this->em->getRepository('WealthbotRiaBundle:RiskQuestion')->getOwnerQuestionsOrAdminIfNotExists($riaId);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach($this->questions as $question){
            if ($question->getIsWithdrawAgeInput()) {
                $builder->add('client_birth_date', 'date', array(
                        'widget' => 'text',
                        'pattern' => '{{ month }}-{{ day }}-{{ year }}',
                        'required' => true
                    ))
                    ->add('answer_'.$question->getId(), 'text', array(
                        'label' => $question->getTitle(),
                        'property_path' => false,
                        'required' => true,
                        'data' => $this->user->getProfile()->getWithdrawAge()
                    ))
                ;
            } else {
                $userAnswer = $this->em->getRepository('WealthbotClientBundle:ClientQuestionnaireAnswer')->createQueryBuilder('ua')
                    ->where('ua.client_id = :client_id AND ua.question_id = :question_id')
                    ->setParameters(array('client_id' => $this->user->getId(), 'question_id' => $question->getId()))
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();

                $userAnswer = $userAnswer ? $userAnswer->getAnswer() : $userAnswer;

                $builder
                    ->add('answer_'.$question->getId(), 'entity', array(
                            'class' => 'WealthbotRiaBundle:RiskAnswer',
                            'query_builder' => function(EntityRepository $er) use ($question) {
                                return $er->createQueryBuilder('a')
                                    ->where('a.risk_question_id = :question_id')
                                    ->setParameter('question_id', $question->getId());
                            },
                            'empty_value' => $userAnswer ? false : 'Choose an Option',
                            'property' => 'title',
                            'property_path' => false,
                            'required' => true,
                            'label' => $question->getTitle(),
                            'preferred_choices' => $userAnswer ? array($userAnswer) : array()
                        ));
            }
        }

        if (!$this->isPreSave) {
            $this->addOnBindValidator($builder);
        }
    }

    protected function addOnBindValidator(FormBuilderInterface $builder)
    {
        $questions = $this->questions;

        $builder->addEventListener(FormEvents::BIND, function(FormEvent $event) use ($questions){
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

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
                //'data_class' => ''
            ));
    }

    public function getName()
    {
        return 'risk_questions';
    }

}
