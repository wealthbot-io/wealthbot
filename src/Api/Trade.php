<?php


namespace App\Api;


use App\Entity\ClientAccount;
use App\Entity\ClientPortfolio;
use App\Entity\ClientQuestionnaireAnswer;
use App\Entity\Lot;
use App\Entity\Position;
use App\Entity\Security;
use App\Entity\SystemAccount;
use App\Entity\Transaction;
use App\Entity\TransactionType;

/**
 * Trait Trade
 * @package App\Api
 */
trait Trade
{


    /**
     * Buy/Sell logic
     * @param $data
     * @param $em
     * @throws \Exception
     */
    protected function buyOrSell($data, $account) {

        if(isset($data['portfolio'])) {
            /** @var ClientPortfolio $portfolio */
            $portfolio = $this->em->getRepository('App\\Entity\\ClientPortfolio')->find($data['portfolio']);

            $answers = $portfolio->getClient()->getAnswers();

            $point = 0;
            foreach($answers as $answer){
                /** @var ClientQuestionnaireAnswer $answer */
                $point += $answer->getAnswer()->getPoint();

            };

            $point = ($point / 100) + 1;

            foreach ($data['values'] as $datum) {
                if ($point - $datum['prices_diff'] > 0) {

                    if ($datum['prices_diff'] > 1) {
                        $this->sell($datum, $account);
                    } else {
                        $this->buy($datum, $account);
                    }
                } else {
                    if ($datum['prices_diff'] > 1) {
                        $this->sell($datum, $account);
                    } else {
                        $this->buy($datum, $account);
                    }
                }

            }
        }

    }

    /**
     * Sell
     * @param $info
     * @param $account_id
     * @param $em
     * @throws \Exception
     */
    protected function sell($info, ClientAccount $account){

        /** @var Security $security */
        $security = $this->em->getRepository("App\\Entity\\Security")->find($info['security_id']);
        /** @var SystemAccount $systemAccount */
        $systemAccount = $account->getSystemAccount();

       /// try {
            $account_id = 'VA000000';

            $result = $this->placeOrder($account_id, $security->getSymbol(),
                'sell',
                $info['amount'],
                $this->getLatestPriceBySecurityId($security->getId()));

            dump($result);
       /// } catch (\Exception $e){

      ////  }


        $transactionType = new TransactionType();
        $transactionType
            ->setName('SELL')
            ->setActivity('sell')
            ->setReportAs(null)
            ->setDescription('Sell '. $security->getSymbol())
            ->setActivity('sell');


        $this->em->persist($transactionType);



        $lot = new Lot();
        $position = new Position();
        $position->setSecurity($security)
            ->setDate(new \DateTime('now'))
            ->setAmount($info['amount'])
            ->setLots([$lot]);
        $position->setClientSystemAccount($systemAccount);
        $position->setQuantity(1);
        $position->setStatus(Position::POSITION_STATUS_IS_OPEN);

        $lot->setAmount($info['amount']);
        $lot->setClientSystemAccount($systemAccount);
        $lot->setStatus(Lot::LOT_IS_OPEN);
        $lot->setDate(new \DateTime('now'));
        $lot->setQuantity(1);
        $lot->setSecurity($security);
        $lot->setCostBasisKnown(true);
        $lot->setCostBasis($this->getLatestPriceBySecurityId($security->getId()));
        $lot->setWashSale(false);
        $lot->setPosition($position);

        $this->em->persist($lot);
        $this->em->persist($position);



        $transaction = new Transaction();
        $security = $this->em->getRepository("App\\Entity\\Security")->find($info['security_id']);
        $transaction->setSecurity($security);
        $transaction->setQty($info['amount']);
        $transaction->setAccount($systemAccount);
        $transaction->setTransactionType($transactionType);
        $transaction->setTxDate(new \DateTime('now'));
        $transaction->setLot($lot);
        $this->em->persist($transaction);


        $this->em->flush();


    }

    /**
     * Buy
     * @param $info
     * @param $account_id
     * @param $em
     * @throws \Exception
     */
    protected function buy($info, ClientAccount $account){

        /** @var Security $security */
        $security = $this->em->getRepository("App\\Entity\\Security")->find($info['security_id']);
        /** @var SystemAccount $systemAccount */
        $systemAccount = $account->getSystemAccount();


       // try {
            $account_id = 'VA000000';

            $result = $this->placeOrder($account_id, $security->getSymbol(),
                'buy',
                $info['amount'],
                $this->getLatestPriceBySecurityId($security->getId()));

            dump($result);
       /// } catch (\Exception $e){

       // }



        $transactionType = new TransactionType();
        $transactionType
            ->setName('BUY')
            ->setActivity('buy')
            ->setReportAs(null)
            ->setDescription('Buy '. $security->getSymbol())
            ->setActivity('buy');

        $this->em->persist($transactionType);


        $lot = new Lot();
        $position = new Position();
        $position->setSecurity($security)
            ->setDate(new \DateTime('now'))
            ->setAmount($info['amount'])
            ->setLots([$lot]);
        $position->setClientSystemAccount($systemAccount);
        $position->setQuantity(1);
        $position->setStatus(Position::POSITION_STATUS_IS_OPEN);

        $lot->setAmount($info['amount']);
        $lot->setClientSystemAccount($systemAccount);
        $lot->setStatus(Lot::LOT_IS_OPEN);
        $lot->setDate(new \DateTime('now'));
        $lot->setQuantity(1);
        $lot->setSecurity($security);
        $lot->setCostBasisKnown(true);
        $lot->setCostBasis($this->getLatestPriceBySecurityId($security->getId()));
        $lot->setWashSale(false);
        $lot->setPosition($position);

        $this->em->persist($lot);
        $this->em->persist($position);

        $transaction = new Transaction();
        $transaction->setSecurity($security);
        $transaction->setQty($info['amount']);
        $transaction->setAccount($systemAccount);
        $transaction->setTransactionType($transactionType);
        $transaction->setTxDate(new \DateTime('now'));
        $transaction->setLot($lot);
        $this->em->persist($transaction);
        $this->em->flush();
    }
}