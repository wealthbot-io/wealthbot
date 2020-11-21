<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 10.02.14
 * Time: 16:07.
 */

namespace App\Form\Type;

use App\Form\Type\ClientDocumentFormType;

class PdfDocumentFormType extends ClientDocumentFormType
{
    protected $allowedMimeTypes = [
        '.pdf' => 'application/pdf', //.pdf
    ];
}
