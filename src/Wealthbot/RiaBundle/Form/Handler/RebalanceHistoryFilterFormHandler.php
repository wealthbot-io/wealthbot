<?php

namespace Wealthbot\RiaBundle\Form\Handler;

use Symfony\Component\HttpFoundation\Session\Session;
use Wealthbot\AdminBundle\Form\Handler\AbstractFormHandler;

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
