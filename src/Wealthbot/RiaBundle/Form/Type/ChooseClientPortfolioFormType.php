<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 23.11.12
 * Time: 16:57
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\RiaBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Wealthbot\ClientBundle\Entity\ClientPortfolio;
use Wealthbot\UserBundle\Entity\User;

class ChooseClientPortfolioFormType extends AbstractType
{
    /** @var \Wealthbot\ClientBundle\Entity\ClientPortfolio */
    private $proposedPortfolio;

    public function __construct(ClientPortfolio $proposedPortfolio = null)
    {
        $this->proposedPortfolio = $proposedPortfolio;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $client = $builder->getData();

        if (!($client instanceof User) || (($client instanceof User) && !$client->hasRole('ROLE_CLIENT'))) {
            throw new Exception('Client is required');
        };

        /** @var User $ria */
        $ria = $client->getRia();

        $builder->add('portfolio', 'entity', [
            'class' => 'Wealthbot\\AdminBundle\\Entity\\CeModel',
            'property' => 'name',
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
}
