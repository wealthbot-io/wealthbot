<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 06.03.13
 * Time: 19:34
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

use App\Entity\Security;
use App\Entity\SecurityPrice;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class SecurityFormHandler extends AbstractFormHandler
{
    public function __construct(Form $form, RequestStack $requestStack, EntityManager $em, array $options = [])
    {
        parent::__construct($form, $requestStack->getCurrentRequest(), $em, $options);
    }

    public function success()
    {
        $data = $this->request->request->get('admin_security');
        $security = new Security();
        $security->setSymbol($data['symbol']);
        $security->setName($data['name']);
        $security->setExpenseRatio($data['expense_ratio']);

        $price = $data['price'];
        $this->em->persist($security);
        $this->em->flush();

        if (!$security->getId()) {
            $this->createPriceHistory($security, $price);
        } else {
            $repository = $this->em->getRepository('App\Entity\SecurityPrice');
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
