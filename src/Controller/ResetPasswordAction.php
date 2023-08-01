<?php

namespace App\Controller;

use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsController]
class ResetPasswordAction  extends AbstractController
{
    private $validator;
    private $userPasswordHasher;
    private $em;
    private $tokenManager;

    public function __construct(
        ValidatorInterface $validator,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $em,
        JWTTokenManagerInterface $tokenManager
    ) {
        $this->validator = $validator;
        $this->userPasswordHasher = $userPasswordHasher;
        $this->em = $em;
        $this->tokenManager = $tokenManager;
    }

    public function __invoke(User $data)
    {
        $this->validator->validate($data);
        $data->setPassword(
            $this->userPasswordHasher->hashPassword(
                $data,
                $data->getNewPassword()
            )
        );
        $data->setPasswordChangeDate(time());

        $this->em->flush();
        $token = $this->tokenManager->create($data);

        return new JsonResponse(['token' => $token]);
    }
}
