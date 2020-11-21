<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 24.05.13
 * Time: 16:28
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\ClientAccount;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DashboardSystemAccountsFormType extends AbstractType
{
    private $account;
    private $systemAccounts;

    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        $this->account = $options['account'];
        $this->systemAccounts = $options['systemAccounts'];

        if (1 === count($this->systemAccounts)) {
            $builder->add('account', HiddenType::class, ['attr' => ['value' => $this->systemAccounts[0]->getId()]]);
        } else {
            $clientId = $this->account->getClientId();
            $type = $this->account->getSystemType();

            $builder->add('account', EntityType::class, [
                'class' => 'App\\Entity\\SystemAccount',
                'multiple' => false,
                'expanded' => true,
                'query_builder' => function (EntityRepository $er) use ($clientId, $type) {
                    return $er->createQueryBuilder('sa')
                        ->where('sa.client_id = :client_id')
                        ->andWhere('sa.type = :type')
                        ->setParameters([
                            'client_id' => $clientId,
                            'type' => $type,
                        ]);
                },
            ]);
        }
    }

    public function getBlockPrefix()
    {
        return 'system_account';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'account' => null,
            'systemAccounts' => null
        ]);
    }
}
