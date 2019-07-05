<?php
/**
 * Created by PhpStorm.
 * User: amalyuhin
 * Date: 12.05.14
 * Time: 14:10.
 */

namespace App\Form\Validator;

use Symfony\Component\Form\FormInterface;

abstract class AbstractFormValidator
{
    protected $form;
    protected $data;

    public function __construct(FormInterface $form, $data)
    {
        $this->form = $form;
        $this->data = $data;
    }

    abstract public function validate();
}
