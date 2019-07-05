<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 05.04.13
 * Time: 15:05
 * To change this template use File | Settings | File Templates.
 */

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\ClosingAccountMessage;

class LoadClosingAccountMessageData extends AbstractFixture implements OrderedFixtureInterface
{
    private $messages = [
        'Consolidating accounts.',
        'Using money in account for something else.',
        'Not happy with advisor.',
        'Does not like web interface.',
        'Moving to another advisor.',
        'Going to manage their account themselves.',
    ];

    public function load(ObjectManager $manager)
    {
        foreach ($this->messages as $message) {
            $messageObject = new ClosingAccountMessage();
            $messageObject->setMessage($message);

            $manager->persist($messageObject);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
