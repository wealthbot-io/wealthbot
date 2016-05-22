<?php

namespace Wealthbot\RiaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Wealthbot\RiaBundle\Entity\RiaCompanyInformation;
use Wealthbot\RiaBundle\Form\Type\RiaCompanyInformationThreeType;
use Wealthbot\RiaBundle\Form\Type\RiaCompanyProfileFormType;
use Wealthbot\UserBundle\Entity\Profile;
use Wealthbot\UserBundle\Entity\User;

class ProfileController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('WealthbotRiaBundle:Default:index.html.twig', ['name' => $name]);
    }

    public function companyProfileAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $ceModelManager = $this->get('wealthbot_admin.ce_model_manager');

        /** @var User $ria */
        $ria = $this->getUser();

        $isPreSave = $request->isXmlHttpRequest();

        $form = $this->createForm(new RiaCompanyProfileFormType($isPreSave), $ria->getRiaCompanyInformation());

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

        return $this->render('WealthbotRiaBundle:Profile:company_profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function completeSubclassesAction(Request $request)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');
        $riaCompanyInfoRepo = $em->getRepository('WealthbotRiaBundle:RiaCompanyInformation');
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
            new RiaCompanyInformationThreeType($em, $this->getUser(), false),
            $riaCompanyInfo,
            ['session' => $session]
        );

        return $this->render('WealthbotRiaBundle:Profile:subclasses_form_field.html.twig', ['form' => $form->createView()]);
    }

    public function checkCompanySlugAction(Request $request)
    {
        $ria = $this->getUser();

        $em = $this->get('doctrine.orm.entity_manager');
        $slug = $request->get('slug');
        $exist = $em->getRepository('WealthbotRiaBundle:RiaCompanyInformation')->findBySlugAndNotRiaUserId(
            $slug,
            $ria->getId()
        );

        return $this->getJsonResponse(['is_valid' => (preg_match('/^[a-zA-Z0-9]+$/', $slug) && !$exist)]);
    }

    protected function getJsonResponse(array $data, $code = 200)
    {
        $response = json_encode($data);

        return new Response($response, $code, ['Content-Type' => 'application/json']);
    }
}
