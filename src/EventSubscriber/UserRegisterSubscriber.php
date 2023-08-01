<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\User;
use App\Security\ActivationTokenGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;


class UserRegisterSubscriber implements EventSubscriberInterface
{

    private $hasher;
    private $activationTokenGenerator;
    private $mailer;

    public function __construct(UserPasswordHasherInterface $hasher, ActivationTokenGenerator $activationTokenGenerator, MailerInterface $mailer)
    {
        $this->hasher = $hasher;
        $this->activationTokenGenerator = $activationTokenGenerator;
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['userRegistered', EventPriorities::PRE_WRITE]
        ];
    }


    public function userRegistered(ViewEvent $event)
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$user instanceof User || Request::METHOD_POST !== $method) {
            return;
        }

        $user->setPassword(
            $this->hasher->hashPassword($user, $user->getPassword())
        );
        $activationToken = $this->activationTokenGenerator->generateToken();
        $user->setActivationToken($activationToken);

        $email = (new Email());
        $email->from('mailtrap@example.com')->to($user->getEmail())
            ->subject("Confirm Job Board registration")
            ->text('Your activation token is ' . $activationToken);

        $this->mailer->send($email);
    }
}