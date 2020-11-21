<?php

namespace App\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\RiaCompanyInformation;
use App\Entity\Profile;

class CreateClientProfileType extends AbstractType
{
    private $ria;
    private $data;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->ria = $options['ria'];
        $this->data = $options['data'];



        /** @param \App\Entity\User $ria */
        $ria = $this->ria;
        $portfolioModel = $ria->getRiaCompanyInformation()->getPortfolioModel();

        $builder
            ->add('first_name')
            ->add('last_name')
        ;

        $accManagedChoices = array_flip(RiaCompanyInformation::$account_managed_choices);
        // If ria managed as client by client, then add choices...
        if ($ria->getRiaCompanyInformation()->getAccountManaged() === $accManagedChoices['Client by Client Basis']) {
            $builder->add(
                'client_account_managed',
                ChoiceType::class,
                [
                    'choices' => Profile::$client_account_managed_choices,
                    'expanded' => true,
                ]
            );
        }

        $builder->add('suggested_portfolio', EntityType::class, [
            'class' => 'App\\Entity\\CeModel',
            'property' => 'name',
            'query_builder' => function (\Doctrine\ORM\EntityRepository $er) use ($portfolioModel, $ria) {
                return $er->createQueryBuilder('p')
                    ->leftJoin('p.parent', 'parent')
                    ->andWhere('p.ownerId = :owner_id')
                    ->andWhere('parent.id = :parent_id')
                    ->setParameters(['owner_id' => $ria->getId(), 'parent_id' => $portfolioModel->getId()]);
            },
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Profile',
            'ria' => null,
            'data' => null
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_riabundle_createclientprofiletype';
    }
}
