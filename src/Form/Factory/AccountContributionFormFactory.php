<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 02.04.13
 * Time: 14:57
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Factory;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;
use App\Entity\AccountContribution;
use App\Entity\ClientAccount;
use App\Entity\OneTimeContribution;
use App\Entity\SystemAccount;
use App\Form\EventListener\AccountContributionFormEventSubscriber;
use App\Form\EventListener\OneTimeContributionFormEventSubscriber;
use App\Form\Type\AccountContributionFormType;
use App\Form\Type\OneTimeContributionFormType;

class AccountContributionFormFactory
{
    private $factory;
    private $em;

    public function __construct(FormFactoryInterface $factory, EntityManager $em)
    {
        $this->factory = $factory;
        $this->em = $em;
    }

    /**
     * Create contribution form.
     *
     * @param string        $action
     * @param SystemAccount $account
     * @param array         $options
     *
     * @return \Symfony\Component\Form\FormInterface
     *
     * @throws \InvalidArgumentException
     */
    public function create($action, SystemAccount $account, array $options = [])
    {
        $actionsList = ['one_time', 'create', 'update'];

        if (!in_array($action, $actionsList)) {
            throw new \InvalidArgumentException(sprintf('Invalid value for action argument: %s', $action));
        }

        $type = $this->buildFormType($action, $account);
        $data = $this->buildFormData($action, $account);

        return $this->factory->create($type, $data, $options);
    }

    /**
     * Build contribution form type.
     *
     * @param string        $action
     * @param SystemAccount $account
     *
     * @return FormTypeInterface
     */
    private function buildFormType($action, SystemAccount $account)
    {
        if ('one_time' === $action) {
            $subscriber = new OneTimeContributionFormEventSubscriber($this->factory, $this->em, $account);
            $formType = OneTimeContributionFormType::class;
        } else {
            $subscriber = new AccountContributionFormEventSubscriber($this->factory, $this->em, $account);
            $formType = AccountContributionFormType::class;
        }

        return $formType;
    }

    /**
     * Build data for contribution form.
     *
     * @param string        $action
     * @param SystemAccount $account
     *
     * @return AccountContribution|OneTimeContribution
     */
    private function buildFormData($action, SystemAccount $account)
    {
        $clientAccount = $account->getClientAccount();
        $existContribution = $account->getAccountContribution();

        switch ($action) {
            case 'one_time':
                $data = $this->buildOneTimeContributionData($clientAccount, $existContribution);
                break;
            case 'create':
                $data = $this->buildCreateContributionData($clientAccount, $existContribution);
                break;
            default:
                $data = $this->buildUpdateContributionData($existContribution);
                break;
        }

        return $data;
    }

    /**
     * Build data for contribution form with one_time action.
     *
     * @param ClientAccount       $clientAccount
     * @param AccountContribution $existContribution
     *
     * @return OneTimeContribution
     */
    private function buildOneTimeContributionData(ClientAccount $clientAccount, AccountContribution $existContribution = null)
    {
        $data = new OneTimeContribution();
        $data->setSystemAccount($clientAccount->getSystemAccount());

        if ($existContribution) {
            $data->setBankInformation($existContribution->getBankInformation());

            if ($existContribution->isOneTimeContribution()) {
                $data->setContributionYear($existContribution->getContributionYear());
                $data->setStartTransferDate($existContribution->getStartTransferDate());
                $data->setAmount($existContribution->getAmount());
            }
        }

        return $data;
    }

    /**
     * Build data for contribution form with create action.
     *
     * @param ClientAccount       $clientAccount
     * @param AccountContribution $existContribution
     *
     * @return AccountContribution
     *
     * @throws \Exception
     */
    private function buildCreateContributionData(ClientAccount $clientAccount, AccountContribution $existContribution = null)
    {
        if ($existContribution) {
            if (!$existContribution->isOneTimeContribution()) {
                throw new \Exception('Object with auto-invest instructions already exist.');
            }

            $data = $existContribution;

            $data->setType(AccountContribution::TYPE_FUNDING_BANK);
            $data->setStartTransferDate(null);
            $data->setAmount(null);
            $data->setTransactionFrequency(null);
            $data->setContributionYear(null);
        } else {
            $data = new AccountContribution();
            $data->setAccount($clientAccount);
        }

        return $data;
    }

    /**
     * Build data for contribution form with create action.
     *
     * @param AccountContribution $existContribution
     *
     * @return AccountContribution
     *
     * @throws \Exception
     */
    private function buildUpdateContributionData(AccountContribution $existContribution = null)
    {
        if (!$existContribution || $existContribution->isOneTimeContribution()) {
            throw new \Exception('No object with auto-invest instructions for update.');
        }

        return $existContribution;
    }
}
