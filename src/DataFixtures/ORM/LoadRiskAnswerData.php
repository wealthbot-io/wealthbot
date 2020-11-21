<?php

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\RiskAnswer;

class LoadRiskAnswerData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $riskAnswer11 = new RiskAnswer();
        $riskAnswer11->setTitle('Sell all of your investments');
        $riskAnswer11->setQuestion($this->getReference('risk-question-1'));
        $manager->persist($riskAnswer11);
        $this->setReference('risk-answer-1-1', $riskAnswer11);

        $riskAnswer12 = new RiskAnswer();
        $riskAnswer12->setTitle('Sell some');
        $riskAnswer12->setQuestion($this->getReference('risk-question-1'));
        $manager->persist($riskAnswer12);
        $this->setReference('risk-answer-1-2', $riskAnswer12);

        $riskAnswer13 = new RiskAnswer();
        $riskAnswer13->setTitle('Keep all');
        $riskAnswer13->setQuestion($this->getReference('risk-question-1'));
        $manager->persist($riskAnswer13);
        $this->setReference('risk-answer-1-3', $riskAnswer13);

        $riskAnswer14 = new RiskAnswer();
        $riskAnswer14->setTitle('Buy more');
        $riskAnswer14->setQuestion($this->getReference('risk-question-1'));
        $manager->persist($riskAnswer14);
        $this->setReference('risk-answer-1-4', $riskAnswer14);

        $riskAnswer21 = new RiskAnswer();
        $riskAnswer21->setTitle('+/- 15%');
        $riskAnswer21->setQuestion($this->getReference('risk-question-2'));
        $manager->persist($riskAnswer21);
        $this->setReference('risk-answer-2-1', $riskAnswer21);

        $riskAnswer22 = new RiskAnswer();
        $riskAnswer22->setTitle('+/- 10%');
        $riskAnswer22->setQuestion($this->getReference('risk-question-2'));
        $manager->persist($riskAnswer22);
        $this->setReference('risk-answer-2-2', $riskAnswer22);

        $riskAnswer23 = new RiskAnswer();
        $riskAnswer23->setTitle('+/- 5%');
        $riskAnswer23->setQuestion($this->getReference('risk-question-2'));
        $manager->persist($riskAnswer23);
        $this->setReference('risk-answer-2-3', $riskAnswer23);

        $riskAnswer31 = new RiskAnswer();
        $riskAnswer31->setTitle('Interested in steady returns if I accept lower returns');
        $riskAnswer31->setQuestion($this->getReference('risk-question-3'));
        $manager->persist($riskAnswer31);
        $this->setReference('risk-answer-3-1', $riskAnswer31);

        $riskAnswer32 = new RiskAnswer();
        $riskAnswer32->setTitle('Interested in maximizing long term gains, even if I experience short term losses');
        $riskAnswer32->setQuestion($this->getReference('risk-question-3'));
        $manager->persist($riskAnswer32);
        $this->setReference('risk-answer-3-2', $riskAnswer32);

        $riskAnswer41 = new RiskAnswer();
        $riskAnswer41->setTitle('Yes');
        $riskAnswer41->setQuestion($this->getReference('risk-question-4'));
        $manager->persist($riskAnswer41);
        $this->setReference('risk-answer-4-1', $riskAnswer41);

        $riskAnswer42 = new RiskAnswer();
        $riskAnswer42->setTitle('No');
        $riskAnswer42->setQuestion($this->getReference('risk-question-4'));
        $manager->persist($riskAnswer42);
        $this->setReference('risk-answer-4-2', $riskAnswer42);

        $manager->flush();
    }

    public function getOrder()
    {
        return 3;
    }
}
