<?php

namespace Wealthbot\RiaBundle\Controller;

use JMS\Serializer\SerializationContext;
use JMS\SecurityExtraBundle\Annotation\SecureParam;
use Wealthbot\ClientBundle\Model\SystemAccount;
use Wealthbot\ClientBundle\Entity\BillItem;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


class BillItemController extends Controller
{
    /**
     * API
     *
     * Update bill item fee
     *
     * @SecureParam(name="billItem", permissions="CHANGE_FEE")
     * @ParamConverter("billItem", class="WealthbotClientBundle:BillItem")
     * @param \Wealthbot\ClientBundle\Entity\BillItem $billItem
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return JsonResponse
     */
    public function updateFeeAction(BillItem $billItem, Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $feeBilled = (float) $request->get('feeBilled', 0);
        $feeCollected = (float) $request->get('feeCollected', 0);

        $billItem
            ->setFeeBilled($feeBilled)
            ->setFeeCollected($feeCollected)
        ;

        $em->persist($billItem);
        $em->flush();

        return new JsonResponse(array(), 200);
    }
}