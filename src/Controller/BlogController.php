<?php

namespace App\Controller;

use App\Entity\Company;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/blog')]
class BlogController extends AbstractController
{
    // private $doctrine;
    // private $serializer;

    // private function __construct(ManagerRegistry $doctrine, SerializerInterface $serializer)
    // {
    //     $this->doctrine = $doctrine;
    //     $this->serializer = $serializer;
    // }



    #[Route('/', name: 'blog_index')]
    public function index(Request $request, ManagerRegistry $doctrine, SerializerInterface $serializer): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $page = $request->get('page', 1);
        $repository = $doctrine->getRepository(Company::class);
        $items = $repository->findAll();
        $serializedItems = $serializer->serialize($items, 'json');

        return new JsonResponse([
            'page' => $page,
            'limit' => $limit,
            'data' => json_decode($serializedItems)
        ]);
    }

    #[Route("/{id}", name: 'blog_show', methods: ["GET"])]
    public function show($id, Company $company, ManagerRegistry $doctrine)
    {
        // return $this->json($doctrine->getRepository(Company::class)->find($id));
        return $this->json($company);
    }

    #[Route("/slug/{slug}", name: 'blog_show_slug')]
    public function showSlug($slug, ManagerRegistry $doctrine, Company $company)
    {
        // return $this->json($doctrine->getRepository(Company::class)->findBy(['slug' => $slug]));
        return $this->json($company);
    }

    #[Route(methods: ['POST'])]
    public function add(Request $request, ManagerRegistry $doctrine, SerializerInterface $serializer)
    {
        $em = $doctrine->getManager();
        $company = $serializer->deserialize($request->getContent(), Company::class, 'json');

        $em->persist($company);
        $em->flush();

        return $this->json($company);
    }


    #[Route("/{id}", methods: ['DELETE'])]
    public function delete(Company $company, ManagerRegistry $doctrine)
    {
        $em = $doctrine->getManager();
        $em->remove($company);
        $em->flush();

        return new JsonResponse(null, 204);
    }
}