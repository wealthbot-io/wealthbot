<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 24.05.13
 * Time: 16:28
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Wealthbot\ClientBundle\Entity\ClientAccount;

class DashboardSystemAccountsFormType extends AbstractType
{
    private $account;
    private $systemAccounts;

    public function __construct(ClientAccount $account, array $systemAccounts = [])
    {
        $this->account = $account;
        $this->systemAccounts = $systemAccounts;
    }

    public function buildForm(FormBuilderInterface $builder, array $options = [])
    {
        if (count($this->systemAccounts) === 1) {
            $builder->add('account', 'hidden', ['attr' => ['value' => $this->systemAccounts[0]->getId()]]);
        } else {
            $clientId = $this->account->getClientId();
            $type = $this->account->getSystemType();

            $builder->add('account', 'entity', [
                'class' => 'Wealthbot\\ClientBundle\\Entity\\SystemAccount',
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
}
