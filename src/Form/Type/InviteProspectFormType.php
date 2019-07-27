<?php

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;

class InviteProspectFormType extends AbstractType
{
    private $ria;
    private $user;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->user = $options['user'];
        $this->ria = $options['ria'];
        $ria = $this->ria;

        $typeChoices = ['online' => 'Online', 'internal' => 'Internal'];

        $builder
            ->add('first_name', TextType::class, [
                'required' => true,
                'property_path' => 'profile.first_name',
            ])
            ->add('last_name', TextType::class, [
                'required' => true,
                'property_path' => 'profile.last_name',
            ])
            ->add('email', EmailType::class, ['required' => true])
            ->add('groups', EntityType::class, [
                'property_path' => 'name', // Assuming that the entity has a "name" property
                'label' => 'Groups:',
                'class' => 'App\Entity\Group',
                'query_builder' => function (EntityRepository $er) use ($ria) {
                    return $er->createQueryBuilder('g')
                        ->andWhere('g.owner = :owner OR g.owner IS NULL')
                        ->setParameter('owner', $ria);
                },
            ])
            ->add('type', ChoiceType::class, [
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

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
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
            'data_class' => 'App\Entity\User',
            'ria' => false,
            'user' => false
        ]);
    }
}
