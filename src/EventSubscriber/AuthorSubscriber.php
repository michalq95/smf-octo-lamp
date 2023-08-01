<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Application;
use App\Entity\Company;
use App\Entity\User;
use App\Wrapper\ViewEventWrapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthorSubscriber implements EventSubscriberInterface
{
    private $tokenStorage;
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['getAuthenticatedUser', EventPriorities::PRE_WRITE]
        ];
    }

    public function getAuthenticatedUser(ViewEventWrapper $event)
    {

        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        if ((!$entity instanceof Company && !$entity instanceof Application) || Request::METHOD_POST !== $method) {
            return;
        }
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        $entity->setOwner($user);
    }
}
