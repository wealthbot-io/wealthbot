<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 26.12.13
 * Time: 14:40.
 */

namespace Wealthbot\ClientBundle\Tests\Manager;

use Wealthbot\ClientBundle\Entity\ClientPortfolio;
use Wealthbot\ClientBundle\Manager\ClientPortfolioManager;

class ClientPortfolioManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ClientPortfolioManager */
    private $manager;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $om;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $repository;

    public function setUp()
    {
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->repository = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
            ->setMethods(['find', 'findAll', 'findOneBy', 'findBy', 'getClassName'])
            ->getMock();

        $this->om->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('WealthbotClientBundle:ClientPortfolio'))
            ->will($this->returnValue($this->repository));

        $this->repository->expects($this->any())
            ->method('findBy')
            ->with($this->equalTo([
                'client' => $this->getMockClient(),
                'status' => ClientPortfolio::STATUS_CLIENT_ACCEPTED,
                'is_active' => true,
            ]))
            ->will($this->returnCallback([$this, 'getMockPortfolios']));

        $modelManager = $this->getMockModelManager();
        $this->manager = new ClientPortfolioManager($this->om, $modelManager);
    }

    public function testProposePortfolio()
    {
        $client = $this->getMockClient();
        $portfolio = $this->getMockModel();

        $proposedPortfolio = $this->manager->proposePortfolio($client, $portfolio);

        $this->assertSame($this->getMockClient(), $proposedPortfolio->getClient(), 'Invalid client object for proposed portfolio.');
        $this->assertSame($this->getMockModel(), $proposedPortfolio->getPortfolio(), 'Invalid portfolio object for proposed portfolio.');
        $this->assertSame(ClientPortfolio::STATUS_PROPOSED, $proposedPortfolio->getStatus(), 'Invalid status of proposed portfolio.');
    }

    public function testApproveProposedPortfolio()
    {
        $client = $this->getMockClient();
        $this->repository->expects($this->at(0))
            ->method('findOneBy')
            ->with($this->equalTo([
                'client' => $client,
                'status' => ClientPortfolio::STATUS_ADVISOR_APPROVED,
            ]))
            ->will($this->returnValue(null));
        $this->repository->expects($this->at(1))
            ->method('findOneBy')
            ->with($this->equalTo([
                'client' => $client,
                'status' => ClientPortfolio::STATUS_PROPOSED,
            ]))
            ->will($this->returnCallback([$this, 'getMockPortfolio']));

        $approvedPortfolio = $this->manager->approveProposedPortfolio($client);

        $this->assertSame($this->getMockClient(), $approvedPortfolio->getClient(), 'Invalid client object for approved portfolio.');
        $this->assertSame($this->getMockModel(), $approvedPortfolio->getPortfolio(), 'Invalid portfolio object for approved portfolio.');
        $this->assertSame(ClientPortfolio::STATUS_ADVISOR_APPROVED, $approvedPortfolio->getStatus(), 'Invalid status of approved portfolio.');
        $this->assertNotNull($approvedPortfolio->getApprovedAt(), 'Invalid portfolio approved date.');
    }

    public function testApproveProposedPortfolioException2()
    {
        $client = $this->getMockClient();

        $this->setExpectedException(
            'RuntimeException',
            sprintf('Client with id: %s does not have proposed portfolio', $client->getId())
        );

        $this->repository->expects($this->at(0))
            ->method('findOneBy')
            ->with($this->equalTo([
                'client' => $client,
                'status' => ClientPortfolio::STATUS_ADVISOR_APPROVED,
            ]))
            ->will($this->returnValue(null));
        $this->repository->expects($this->at(1))
            ->method('findOneBy')
            ->with($this->equalTo([
                'client' => $client,
                'status' => ClientPortfolio::STATUS_PROPOSED,
            ]))
            ->will($this->returnValue(null));

        $this->manager->approveProposedPortfolio($client);
    }

    public function testAcceptApprovedPortfolio()
    {
        $client = $this->getMockClient();

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo([
                'client' => $client,
                'status' => ClientPortfolio::STATUS_ADVISOR_APPROVED,
            ]))
            ->will($this->returnCallback([$this, 'getMockPortfolio']));

        $acceptedPortfolio = $this->manager->acceptApprovedPortfolio($client);

        $this->assertSame($this->getMockClient(), $acceptedPortfolio->getClient(), 'Invalid client object for accepted portfolio.');
        $this->assertNotNull($acceptedPortfolio->getPortfolio()->getId(), 'Invalid portfolio object for accepted portfolio.');
        $this->assertSame(ClientPortfolio::STATUS_CLIENT_ACCEPTED, $acceptedPortfolio->getStatus(), 'Invalid status of accepted portfolio.');
        $this->assertNotNull($acceptedPortfolio->getAcceptedAt(), 'Invalid portfolio accepted date.');
        $this->assertSame(true, $acceptedPortfolio->getIsActive(), 'Invalid is_active flag value of approved portfolio.');
    }

    public function testAcceptApprovedPortfolioException()
    {
        $client = $this->getMockClient();

        $this->setExpectedException(
            'RuntimeException',
            sprintf('Client with id: %s does not have advisor approved portfolio', $client->getId())
        );

        $this->repository->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo([
                'client' => $client,
                'status' => ClientPortfolio::STATUS_ADVISOR_APPROVED,
            ]))
            ->will($this->returnValue(null));

        $this->manager->acceptApprovedPortfolio($client);
    }

    private function getMockModelManager()
    {
        $mockModelManager = $this->getMockBuilder('Wealthbot\AdminBundle\Manager\CeModelManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mockModelManager->expects($this->any())
            ->method('copyForOwner')
            ->will($this->returnValue($this->getMockModel(null)));

        return $mockModelManager;
    }

    private function getMockClient()
    {
        $mockClient = $this->getMockBuilder('Wealthbot\UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $mockClient->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        $mockClient->setRoles(['ROLE_USER', 'ROLE_CLIENT']);

        return $mockClient;
    }

    private function getMockModel($id = 1)
    {
        $mockModel = $this->getMock('Wealthbot\AdminBundle\Entity\CeModel', ['getId']);

        $mockModel->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        $mockModel->setName('Mock model');

        return $mockModel;
    }

    public function getMockPortfolio(array $params)
    {
        $mockPortfolio = $this->getMock('Wealthbot\ClientBundle\Entity\ClientPortfolio', null);

        $mockPortfolio->setClient($this->getMockClient());
        $mockPortfolio->setPortfolio($this->getMockModel());

        if (isset($params['status'])) {
            $mockPortfolio->setStatus($params['status']);
        }

        if (isset($params['is_active'])) {
            $mockPortfolio->setIsActive($params['is_active']);
        }

        return $mockPortfolio;
    }

    public function getMockPortfolios(array $params)
    {
        return [$this->getMockPortfolio($params)];
    }
}
