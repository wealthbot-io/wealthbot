<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\UserBundle\Entity\User;

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

        $typeChoices = ['online' => 'Online', 'internal' => 'Internal'];

        $builder
            ->add('first_name', 'text', [
                'required' => true,
                'property_path' => 'profile.first_name',
            ])
            ->add('last_name', 'text', [
                'required' => true,
                'property_path' => 'profile.last_name',
            ])
            ->add('email', 'email', ['required' => true])
            ->add('groups', 'entity', [
                'property' => 'name', // Assuming that the entity has a "name" property
                'label' => 'Groups:',
                'class' => 'Wealthbot\UserBundle\Entity\Group',
                'query_builder' => function (EntityRepository $er) use ($ria) {
                    return $er->createQueryBuilder('g')
                        ->andWhere('g.owner = :owner OR g.owner IS NULL')
                        ->setParameter('owner', $ria);
                },
            ])
            ->add('type', 'choice', [
                'choices' => $typeChoices,
                'mapped' => false,
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($typeChoices) {
            $form = $event->getForm();
            $data = $event->getData();

            if ($data) {
                if (!array_key_exists($data['type'], $typeChoices)) {
                    $form->get('type')->addError(new FormError('Invalid type'));
                }
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($typeChoices) {
            $data = $event->getData();

            if ($data instanceof User) {
                $data->setUsername($data->getEmail());
            }
    });
    }

    public function getBlockPrefix()
    {
        return 'invite_prospect';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\UserBundle\Entity\User',
        ]);
    }
}
