<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\UserBundle\Entity\Profile;

class CreateClientProfileType extends AbstractType
{
    private $ria;
    private $data;

    public function __construct($ria, $data)
    {
        $this->ria = $ria;
        $this->data = $data;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \Wealthbot\UserBundle\Entity\User $ria */
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
                'client_account_managed', 'choice', [
                    'choices' => Profile::$client_account_managed_choices,
                    'expanded' => true,
                ]
            );
        }

        $builder->add('suggested_portfolio', 'entity', [
            'class' => 'Wealthbot\\AdminBundle\\Entity\\CeModel',
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
            'data_class' => 'Wealthbot\UserBundle\Entity\Profile',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_riabundle_createclientprofiletype';
    }
}
