<?php

namespace Test\Model\WealthbotRebalancer\Repository;

require_once(__DIR__ . '/../../../../../AutoLoader.php');
\AutoLoader::registerAutoloader();

use Model\WealthbotRebalancer\Repository\RiaCompanyInformationRepository;
use Model\WealthbotRebalancer\Repository\RiaRepository;
use Model\WealthbotRebalancer\Ria;
use Test\Suit\ExtendedTestCase;

class RiaCompanyInformationRepositoryTest extends ExtendedTestCase
{
   /** @var RiaCompanyInformationRepository */
   private $riaCompanyInfoRepo;

   public function setUp()
   {
       $this->riaCompanyInfoRepo = new RiaCompanyInformationRepository();
   }

   public function testFindOneByRia()
   {
       $riaRepo = new RiaRepository();
       $ria = $riaRepo->findOneBy(array(
           'email' => 'johnny@wealthbot.io'
       ));

       $riaCompanyInformation = $this->riaCompanyInfoRepo->findOneByRia($ria);
       $this->assertEquals($ria, $riaCompanyInformation->getRia());

       $this->assertNotNull($riaCompanyInformation->getId());
       $this->assertTrue($riaCompanyInformation->getUseTransactionFees());
   }
}