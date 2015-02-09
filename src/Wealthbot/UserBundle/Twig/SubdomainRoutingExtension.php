<?php
namespace Wealthbot\UserBundle\Twig;


use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\UserBundle\Manager\SubdomainManager;

class SubdomainRoutingExtension extends \Twig_Extension
{
    private $manager;

    public function __construct(SubdomainManager $manager)
    {
        $this->manager = $manager;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('subdomain_url', array($this, 'subDomainUrl')),
            new \Twig_SimpleFunction('has_subdomain', array($this, 'hasSubDomain')),
            new \Twig_SimpleFunction('get_subdomain', array($this, 'getSubDomain')),
            new \Twig_SimpleFunction('get_domain', array($this, 'getDomain'))
        );
    }

    public function subDomainUrl(RiaCompanyInformation $companyInformation, $name, $parameters = array())
    {
        return $this->manager->generateSubDomainUrl($companyInformation, $name, $parameters);
    }

    public function hasSubDomain()
    {
        return $this->manager->hasSubDomain();
    }

    public function getSubDomain()
    {
        return $this->manager->getSubDomain();
    }

    public function getDomain()
    {
        return $this->manager->getDomain();
    }

    public function getName()
    {
        return 'subdomain_routing_extension';
    }

}