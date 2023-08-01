<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use App\Entity\Company;
use App\Entity\Image;
use App\Entity\Offer;
use App\Entity\Tags;
use App\Entity\User;

class DashboardController extends AbstractDashboardController
{

    public function __construct(
        private ChartBuilderInterface $chartBuilder,
    ) {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // return parent::index();

        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        // Option 1. Make your dashboard redirect to the same page for all users
        // return $this->redirect($adminUrlGenerator->setController(CompanyCrudController::class)->generateUrl());
        return $this->render('admin/index.html.twig');
        // // Option 2. Make your dashboard redirect to different pages depending on the user
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        // return $this->redirect($adminUrlGenerator->setController(MailCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Project');
    }

    public function configureMenuItems(): iterable
    {
        // yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),
            // MenuItem::section('Company'),
            MenuItem::linkToCrud('Company', 'fa fa-tags', Company::class),
            // MenuItem::section('Offer'),
            MenuItem::linkToCrud('Offer', 'fa fa-tags', Offer::class),
            MenuItem::linkToCrud('User', 'fa fa-tags', User::class),
            MenuItem::linkToCrud('Tags', 'fa fa-tags', Tags::class),
            MenuItem::linkToCrud('Image', 'fa fa-tags', Image::class),
            // MenuItem::linkToCrud('Blog Posts', 'fa fa-file-text', BlogPost::class),
        ];
    }
}