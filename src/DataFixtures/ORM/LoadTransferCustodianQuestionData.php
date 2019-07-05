<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 29.08.13
 * Time: 13:49
 * To change this template use File | Settings | File Templates.
 */

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\TransferCustodian;
use App\Entity\TransferCustodianQuestion;

class LoadTransferCustodianQuestionData extends AbstractFixture implements OrderedFixtureInterface
{
    private $data = [
        [
            'transfer_custodian_index' => 14,
            'title' => 'Is this a personal bank account holding a CD?',
            'docusign_eligible_answer' => false,
        ],
        [
            'transfer_custodian_index' => 62,
            'title' => 'Are there 9 characters in the account number with all being numbers or the first character as a letter?',
            'docusign_eligible_answer' => true,
        ],
        [
            'transfer_custodian_index' => 63,
            'title' => 'Are there 9 characters in the account number?',
            'docusign_eligible_answer' => true,
        ],
        [
            'transfer_custodian_index' => 66,
            'title' => 'Are there 8 characters in the account number?',
            'docusign_eligible_answer' => true,
        ],
        [
            'transfer_custodian_index' => 75,
            'title' => 'Are there 12 characters in the account number?',
            'docusign_eligible_answer' => false,
        ],
        [
            'transfer_custodian_index' => 237,
            'title' => 'Are there 8 characters in the account number?',
            'docusign_eligible_answer' => true,
        ],
    ];

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->data as $item) {
            /** @var TransferCustodian $custodian */
            $custodian = $this->getReference('transfer-custodian-'.$item['transfer_custodian_index']);

            $question = new TransferCustodianQuestion();
            $question->setTransferCustodian($custodian);
            $question->setTitle($item['title']);
            $question->setDocusignEligibleAnswer($item['docusign_eligible_answer']);

            $manager->persist($question);
            $this->addReference('transfer-custodian-'.$item['transfer_custodian_index'].'-question', $question);
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 2;
    }
}
