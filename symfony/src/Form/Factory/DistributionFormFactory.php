<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 04.04.13
 * Time: 13:39
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Factory;

use Symfony\Component\Form\FormFactoryInterface;
use App\Entity\Distribution;
use App\Entity\SystemAccount;
use App\Form\EventListener\OneTimeDistributionFormEventSubscriber;
use App\Form\EventListener\ScheduledDistributionFormEventSubscriber;
use App\Form\Type\OneTimeDistributionFormType;
use App\Form\Type\ScheduledDistributionFormType;
use App\Manager\DistributionManager;

class DistributionFormFactory
{
    /** @var \Symfony\Component\Form\FormFactoryInterface */
    private $factory;

    /** @var \App\Manager\DistributionManager */
    private $manager;

    public function __construct(FormFactoryInterface $factory, DistributionManager $manager)
    {
        $this->factory = $factory;
        $this->manager = $manager;
    }

    /**
     * Create distribution form.
     *
     * @param string        $type
     * @param SystemAccount $account
     * @param array         $options
     *
     * @return \Symfony\Component\Form\FormInterface
     *
     * @throws \InvalidArgumentException
     */
    public function create($type, SystemAccount $account, array $options = [])
    {
        $existDistribution = $this->manager->getScheduledDistribution($account);

        if ($type === Distribution::TYPE_SCHEDULED) {
            $data = $this->buildScheduledDistributionData($account);
            $subscriber = new ScheduledDistributionFormEventSubscriber($this->factory);
            $formType = ScheduledDistributionFormType::class;
        } else {
            $data = $this->buildOneTimeDistributionData($account, $existDistribution);
            $subscriber = new OneTimeDistributionFormEventSubscriber($this->factory);
            $formType = OneTimeDistributionFormType::class;
        };

        return $this->factory->create($formType, $data, [
            'client' => $account->getClient(),
            'subscriber' => $subscriber
        ]);
    }

    /**
     * Build data for one-time distribution form.
     *
     * @param SystemAccount $account
     * @param Distribution  $existDistribution
     *
     * @return Distribution
     */
    private function buildOneTimeDistributionData(SystemAccount $account, Distribution $existDistribution = null)
    {
        $data = $this->manager->createOneTimeDistribution($account);

        if ($existDistribution && $existDistribution->getBankInformation()) {
            $data->setBankInformation($existDistribution->getBankInformation());
        } elseif ($account->getAccountContribution() && $account->getAccountContribution()->getBankInformation()) {
            $data->setBankInformation($account->getAccountContribution()->getBankInformation());
        }

        return $data;
    }

    /**
     * Build data for scheduled distribution form.
     *
     * @param SystemAccount $account
     *
     * @return Distribution
     */
    private function buildScheduledDistributionData(SystemAccount $account)
    {
        $data = $this->manager->createScheduledDistribution($account);

        if ($account->getAccountContribution()) {
            $data->setBankInformation($account->getAccountContribution()->getBankInformation());
        }

        return $data;
    }
}
