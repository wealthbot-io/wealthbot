<?php

namespace App\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\AlertsConfiguration;
use App\Entity\User;

class AlertsConfigurationManager
{
    protected $objectManager;
    protected $class;
    protected $repository;

    /**
     * Constructor.
     *
     * @param ObjectManager $om
     * @param $class
     */
    public function __construct(ObjectManager $om, $class)
    {
        $this->objectManager = $om;
        $this->repository = $om->getRepository($class);

        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
    }

    public function saveDefaultConfiguration(User $user)
    {
        $configuration = null;

        if ($user->hasRole('ROLE_RIA')) {
            $configuration = $this->createRiaDefaultConfiguration($user);
        } elseif ($user->hasRole('ROLE_CLIENT')) {
            //TODO: next step
            $configuration = null;
        }

        if ($configuration) {
            $this->objectManager->persist($configuration);
            $this->objectManager->flush();
        }

        return $configuration;
    }

    public function createRiaDefaultConfiguration(User $ria)
    {
        /** @var AlertsConfiguration $alertsConfiguration */
        $alertsConfiguration = new $this->class();

        $alertsConfiguration->setIsClientDrivenAccountClosures(true);
        $alertsConfiguration->setIsClientPortfolioSuggestion(true);
        $alertsConfiguration->setUser($ria);

        return $alertsConfiguration;
    }
}
