<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 14.08.13
 * Time: 16:14
 * To change this template use File | Settings | File Templates.
 */

namespace App\Controller\Signature;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function checkApplicationSigned($account_id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $accountSignatureManager = $this->get('wealthbot_docusign.document_signature.manager');

        $repository = $em->getRepository('App\Entity\ClientAccount');
        $client = $this->getUser();

        $account = $repository->findOneBy(['id' => $account_id, 'client_id' => $client->getId()]);
        if (!$account) {
            return $this->json([
                'status' => 'error',
                'message' => 'Account does not exist or does not belong to you.',
            ]);
        }

        if (!$accountSignatureManager->isApplicationSigned($account->getId())) {
            $result = [
                'status' => 'error',
                'message' => 'You have not signed applications. Please sign all applications.',
            ];
        } else {
            $result = ['status' => 'success'];
        }

        return $this->json($result);
    }

    public function fillPdf(Request $request)
    {
        $dir = 'uploads/signature_pdfs';
        $filename = 'IRA_Account_Application';

        $firmName = 'Wealthbot';
        $primaryContact = 'RiaFirst RiaLast';
        $accountType = 'rollover_ira';

        $fdf = '%FDF-1.2
1 0 obj<</FDF<< /Fields[
<</T(firm_name)/V('.$firmName.')>>
<</T(primary_contact)/V('.$primaryContact.')>>
<</T(account_type)/V('.$accountType.')>>
] >> >>
endobj
trailer
<</Root 1 0 R>>
%%EOF';

        file_put_contents($dir.'/'.$filename.'_tmp.fdf', $fdf);

        $command = 'cd uploads/signature_pdfs ';
        $command .= '&& pdftk '.$filename.'.pdf fill_form '.$filename.'_tmp.fdf output '.$filename.'_filled.pdf';

        exec($command);
        unlink($dir.'/'.$filename.'_tmp.fdf');

        return 'Complete.';
    }
}
