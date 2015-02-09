<?php

namespace Wealthbot\RiaBundle\Controller;

use JMS\Serializer\SerializationContext;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Wealthbot\AdminBundle\Entity\BillingSpec;
use Wealthbot\AdminBundle\Entity\Fee;
use Wealthbot\AdminBundle\Form\FormErrorBag;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\RiaBundle\Form\Type\BillingSpecFormType;
use Wealthbot\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class BillingSpecController
 * @package Wealthbot\RiaBundle\Controller
 */
class BillingSpecController extends Controller
{
    /**
     * @return object|void
     */
    public function listAction() {

        /* @var $user User */
        $user = $this->getUser();
        $specs = $this->get('wealthbot.manager.billing_spec')->getSpecs($user);
        $serializer = $this->container->get('jms_serializer');
        $data = $serializer->serialize($specs, 'json', SerializationContext::create()->setGroups(array('list')));

        return new Response($data);
    }

    /**
     * @return Response
     */
    public function createAction() {

        /** @var User $ria */
        $ria = $this->getUser();
        $em = $this->container->get('doctrine.orm.entity_manager');
        $riaCompanyInfo = $em->getRepository('WealthbotRiaBundle:RiaCompanyInformation')->findOneBy(array('ria_user_id' => $ria->getId()));
        if ($riaCompanyInfo->getPortfolioProcessing() == RiaCompanyInformation::PORTFOLIO_PROCESSING_STRAIGHT_THROUGH
            && $ria->getBillingSpecs()->count() > 0) {

            return new Response(array('error'=>'can\'t add more than one for straight portfolio processing'), 400);
        }

        $billingSpec = new BillingSpec();

        //Need to separate fields in subform
        $formType = new BillingSpecFormType();

        $data = $this->getRequest()->get($formType->getName());
        $billingSpec->setType($data['type']);

        $form = $this->createForm($formType, $billingSpec);

        $form->bind($this->getRequest());

        if($form->isValid()) {

            if ($riaCompanyInfo->getPortfolioProcessing() == RiaCompanyInformation::PORTFOLIO_PROCESSING_STRAIGHT_THROUGH) {
                $billingSpec->setMaster(true);
            }

            $em = $this->getDoctrine()->getManager();
            $billingSpec->setOwner($this->getUser());

            $em->persist($billingSpec);
            $em->flush();
            return new Response();

        } else {
            $errors = new FormErrorBag($form);

            return new Response($errors->toJson(), 400);
        }
    }

    /**
     * @SecureParam(name="billingSpec", permissions="VIEW")
     * @param BillingSpec $billingSpec
     * @return Response
     */
    public function getAction(BillingSpec $billingSpec) {

        $data = $this->container->get('jms_serializer')->serialize($billingSpec, 'json', SerializationContext::create()->setGroups(array('details')));

        return new Response($data);
    }

    /**
     * @SecureParam(name="billingSpec", permissions="DELETE")
     * @param BillingSpec $billingSpec
     * @return Response
     */
    public function deleteAction(BillingSpec $billingSpec) {

        $this->get('wealthbot.manager.billing_spec')->remove($billingSpec);

        return new Response();
    }

    /**
     * @SecureParam(name="billingSpec", permissions="EDIT")
     * @param BillingSpec $billingSpec
     * @return Response
     */
    public function updateAction(BillingSpec $billingSpec) {

        $form = $this->createForm(new BillingSpecFormType(), $billingSpec);

        $form->bind($this->getRequest());

        if($form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $em->persist($billingSpec);
            $em->flush();
            return new Response();

        } else {
            $errors = new FormErrorBag($form);

            return new Response($errors->toJson(), 400);
        }
    }
}