<?php

namespace App\Form\Handler;

use Symfony\Component\HttpFoundation\Session\Session;
use App\Form\Handler\AbstractFormHandler;

class RebalanceHistoryFilterFormHandler extends AbstractFormHandler
{
    protected function success()
    {
        $session = $this->getOption('session');

        if (!($session instanceof Session)) {
            throw new \InvalidArgumentException(sprintf('Option user must be instance of %s', get_class(new Session())));
        }

        $data = $this->form->getData();

        $session->set('rebalance_history_filter', $data);
    }
}
