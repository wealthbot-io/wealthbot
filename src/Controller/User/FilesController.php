<?php

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FilesController extends Controller
{
    public function logo(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');

        $ria = $em->getRepository('App\Entity\User')->find($request->get('ria_id'));
        if (!$ria) {
            throw $this->createNotFoundException();
        }
        $defaultLogoPath = getcwd().'/img/logo.png';
        $companyInformation = $ria->getRiaCompanyInformation();
        if ($companyInformation->getLogo()) {
            $logoPath = $this->container->getParameter('uploads_ria_company_logos_dir').'/'.$companyInformation->getLogo();
        };

        $logo =  $logoPath ?? $defaultLogoPath;
        return new BinaryFileResponse($logo);
    }

    public function documents($filename, $originalName = null)
    {
        $documentManager = $this->get('wealthbot_user.document_manager');

        try {
            $file = new File($this->container->getParameter('uploads_documents_dir').'/'.$filename);
        } catch (FileNotFoundException $e) {
            throw $this->createNotFoundException('File does not exist.');
        }

        if ('zip' === $file->getExtension()) {
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

    public function tradeFile($filename, $originalName = null)
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

        if ('application/pdf' === $mimeType) {
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
