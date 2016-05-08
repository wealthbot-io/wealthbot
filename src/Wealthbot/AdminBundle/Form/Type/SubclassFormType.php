<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 09.04.13
 * Time: 15:14
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;
use Wealthbot\AdminBundle\Entity\Subclass;
use Wealthbot\AdminBundle\Repository\SubclassRepository;

class SubclassFormType extends AbstractType
{
    private $user;

    private $em;

    private $allSubclasses;

    public function __construct($user, EntityManager $em, $allSubclasses = null)
    {
        $this->user = $user;
        $this->em = $em;
        $this->allSubclasses = $allSubclasses;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $user = $this->user;
        $em = $this->em;
        $allSubclasses = $this->allSubclasses;

        $builder
            ->add('name', 'text', ['label' => 'Subclass'])
            ->add('expected_performance', 'text', ['label' => 'Expected Performance (%)'])
            ->add('accountType', 'entity', [
                'class' => 'Wealthbot\RiaBundle\Entity\SubclassAccountType',
                'placeholder' => 'Choose an option',
            ])
        ;

        if ($user->hasRole('ROLE_RIA')) {
            if ($user->getRiaCompanyInformation()->isRebalancedFrequencyToleranceBand()) {
                $builder->add('tolerance_band', 'number', ['precision' => 2]);
            }

//            if ($user->getRiaCompanyInformation()->isShowSubclassPriority()) {
              if (false) {
                  $factory = $builder->getFormFactory();
                  $refreshPriority = function ($form, $choices) use ($factory, $em) {
                    $form->add($factory->createNamed('priority', 'choice', null, [
                        'choices' => $choices,
                        'auto_initialize' => false,
                    ]));
                };

                  $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($refreshPriority, $user, $em) {
                    $form = $event->getForm();
                    $data = $event->getData();

                    if ($data === null) {
                        return;
                    }

                    if ($data instanceof Subclass) {
                        /** @var $subclassRepo SubclassRepository */
                        $subclassRepo = $em->getRepository('WealthbotAdminBundle:Subclass');
                        $subclasses = $subclassRepo->findByOwnerIdAndAccountTypeId($user->getId(), $data->getAccountTypeId());
                        $choices = range(0, count($subclasses));
                        unset($choices[0]);

                        $refreshPriority($form, $choices);
                    }
                });

                  $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($refreshPriority, $allSubclasses, $user) {
                    $form = $event->getForm();
                    $data = $event->getData();

                    if ($data === null) {
                        return;
                    }

                    $maxChoice = 0;
                    foreach ($allSubclasses as $subclass) {
                        if ($data['accountType'] === $subclass['accountType']) {
                            ++$maxChoice;
                        }
                    }

                    $choices = range(0, $maxChoice);
                    unset($choices[0]);

                    $refreshPriority($form, $choices);
                });
              }
        }

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($user, $em) {
            $data = $event->getData();

            if ($data === null) {
                return;
            }

            // Validate unique subclass name for strategy

            if (!$data->getExpectedPerformance()) {
                $data->setExpectedPerformance(0);
            }

            if ($data instanceof Subclass && $user->hasRole('ROLE_RIA')) {
                $data->setOwner($user);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\AdminBundle\Entity\Subclass',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'subclass';
    }
}
