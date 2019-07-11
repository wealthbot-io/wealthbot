<?php

namespace App\Controller\Ria;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\RiaCompanyInformation;
use App\Form\Type\RiaCompanyInformationThreeType;
use App\Form\Type\RiaCompanyProfileFormType;
use App\Entity\Profile;
use App\Entity\User;

class ProfileController extends Controller
{
    public function index($name)
    {
        return $this->render('/Ria/Default/index.html.twig', ['name' => $name]);
    }

    public function companyProfile(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $ceModelManager = $this->get('wealthbot_admin.ce_model_manager');

        /** @var User $ria */
        $ria = $this->getUser();

        $isPreSave = $request->isXmlHttpRequest();

        $form = $this->createForm(RiaCompanyProfileFormType::class, $ria->getRiaCompanyInformation());

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var RiaCompanyInformation $riaCompanyInformation */
                $riaCompanyInformation = $form->getData();

                if (!$isPreSave) {
                    /** @var Profile $profile */
                    $profile = $ria->getProfile();
                    $profile->setRegistrationStep(5);
                    $em->persist($profile);

                    $parentModel = $ceModelManager->createCustomModel($ria);
                    $em->persist($parentModel);

                    $riaCompanyInformation->setPortfolioModel($parentModel);
                    $em->persist($riaCompanyInformation);

                    $em->flush();

                    return $this->redirect($this->generateUrl('rx_ria_user_management'));
                }

                $em->persist($riaCompanyInformation);

                $em->flush();
            }
        }

        return $this->render('/Ria/Profile/company_profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function completeSubclasses(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $riaCompanyInfoRepo = $em->getRepository('App\Entity\RiaCompanyInformation');
        $session = $this->get('session');

        $riaCompanyInfo = $riaCompanyInfoRepo->createQueryBuilder('rci')
            ->leftJoin('rci.portfolioModel', 'pm')
            ->where('rci.ria_user_id = :ria_user_id')
            ->setParameter('ria_user_id', $this->getUser()->getId())
            ->getQuery()
            ->getOneOrNullResult();

        $requestData = $request->get('wealthbot_riabundle_riacompanyinformationtype');
        if (isset($requestData['strategy_model'])) {
            $riaCompanyInfo->setPortfolioModelId($requestData['strategy_model']);
        }

        $form = $this->createForm(
            RiaCompanyInformationThreeType::class,
            $riaCompanyInfo,
            [
                'em'=>$em,
                'user'=> $this->getUser(),
                'session' => $session,
                'isPreSave' => false
            ]
        );

        return $this->render('/Ria/Profile/subclasses_form_field.html.twig', ['form' => $form->createView()]);
    }

    public function checkCompanySlug(Request $request)
    {
        $ria = $this->getUser();

        $em = $this->get('doctrine.orm.entity_manager');
        $slug = $request->get('slug');
        $exist = $em->getRepository('App\Entity\RiaCompanyInformation')->findBySlugAndNotRiaUserId(
            $slug,
            $ria->getId()
        );

        return $this->json(['is_valid' => (preg_match('/^[a-zA-Z0-9]+$/', $slug) && !$exist)]);
    }
}
