<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 10.02.14
 * Time: 16:07.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Wealthbot\UserBundle\Form\Type\ClientDocumentFormType;

class PdfDocumentFormType extends ClientDocumentFormType
{
    protected $allowedMimeTypes = [
        '.pdf' => 'application/pdf', //.pdf
    ];

    public function __construct()
    {
        parent::__construct(false);
    }
}
