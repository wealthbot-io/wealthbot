<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 26.09.12
 * Time: 12:16
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\FixturesBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Wealthbot\AdminBundle\Entity\State;

class LoadStateData extends AbstractFixture implements OrderedFixtureInterface
{
    private $states = array(
        array('Alabama', 'AL'),
        array('Alaska', 'AK'),
        array('Arizona', 'AZ'),
        array('Arkansas', 'AR'),
        array('California', 'CA'),
        array('Colorado', 'CO'),
        array('Connecticut', 'CT'),
        array('Delaware', 'DE'),
        array('Florida', 'FL'),
        array('Georgia', 'GA'),
        array('Hawaii', 'HI'),
        array('Idaho', 'ID'),
        array('Illinois', 'IL'),
        array('Indiana', 'IN'),
        array('Iowa', 'IA'),
        array('Kansas', 'K'),
        array('Kentucky', 'KY'),
        array('Louisiana', 'LA'),
        array('Maine', 'ME'),
        array('Maryland', 'MD'),
        array('Massachusetts', 'MA'),
        array('Michigan', 'MI'),
        array('Minnesota', 'MN'),
        array('Mississippi', 'MS'),
        array('Missouri', 'MO'),
        array('Montana', 'MT'),
        array('Nebraska', 'NE'),
        array('Nevada', 'NV'),
        array('New Hampshire', 'NH'),
        array('New Jersey', 'NJ'),
        array('New Mexico', 'NM'),
        array('New York', 'NY'),
        array('North Carolina', 'NC'),
        array('North Dakota', 'ND'),
        array('Ohio', 'OH'),
        array('Oklahoma', 'OK'),
        array('Oregon', 'OR'),
        array('Pennsylvania', 'PA'),
        array('Rhode Island', 'RI'),
        array('South Carolina', 'SC'),
        array('South Dakota', 'SD'),
        array('Tennessee', 'TN'),
        array('Texas', 'TX'),
        array('Utah', 'UT'),
        array('Vermont', 'VT'),
        array('Virginia', 'VA'),
        array('Washington', 'WA'),
        array('West Virginia', 'WV'),
        array('Wisconsin', 'WI'),
        array('Wyoming', 'WY')
    );

    public function load(ObjectManager $manager)
    {
        foreach ($this->states as $item) {
            $state = new State();
            $state->setName($item[0]);
            $state->setAbbr($item[1]);

            $manager->persist($state);

            $this->addReference('state-'.$item[0], $state);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 1;
    }
}
