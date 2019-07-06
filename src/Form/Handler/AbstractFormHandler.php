<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 06.03.13
 * Time: 19:34
 * To change this template use File | Settings | File Templates.
 */

namespace App\Form\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFormHandler
{
    protected $form;
    protected $request;
    protected $em;
    protected $options;

    abstract protected function success();

    public function __construct(Form $form, Request $request, EntityManager $em, array $options = [])
    {
        $this->form = $form;
        $this->request = $request;
        $this->em = $em;
        $this->options = $options;
    }

    public function process()
    {
        $this->form->handleRequest($this->request);

        if ($this->form->isValid()) {
            $this->success();

            return true;
        }

        $this->error();

        return false;
    }

    protected function getOption($name, $defaultValue = null)
    {
        if ($this->hasOption($name)) {
            return $this->options[$name];
        }

        return $defaultValue;
    }

    protected function hasOption($name)
    {
        return isset($this->options[$name]);
    }

    protected function error()
    {
    }
}
