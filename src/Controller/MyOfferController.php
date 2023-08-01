<?php

namespace App\Controller;

use App\Repository\OfferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class MyOfferController extends AbstractController
{

    private $security;
    private $offerRepository;
    public function __construct(
        Security $security,
        OfferRepository $offerRepository,
    ) {
        $this->offerRepository = $offerRepository;
        $this->security = $security;
    }

    public function __invoke()
    {
        return $this->offerRepository->findBy(['company' => $this->security->getUser()->getCompany()]);
    }
}