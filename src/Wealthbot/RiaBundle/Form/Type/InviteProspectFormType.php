<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Wealthbot\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InviteProspectFormType extends AbstractType
{
    private $ria;

    public function __construct(User $ria)
    {
        $this->ria = $ria;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $ria = $this->ria;

        $typeChoices = array('online' => 'Online', 'internal' => 'Internal');

        $builder
            ->add('first_name','text', array(
                'required' => true,
                'property_path' => 'profile.first_name'
            ))
            ->add('last_name','text', array(
                'required' => true,
                'property_path' => 'profile.last_name'
            ))
            ->add('email', 'email', array('required' => true))
            ->add('groups', 'entity', array(
                'property' => 'name', // Assuming that the entity has a "name" property
                'label'    => 'Groups:',
                'class'    => 'Wealthbot\UserBundle\Entity\Group',
                'query_builder' => function(EntityRepository $er) use ($ria) {
                    return $er->createQueryBuilder('g')
                        ->andWhere('g.owner = :owner OR g.owner IS NULL')
                        ->setParameter('owner', $ria);
                }
            ))
            ->add('type', 'choice', array(
                'choices' => $typeChoices,
                'mapped' => false
            ));

        $builder->addEventListener(FormEvents::PRE_BIND, function(FormEvent $event) use ($typeChoices) {
            $form = $event->getForm();
            $data = $event->getData();

            if ($data) {

                if (!array_key_exists($data['type'], $typeChoices)) {
                    $form->get('type')->addError(new FormError('Invalid type'));
                }

            }
        });

        $builder->addEventListener(FormEvents::BIND, function(FormEvent $event) use ($typeChoices) {
            $data = $event->getData();

            if ($data instanceof User) {
                $data->setUsername($data->getEmail());
            }
    });


    }

    public function getName()
    {
        return 'invite_prospect';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Wealthbot\UserBundle\Entity\User'
        ));
    }

}