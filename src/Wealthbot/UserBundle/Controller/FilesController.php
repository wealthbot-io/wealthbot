<?php

namespace Wealthbot\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Wealthbot\UserBundle\Entity\Document;

class FilesController extends Controller
{
    public function logoAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $ria = $em->getRepository('WealthbotUserBundle:User')->find($request->get('ria_id'));
        if (!$ria) {
            throw $this->createNotFoundException();
        }

        $defaultLogoPath = $this->get('kernel')->getRootDir().'/../web/img/logo.png';
        $companyInformation = $ria->getRiaCompanyInformation();
        if ($companyInformation && $companyInformation->getLogo()) {
            $logoPath = $this->container->getParameter('uploads_ria_company_logos_dir').'/'.$companyInformation->getLogo();
        } else {
            $logoPath = $defaultLogoPath;
        }

        try {
            $file = new File($logoPath);
        } catch (FileNotFoundException $e) {
            $file = new File($defaultLogoPath);
        }

        return $this->prepareResponse($file, $companyInformation->getName().'_logo.'.$file->getExtension());
    }

    public function documentsAction($filename, $originalName = null)
    {
        $documentManager = $this->get('wealthbot_user.document_manager');

        try {
            $file = new File($this->container->getParameter('uploads_documents_dir').'/'.$filename);
        } catch (FileNotFoundException $e) {
            throw $this->createNotFoundException('File does not exist.');
        }

        if ($file->getExtension() === 'zip') {
            if (null === $originalName) {
                $originalName = $filename;
            }
        } else {
            $document = $documentManager->findDocumentBy(['filename' => $filename]);
            if (!$document) {
                throw $this->createNotFoundException('Document does not exist.');
            }

            if (null === $originalName) {
                $originalName = $document->getOriginalName();
            }
        }

        return $this->prepareResponse($file, $originalName);
    }

    public function tradeFileAction($filename, $originalName = null)
    {
        try {
            $file = new File($this->container->getParameter('uploads_trade_files_dir').'/'.$filename);
        } catch (FileNotFoundException $e) {
            throw $this->createNotFoundException('File does not exist.');
        }

        return $this->prepareResponse($file, $originalName);
    }

    private function prepareResponse(File $file, $filename = null)
    {
        $mimeType = $file->getMimeType();

        if ($mimeType === 'application/pdf') {
            $disposition = 'inline';
        } else {
            $disposition = 'attachment';
        }

        $response = new Response();

        $response->headers->set('Cache-Control', 'private');
        $response->headers->set('Content-Type', $mimeType);

        $response->headers->set('Content-Disposition', $disposition.'; filename="'.($filename ? $filename : $file->getFilename()).'"');
        $response->headers->set('Content-Length', $file->getSize());

        $response->sendHeaders();
        readfile($file);

        return $response;
    }
}
