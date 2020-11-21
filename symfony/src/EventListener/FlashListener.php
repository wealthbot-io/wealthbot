<?php
/**
 * Created by JetBrains PhpStorm.
 * User: maksim
 * Date: 20.05.13
 * Time: 19:43
 * To change this template use File | Settings | File Templates.
 */

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Event\RiaEvents;

class FlashListener implements EventSubscriberInterface
{
    public $messages = [];

    /**
     * @var Session
     */
    private $session;

    public static function getSubscribedEvents()
    {
        return [
            RiaEvents::RIA_FLASH_MESSAGE => 'addFlash',
        ];
    }

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function addFlash(Event $event)
    {
        $this->session->getFlashBag()->add($event->getType(), $event->getMessage());
    }
}
