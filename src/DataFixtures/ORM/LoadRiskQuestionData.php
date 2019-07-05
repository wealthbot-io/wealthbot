<?php

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\RiskQuestion;
use App\Entity\User;

class LoadRiskQuestionData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var User $adminUser */
        $adminUser = $this->getReference('user-admin');

        $riskQuestion1 = new RiskQuestion();
        $riskQuestion1->setOwner($adminUser);
        $riskQuestion1->setTitle('The global stock market is often volatile. If your entire investment portfolio lost 10% of its value in a month during a market decline, what would you do?');
        $riskQuestion1->setDescription('Lorem ipsum');
        $riskQuestion1->setSequence(2);
        $manager->persist($riskQuestion1);

        $riskQuestion2 = new RiskQuestion();
        $riskQuestion2->setOwner($adminUser);
        $riskQuestion2->setTitle('Which set of hypothetical portfolio returns in a year is most acceptable to you?');
        $riskQuestion2->setDescription('Lorem ipsum');
        $riskQuestion2->setSequence(3);
        $manager->persist($riskQuestion2);

        $riskQuestion3 = new RiskQuestion();
        $riskQuestion3->setOwner($adminUser);
        $riskQuestion3->setTitle('Choose the statement that best reflects your thoughts about reaching your financial goal:');
        $riskQuestion3->setDescription('Lorem ipsum');
        $riskQuestion3->setSequence(4);
        $manager->persist($riskQuestion3);

        $riskQuestion4 = new RiskQuestion();
        $riskQuestion4->setOwner($adminUser);
        $riskQuestion4->setTitle('Will you need up to ¼ of your portfolio within the next 10 years for a large expense (house, college, etc.)?');
        $riskQuestion4->setDescription('Lorem ipsum');
        $riskQuestion4->setSequence(5);
        $manager->persist($riskQuestion4);

        $manager->flush();

        $this->addReference('risk-question-1', $riskQuestion1);
        $this->addReference('risk-question-2', $riskQuestion2);
        $this->addReference('risk-question-3', $riskQuestion3);
        $this->addReference('risk-question-4', $riskQuestion4);
    }

    public function getOrder()
    {
        return 2;
    }
}
