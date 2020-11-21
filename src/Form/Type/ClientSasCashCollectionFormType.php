<?php

namespace App\Form\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use App\Entity\SystemAccount;
use App\Entity\User;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientSasCashCollectionFormType extends AbstractType
{
    private $clientAccounts = [];

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $systemAccounts = $options['systemAccounts'];
        $client = $options['client'];

        if (null === $systemAccounts) {
            $systemAccounts = $client ? $client->getSystemAccounts() : null;
        };

        /** @var SystemAccount $systemAccount */
        foreach ($systemAccounts as $systemAccount) {
            $clientAccount = $systemAccount->getClientAccount();
            $this->clientAccounts[$clientAccount->getId()] = $clientAccount->getSasCash();
        }


        $builder
            ->add('sas_cash_collection', CollectionType::class, [
                'entry_type' => NumberType::class,
                'data' => new ArrayCollection($this->clientAccounts),
            ]);
    }

    public function getBlockPrefix()
    {
        return 'wealthbot_riabundle_client_sas_cash_collection';
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'client' => null,
            'systemAccounts' => null
        ]);
    }
}
