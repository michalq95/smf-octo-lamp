<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Symfony\Component\HttpFoundation\Request;

class TokenAuthenticator extends JWTAuthenticator
{

    public function doAuthenticate(Request $request) /*: Passport */
    {

        $passport = parent::doAuthenticate($request);
        $payload = $passport->getAttributes()['payload'];
        $user = parent::loadUser($payload, $payload['username']);
        if ($user->getPasswordChangeDate() && $payload['iat'] < $user->getPasswordChangeDate()) {
            throw new ExpiredTokenException();
        }
        return $passport;
    }
}