<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 04.04.13
 * Time: 13:39
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Factory;

use Symfony\Component\Form\FormFactoryInterface;
use Wealthbot\ClientBundle\Entity\Distribution;
use Wealthbot\ClientBundle\Entity\SystemAccount;
use Wealthbot\ClientBundle\Form\EventListener\OneTimeDistributionFormEventSubscriber;
use Wealthbot\ClientBundle\Form\EventListener\ScheduledDistributionFormEventSubscriber;
use Wealthbot\ClientBundle\Form\Type\OneTimeDistributionFormType;
use Wealthbot\ClientBundle\Form\Type\ScheduledDistributionFormType;
use Wealthbot\ClientBundle\Manager\DistributionManager;

class DistributionFormFactory
{
    /** @var \Symfony\Component\Form\FormFactoryInterface */
    private $factory;

    /** @var \Wealthbot\ClientBundle\Manager\DistributionManager */
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
        if (!array_key_exists($type, Distribution::getTypeChoices())) {
            throw new \InvalidArgumentException(sprintf('Invalid value for type argument: %s', $type));
        }

        $formType = $this->buildFormType($type, $account);
        $formData = $this->buildFormData($type, $account);

        return $this->factory->create($formType, $formData, $options);
    }

    /**
     * Build distribution form type.
     *
     * @param string        $type
     * @param SystemAccount $account
     *
     * @return ScheduledDistributionFormType
     *
     * @throws \InvalidArgumentException
     */
    private function buildFormType($type, SystemAccount $account)
    {
        switch ($type) {
            case Distribution::TYPE_SCHEDULED:
                $subscriber = new ScheduledDistributionFormEventSubscriber($this->factory);
                $formType = new ScheduledDistributionFormType($account, $subscriber);
                break;
            case Distribution::TYPE_ONE_TIME:
                $subscriber = new OneTimeDistributionFormEventSubscriber($this->factory, $account);
                $formType = new OneTimeDistributionFormType($account, $subscriber);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Invalid value for type argument: %s', $type));
                break;
        }

        return $formType;
    }

    private function buildFormData($type, SystemAccount $account)
    {
        $existDistribution = $this->manager->getScheduledDistribution($account);

        switch ($type) {
            case Distribution::TYPE_ONE_TIME:
                $data = $this->buildOneTimeDistributionData($account, $existDistribution);
                break;
            case Distribution::TYPE_SCHEDULED:
                if ($existDistribution) {
                    $data = $existDistribution;
                } else {
                    $data = $this->buildScheduledDistributionData($account);
                }
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Invalid value for type argument: %s', $type));
                break;
        }

        return $data;
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
