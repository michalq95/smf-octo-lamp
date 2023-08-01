<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Application;
use App\Entity\Offer;
use App\Repository\OfferRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;

class ApplicationSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['setProperCompany', EventPriorities::PRE_WRITE]
        ];
    }

    public function setProperCompany(ViewEvent $event)
    {
        $entity = $event->getControllerResult();
        $request = $event->getRequest();
        if (!$entity instanceof Application || Request::METHOD_POST !== $request->getMethod()) {
            return;
        }

        $offer = $entity->getOffer();
        $entity->setCompany($offer->getCompany());
    }
}
