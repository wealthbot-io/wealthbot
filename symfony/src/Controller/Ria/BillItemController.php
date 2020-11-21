<?php

namespace App\Controller\Ria;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\BillItem;

class BillItemController extends Controller
{
    /**
     * API.
     *
     * Update bill item fee
     *
     * SecureParam(name="billItem", permissions="CHANGE_FEE")
     * @ParamConverter("billItem", class="App\Entity\BillItem")
     *
     * @param \App\Entity\BillItem                   $billItem
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return JsonResponse
     */
    public function updateFee(BillItem $billItem, Request $request)
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

        return new JsonResponse([], 200);
    }
}
