<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 06.03.13
 * Time: 19:34
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\AdminBundle\Form\Handler;

use Wealthbot\AdminBundle\Entity\Security;
use Wealthbot\AdminBundle\Entity\SecurityPrice;
use Wealthbot\UserBundle\Entity\User;

class SecurityFormHandler extends AbstractFormHandler
{
    protected function success()
    {
        $security = $this->form->getData();
        $price = $this->form->get('price')->getData();

        if (!$security->getId()) {
            $this->createPriceHistory($security, $price);
        } else {
            $repository = $this->em->getRepository('WealthbotAdminBundle:SecurityPrice');
            $currentPrice = $repository->getCurrentBySecurityId($security->getId());

            if (!$currentPrice || $price !== $currentPrice->getPrice()) {
                $this->createPriceHistory($security, $price);
            }
        }

        $this->em->persist($security);
        $this->em->flush();
    }

    /**
     * Create new SecurityPrice for security.
     *
     * @param Security $security
     * @param float    $price
     */
    private function createPriceHistory(Security $security, $price)
    {
        $author = $this->getAuthor();
        $source = $author->getFirstName() ? $author->getFirstName().' '.$author->getLastName() : $author->getUsername();

        $securityPrice = new SecurityPrice();

        $securityPrice->setSecurity($security);
        $securityPrice->setPrice($price);
        $securityPrice->setSource($source);
        $securityPrice->setIsCurrent(true);

        $security->addSecurityPrice($securityPrice);
    }

    /**
     * Get user from security context.
     *
     * @return User
     *
     * @throws \InvalidArgumentException
     */
    private function getAuthor()
    {
        $tokenStorage = $this->getOption('token_storage');

        return $tokenStorage->getToken()->getUser();
    }
}
