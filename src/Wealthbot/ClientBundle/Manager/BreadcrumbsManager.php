<?php

namespace Wealthbot\ClientBundle\Manager;

use Symfony\Component\Routing\RouterInterface;

class BreadcrumbsManager {

    private $breadcrumbs = array();

    /** @var RouterInterface */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
        $this->addCrumb('Home', 'rx_client_dashboard');
    }

    public function addCrumb($name, $route)
    {
        $this->breadcrumbs[] = array(
            'name' => $name,
            'url' => $this->router->generate($route)
        );

        return $this;
    }

    public function getBreadcrumbs()
    {
        if (count($this->breadcrumbs) > 1) {
            return $this->breadcrumbs;
        }

        return array();
    }
}