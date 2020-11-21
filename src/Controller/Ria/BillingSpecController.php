<?php

namespace App\Controller\Ria;

use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\BillingSpec;
use App\Form\FormErrorBag;
use App\Entity\RiaCompanyInformation;
use App\Form\Type\BillingSpecFormType;
use App\Entity\User;

/**
 * Class BillingSpecController.
 */
class BillingSpecController extends Controller
{
    /**
     * @return object|void
     */
    public function list()
    {
        /* @var $user User */
        $user = $this->getUser();
        $specs = $this->get('wealthbot.manager.billing_spec')->getSpecs($user);
        $serializer = $this->container->get('jms_serializer');
        $data = $serializer->serialize($specs, 'json', SerializationContext::create()->setGroups(['list']));

        return new Response($data);
    }

    /**
     * @return Response
     */
    public function create(Request $request)
    {
        /** @var User $ria */
        $ria = $this->getUser();
        $em = $this->container->get('doctrine.orm.entity_manager');
        $riaCompanyInfo = $em->getRepository('App\Entity\RiaCompanyInformation')->findOneBy(['ria_user_id' => $ria->getId()]);
        if (RiaCompanyInformation::PORTFOLIO_PROCESSING_STRAIGHT_THROUGH === $riaCompanyInfo->getPortfolioProcessing()
            && $ria->getBillingSpecs()->count() > 0) {
            return new Response(['error' => 'can\'t add more than one for straight portfolio processing'], 400);
        }

        $billingSpec = new BillingSpec();

        //Need to separate fields in subform
        $formType = BillingSpecFormType::class;

        $data = $request->get($formType->getName());
        $billingSpec->setType($data['type']);

        $form = $this->createForm($formType, $billingSpec);

        $form->handleRequest($request);

        if ($form->isValid()) {
            if (RiaCompanyInformation::PORTFOLIO_PROCESSING_STRAIGHT_THROUGH === $riaCompanyInfo->getPortfolioProcessing()) {
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
     * SecureParam(name="billingSpec", permissions="VIEW")
     *
     * @param BillingSpec $billingSpec
     *
     * @return Response
     */
    public function cget(BillingSpec $billingSpec)
    {
        $data = $this->container->get('jms_serializer')->serialize($billingSpec, 'json', SerializationContext::create()->setGroups(['details']));

        return new Response($data);
    }

    /**
     * SecureParam(name="billingSpec", permissions="DELETE")
     *
     * @param BillingSpec $billingSpec
     *
     * @return Response
     */
    public function delete(BillingSpec $billingSpec)
    {
        $this->get('wealthbot.manager.billing_spec')->remove($billingSpec);

        return new Response();
    }

    /**
     * SecureParam(name="billingSpec", permissions="EDIT")
     *
     * @param BillingSpec $billingSpec
     *
     * @return Response
     */
    public function update(BillingSpec $billingSpec, Request $request)
    {
        $form = $this->createForm(BillingSpecFormType::class, $billingSpec);

        $form->handleRequest($request);

        if ($form->isValid()) {
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
