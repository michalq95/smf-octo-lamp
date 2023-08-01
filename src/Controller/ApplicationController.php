<?php

namespace App\Controller;

use App\Repository\ApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[AsController]
class ApplicationController extends AbstractController
{

    private $security;
    private $applicationRepository;
    private $requestStack;
    public function __construct(
        RequestStack $requestStack,
        Security $security,
        ApplicationRepository $applicationRepository,
    ) {
        $this->applicationRepository = $applicationRepository;
        $this->security = $security;
        $this->requestStack = $requestStack;
    }

    public function __invoke()
    {
        $pathInfo = $this->requestStack->getCurrentRequest()->getPathInfo();
        if ('/api/companyapplications' === $pathInfo) {
            return $this->applicationRepository->findBy(['company' => $this->security->getUser()->getCompany()]);
        }
        if ('/api/myapplications' === $pathInfo) {
            return $this->applicationRepository->findBy(['author' => $this->security->getUser()]);
        }


        throw new BadRequestHttpException();
    }
}