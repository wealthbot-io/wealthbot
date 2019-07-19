<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 15.07.13
 * Time: 13:05
 * To change this template use File | Settings | File Templates.
 */

namespace App\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\CeModel;
use App\Entity\Custodian;
use App\Manager\CeModelManager;
use App\Entity\AdvisorCode;
use App\Entity\RiaCompanyInformation;
use App\Repository\RiskQuestionRepository;
use App\Entity\Document;
use App\Entity\Profile;
use App\Entity\User;

class LoadRiaData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $riaUser = $this->createUser();
        $manager->persist($riaUser);
        $this->addReference('user-ria', $riaUser);

        $riaCompanyInformation = $this->createRiaCompanyInformation($manager, $riaUser);
        $this->createAdvisorCode($manager, $riaCompanyInformation);

        //look fees in LoadBillingSpecData

        $this->createRiaRiskQuestionnaire($manager, $riaUser);

        $blankRia = $this->createBlankRia();
        $manager->persist($blankRia);

        $this->createRiaCompanyInformation($manager, $blankRia);
        $manager->flush();
    }

    protected function addDocumentForOwner(User $owner, Document $document)
    {
        if (!$owner->getUserDocuments()->contains($document)) {
            $owner->addUserDocument($document);
        }
    }

    protected function createBlankRia()
    {
        $riaUser = new User();
        $riaUser->setUsername('blankria');
        $riaUser->setEmail('blankria@example.com');
        $riaUser->setPlainPassword('blankria');
        $riaUser->setEnabled(true);
        $riaUser->setRoles(['ROLE_RIA']);

        $riaUserProfile = new Profile();
        $riaUserProfile->setUser($riaUser);
        $riaUserProfile->setCompany('Blank RIA');
        $riaUserProfile->setFirstName('Blank RiaFirst');
        $riaUserProfile->setLastName('Blank RiaLast');

        $riaUser->setProfile($riaUserProfile);

        return $riaUser;
    }

    private function createUser()
    {
        $riaUser = new User();
        $riaUser->setUsername('ria');
        $riaUser->setEmail('ria@example.com');
        $riaUser->setPlainPassword('ria');
        $riaUser->setEnabled(true);
        $riaUser->setRoles(['ROLE_RIA']);

        $riaUserProfile = new Profile();
        $riaUserProfile->setUser($riaUser);
        $riaUserProfile->setCompany('Wealthbot');
        $riaUserProfile->setFirstName('RiaFirst');
        $riaUserProfile->setLastName('RiaLast');
        $riaUserProfile->setRegistrationStep(5);

        $riaUser->setProfile($riaUserProfile);

        return $riaUser;
    }

    private function createRiaCompanyInformation(ObjectManager $manager, User $riaUser)
    {
        $riaCompanyInfo = new RiaCompanyInformation();

        $riaCompanyInfo->setRia($riaUser);
        $riaCompanyInfo->setAccountManaged(1);
        $riaCompanyInfo->setAddress('Ekvator');
        $riaCompanyInfo->setCity('Nsk');
        $riaCompanyInfo->setAdvCopy('none.pdf');
        $riaCompanyInfo->setClientsTaxBracket(0.12);
        $riaCompanyInfo->setContactEmail('contact_'.$riaUser->getEmail());
        $riaCompanyInfo->setIsAllowRetirementPlan(true);
        $riaCompanyInfo->setIsSearchableDb(true);
        $riaCompanyInfo->setIsShowClientExpectedAssetClass(true);
        $riaCompanyInfo->setMinAssetSize(10000);
        $riaCompanyInfo->setMinimumBillingFee(10);
        $riaCompanyInfo->setName('Wealthbot');
        $riaCompanyInfo->setOffice('408');
        $riaCompanyInfo->setState($this->getReference('state-Nevada'));
        $riaCompanyInfo->setPhoneNumber('3333333333');
        $riaCompanyInfo->setPortfolioModel(null);
        $riaCompanyInfo->setPrimaryFirstName('RiaFirst');
        $riaCompanyInfo->setPrimaryLastName('RiaLast');
        $riaCompanyInfo->setRebalancedFrequency(1);
        $riaCompanyInfo->setRebalancedMethod(1);
        $riaCompanyInfo->setRiskAdjustment(1);
        $riaCompanyInfo->setUseMunicipalBond(true);
        $riaCompanyInfo->setWebsite('leningrad.com');
        $riaCompanyInfo->setZipcode('12334');
        $riaCompanyInfo->setAllowNonElectronicallySigning(true);

        $riaCompanyInfo->setPortfolioModel($this->createRiaPortfolioModel($manager, $riaUser));
        $riaCompanyInfo->setPortfolioProcessing(RiaCompanyInformation::PORTFOLIO_PROCESSING_COLLABORATIVE);
        $riaCompanyInfo->setTaxLossHarvestingMinimumPercent(10);

        /** @var Custodian $custodian */
        $custodian = $this->getReference('custodian');
        $riaCompanyInfo->setCustodian($custodian);
        $manager->persist($riaCompanyInfo);

        return $riaCompanyInfo;
    }

    private function createAdvisorCode(ObjectManager $manager, RiaCompanyInformation $riaCompanyInfo)
    {
        $custodian = $this->getReference('custodian');

        $advisorCode = new AdvisorCode();
        $advisorCode->setCustodian($custodian);
        $advisorCode->setRiaCompany($riaCompanyInfo);
        $advisorCode->setName('ABC');
        $manager->persist($advisorCode);

        $advisorCode1 = clone $advisorCode;
        $advisorCode1->setName('AH5');
        $manager->persist($advisorCode1);

        $advisorCode2 = clone $advisorCode;
        $advisorCode2->setName('YKR');
        $manager->persist($advisorCode2);
    }

    private function createRiaPortfolioModel(ObjectManager $manager, User $riaUser)
    {
        /** @var CeModel $strategy */
        $strategy = $this->getReference('strategy-1');
        $modelManager = new CeModelManager($manager, '\App\Entity\CeModel');

        $riaModel = $modelManager->copyForOwner($strategy, $riaUser);
        $manager->persist($riaModel);

        return $riaModel;
    }

    private function createRiaRiskQuestionnaire(ObjectManager $manager, User $riaUser)
    {
        /** @var RiskQuestionRepository $repo */
        $repo = $manager->getRepository('App\Entity\RiskQuestion');

        $questions = $repo->getClonedAdminQuestions($riaUser);
        foreach ($questions as $index => $question) {
            $this->addReference('ria-risk-question-'.($index + 1), $question);

            foreach ($question->getAnswers() as $answerIndex => $answer) {
                $this->addReference('ria-risk-answer-'.($index + 1).'-'.($answerIndex + 1), $answer);
            }
        }
    }

    public function getOrder()
    {
        return 6;
    }
}
