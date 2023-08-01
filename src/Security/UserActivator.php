<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserActivator implements UserCheckerInterface
{

    public function checkPreAuth(UserInterface $user)
    {

        if (!$user instanceof User) {
            return;
        }

        if (!$user->getActivated()) {
            throw new DisabledException();
            // throw new AccessDeniedHttpException("User not activated");

        }
    }

    public function checkPostAuth(UserInterface $user)
    {
    }
}
