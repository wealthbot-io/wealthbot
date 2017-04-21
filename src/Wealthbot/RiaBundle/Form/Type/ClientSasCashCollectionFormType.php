<?php

namespace Wealthbot\RiaBundle\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Wealthbot\ClientBundle\Entity\SystemAccount;
use Wealthbot\UserBundle\Entity\User;

class ClientSasCashCollectionFormType extends AbstractType
{
    private $clientAccounts = [];

    public function __construct(User $client, array $systemAccounts = null)
    {
        if (null === $systemAccounts) {
            $systemAccounts = $client->getSystemAccounts();
        }

        /** @var SystemAccount $systemAccount */
        foreach ($systemAccounts as $systemAccount) {
            $clientAccount = $systemAccount->getClientAccount();
            $this->clientAccounts[$clientAccount->getId()] = $clientAccount->getSasCash();
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sas_cash_collection', 'collection', [
                'type' => 'number',
                'data' => new ArrayCollection($this->clientAccounts),
                'options' => [
                    'required' => false,
                    'grouping' => true,
                ],
            ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_riabundle_client_sas_cash_collection';
    }
}
