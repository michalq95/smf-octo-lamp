<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ApplicationRepository;
use App\Repository\OfferRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Controller\ApplicationController;


#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
#[ApiResource(
    order: ['published' => 'DESC'],
    operations: [
        new Post(security: "is_granted('ROLE_USER')", denormalizationContext: ['groups' => ['application.post']]),
        // new GetCollection(),
        new Put(security: "(is_granted('ROLE_USER') and object.getCompany()==user.getCompany()) or is_granted('ROLE_ADMIN')", denormalizationContext: ['groups' => ['application.put']]),
        new Get(security: "(is_granted('ROLE_USER') and object.getCompany()==user.getCompany()) or is_granted('ROLE_ADMIN')"),
        new GetCollection(
            uriTemplate: '/companyapplications',
            security: 'is_granted("ROLE_USER")',
            controller: ApplicationController::class
        ),
        new GetCollection(
            uriTemplate: '/myapplications',
            security: 'is_granted("ROLE_USER")',
            controller: ApplicationController::class
        )
    ],
    normalizationContext: ['groups' => ['application.read']],
)]
// #[ApiResource(
//     uriTemplate: '/companies/{id}/applications',
//     uriVariables: [
//         'id' => new Link(fromClass: Company::class, fromProperty: 'applications')
//     ],
//     normalizationContext: ['groups' => ['application.read']],
//     // denormalizationContext: ['groups' => ['offer.write']],
//     operations: [new GetCollection(security: "object.getCompany() === user.getCompany()")]
// )]
// #[ApiResource(
//     uriTemplate: '/users/{id}/applications',
//     uriVariables: [
//         'id' => new Link(fromClass: User::class, fromProperty: 'applications')
//     ],
//     normalizationContext: ['groups' => ['application.read']],
//     // denormalizationContext: ['groups' => ['offer.write']],
//     operations: [new GetCollection(security: "object.getAuthor() === user")]
// )]
#[ORM\HasLifecycleCallbacks]
class Application
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['application.post', 'application.read'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['application.post', 'application.read'])]
    private ?string $resume = null;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['application.read'])]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['application.post', 'application.read'])]
    private ?Offer $offer = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Groups(['application.read', 'application.put'])]
    private ?int $status = null;

    #[ORM\ManyToOne(inversedBy: 'applications')]
    #[Groups(['application.read'])]
    private ?Company $company = null;

    // #[ORM\PrePersist]
    // public function initValues(): void
    // {
    //     $offerRepository = new OfferRepository();
    //     $offer = $offerRepository->findOneBy(['@id' => $this->offer]);
    //     if (!$offer) {
    //         throw new \InvalidArgumentException('The offer does not exist.');
    //     }
    //     $this->company = $offer->getCompany();
    // }

    // private $offerRepository;
    // public function __construct(OfferRepository $offerRepository)
    // {
    //     $this->offerRepository = $offerRepository;
    // }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getResume(): ?string
    {
        return $this->resume;
    }

    public function setResume(string $resume): static
    {
        $this->resume = $resume;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setOwner(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getOffer(): ?Offer
    {
        return $this->offer;
    }

    public function setOffer(?Offer $offer): static
    {
        $this->offer = $offer;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }
}