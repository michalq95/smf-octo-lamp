<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Exception\InvalidActivationToken;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class UserActivationSubscriber implements EventSubscriberInterface
{
    private $userRepository;
    private $em;
    private $logger;
    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $em,
        LoggerInterface  $logger
    ) {
        $this->userRepository = $userRepository;
        $this->em = $em;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['activateUser', EventPriorities::POST_VALIDATE]
        ];
    }

    public function activateUser(ViewEvent $event)
    {

        $request = $event->getRequest();
        if ('_api_/users/activate_post' !== $request->get('_route')) {
            return;
        }

        $activationToken = $event->getControllerResult();
        $user = $this->userRepository->findOneBy(['activationToken' => $activationToken->activationToken]);

        if (!$user) {
            $this->logger->debug("User by activation token not found");
            throw new InvalidActivationToken();
            // throw new NotFoundHttpException();
        }

        $user->setActivated(true);
        $user->setActivationToken(null);
        $this->em->flush();
        $this->logger->debug("User activated");
        $event->setResponse(new JsonResponse("activated user", 200));
    }
}
