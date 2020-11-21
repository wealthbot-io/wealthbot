<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 23.11.12
 * Time: 16:57
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\ClientPortfolio;
use App\Entity\User;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChooseClientPortfolioFormType extends AbstractType
{
    /** @param \App\Entity\ClientPortfolio */
    private $proposedPortfolio;


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->proposedPortfolio = $options['proposed_portfolio'];


        $client = $builder->getData();

        if (!($client instanceof User) || (($client instanceof User) && !$client->hasRole('ROLE_CLIENT'))) {
            throw new Exception('Client is required');
        }

        /** @var User $ria */
        $ria = $client->getRia();

        $builder->add('portfolio', EntityType::class, [
            'class' => 'App\\Entity\\CeModel',
            'property_path' => 'name',
            'query_builder' => function (EntityRepository $er) use ($ria) {
                return $er->createQueryBuilder('p')
                        ->where('p.parent IS NOT NULL')
                        ->andWhere('p.ownerId = :owner_id')
                        ->setParameters([
                            'owner_id' => $ria->getId(),
                        ]);
            },
            'mapped' => false,
            'data' => $this->proposedPortfolio ? $this->proposedPortfolio->getPortfolio() : null,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'client_portfolio_form';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'proposed_portfolio' => null
        ]);
    }
}
