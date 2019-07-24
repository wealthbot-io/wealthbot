<?php


namespace App\Api;


use App\Entity\SecurityPrice;

class BaseRebalancer
{


    /**
     * Sets api key and api secret
     * @throws \Exception
     */
    protected function setApiKey(){
        if($this->security->getUser()) {
            if ($this->security->getUser()->hasRole('ROLE_ADMIN')) {
                $this->ria = $this->getDoctrine()->getRepository('App\Entity\User')->findOneByEmail('raiden@wealthbot.io');
            } else if ($this->security->getUser()->hasRole('ROLE_RIA')) {
                $this->ria = $this->security->getUser();
            } else {
                $this->ria = $this->security->getUser()->getRia();
            }
            $this->apiKey = $this->ria ? $this->ria->getRiaCompanyInformation()->getCustodianKey() : " ";
            $this->apiSecret = $this->ria ? $this->ria->getRiaCompanyInformation()->getCustodianSecret() : " ";
        } else  {
            $this->apiKey = $this->container->getParameter('tradier_api_key');
            $this->apiSecret = $this->container->getParameter('tradier_api_secret');
        }
    }



    /**
     * @param $em
     * @param $securities
     * @return array
     */
    protected function processPrices($securities)
    {
        $prices = [];
        foreach ($securities as $security){

            $twoPrices  = $this->em->getRepository("App\Entity\SecurityPrice")->findBy(
                ['security_id'=>$security->getId()],[
                'datetime' => 'desc'
            ],2,0
            );
            $prices[] = [
                'security_id' => $security->getId(),
                'old_price' => isset($twoPrices[1]) ? $twoPrices[1]->getPrice() : 0,
                'price' => isset($twoPrices[0]) ? $twoPrices[0]->getPrice() : 0
            ];

            foreach($prices as $key => $price){
                if($price == 0){
                    unset($prices[$key]);
                }
            }
        }

        return $prices;
    }


    /**
     * @param $id
     * @return mixed
     */
    protected function getPricesDiff($id)
    {
        foreach($this->prices as $price){
            if($price['security_id'] == $id){
                return $price['price'] / $price['old_price'];
            }
        }
    }

    protected function getLatestPriceBySecurityId($id)
    {
        foreach($this->prices as $price){
            if($price['security_id'] == $id){
                return $price['price'];
            }
        }
    }


    /**
     * @return object[]
     */
    public function updateSecurities()
    {

        $securities = $this->em->getRepository('App\Entity\Security')->findAll();
        $symbols = implode(",",array_map(function($security){
            return $security->getSymbol();
        },$securities));
        $quotes = json_decode($this->getQuotes($symbols));
        foreach($quotes->quotes->quote as $quote){
            if(isset($quote->last)) {
                $security = $this->em->getRepository('App\Entity\Security')->findOneBySymbol($quote->symbol);
                $price = new SecurityPrice();
                $price->setSecurity($security);
                $price->setSecurityId($security->getId());
                $price->setDatetime(new \DateTime('now'));
                $price->setIsCurrent(true);
                $price->setPrice($quote->last);
                $price->setIsPosted(true);
                $price->setSource("tradier");
                $this->em->persist($price);
            }
        };

        $this->em->flush();

        return $securities;
    }


}