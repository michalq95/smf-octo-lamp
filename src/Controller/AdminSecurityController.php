<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class AdminSecurityController extends AbstractController
{

    #[Route("/login", name: "security_login")]
    public function login()
    {
        return $this->render('login.html.twig');
    }

    #[Route("/logout", name: "security_logout")]
    public function logout()
    {
    }
}