<?php
// tests/ApplicationAvailabilityFunctionalTest.php
namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApplicationAvailabilityFunctionalTest extends WebTestCase
{
    /**
     * @dataProvider urlProvider
     */
    public function testPageIsSuccessful($url)
    {
        $url = $this->urlProvider();
        $client = self::createClient();
        $client->request('GET', $url);
        $this->assertTrue($client->getResponse()->isOk());
    }

    public function urlProvider()
    {
        yield ['/'];
    }
}