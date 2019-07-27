<?php

namespace App\Manager;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\RiaCompanyInformation;
use Symfony\Component\Routing\RouterInterface;

class SubdomainManager
{
    private $om;
    private $router;
    private $domain;

    private $repository;
    private $class;

    public function __construct(EntityManagerInterface $om, RouterInterface $router, $class, $domain)
    {
        $this->om = $om;
        $this->repository = $om->getRepository($class);

        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();

        $this->router = $router;
        $this->domain = $domain;
    }

    /**
     * Returns RiaCompanyInformation by slug or null.
     * The slug is taken from the host (example: slug.site.com).
     *
     * @return object|null
     */
    public function getRiaCompanyInformation()
    {
        if ($this->hasSubDomain()) {
            return $this->repository->findOneBy(['slug' => $this->getSubDomain()]);
        }

        return;
    }

    public function getSubDomain()
    {
        $routerContext = $this->router->getContext();
        $matches = [];

        if (preg_match('/^([a-zA-Z0-9-]+)\./', $routerContext->getHost(), $matches)) {
            if (!empty($matches) && count($matches) > 1) {
                $slug = $matches[1];
                $currentHost = $slug.'.'.$this->getDomain();

                if ($currentHost === $this->router->getContext()->getHost()) {
                    return $slug;
                }
            }
        }

        return '';
    }

    /**
     * Has http host subdomain.
     *
     * @return bool
     */
    public function hasSubDomain()
    {
        return '' !== $this->getSubDomain();
    }

    /**
     * Returns login url string with subdomain.
     *
     * @param RiaCompanyInformation $companyInformation
     * @param string                $name
     * @param array                 $parameters
     *
     * @return string
     */
    public function generateSubDomainUrl(RiaCompanyInformation $companyInformation, $name, $parameters = [])
    {
        $slug = $companyInformation->getSlug();
        $this->router->getContext()->setHost($slug.'.'.$this->getDomain());

        return $this->router->generate($name, $parameters, true);
    }

    /**
     * Returns url string without subdomain.
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return string
     */
    public function generateUrl($name, $parameters = [])
    {
        $this->router->getContext()->setHost($this->getDomain());
        $url = $this->router->generate($name, $parameters, true);

        return $url;
    }

    /**
     * Get site host.
     *
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }
}
