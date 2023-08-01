<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Cocur\Slugify\Slugify;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[ApiFilter(
    SearchFilter::class,
    properties: ['title' => 'partial', 'content' => 'partial', 'address' => 'partial']
)]
#[ApiFilter(
    DateFilter::class,
    properties: ['published']
)]
#[ApiFilter(
    OrderFilter::class,
    properties: ['published', 'bracketHigh', 'bracketLow']
)]
#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            security: "is_granted('ROLE_USER')",
            denormalizationContext: ['groups' => ['company.post']],
            validationContext: ['groups' => ['company.post']]
        ),
        new Get(),
        new Put(
            security: "(is_granted('ROLE_USER') and object.getOwner()==user) or is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['company.put']],
            validationContext: ['groups' => ['company.put']]
        ),
        new GetCollection(),
        new Delete(security: "(is_granted('ROLE_USER') and object.getOwner()==user) or is_granted('ROLE_ADMIN')")
    ],

    normalizationContext: ['groups' => ['company.read']],
)]
#[ORM\HasLifecycleCallbacks]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups: ['company.post'])]
    #[Groups(['company.post', 'company.read', 'offer.read'])]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['company.read'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['company.read'])]
    private ?\DateTimeInterface $published = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(groups: ['company.post'])]
    #[Groups(['company.post', 'company.put', 'company.read'])]
    private ?string $content = null;

    // #[ORM\Column(length: 255)]
    #[ORM\OneToOne(targetEntity: "User", inversedBy: "company", cascade: ["persist"])]
    #[Groups(['company.read'])]
    private ?User $owner = null;

    #[ORM\OneToMany(targetEntity: "Offer", mappedBy: "company", cascade: ["persist", "remove"])]
    #[Groups(['company.read'])]
    private iterable $offers;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Application::class)]
    private Collection $applications;

    #[ORM\Column(length: 255)]
    #[Groups(['company.post',  'company.read', 'offer.read'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['company.post', 'company.put', 'company.read', 'offer.read'])]
    private ?string $address = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['company.post', 'company.put', 'company.read', 'offer.read'])]
    private ?float $locX = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['company.post', 'company.put', 'company.read', 'offer.read'])]
    private ?float $locY = null;

    #[ORM\ManyToMany(targetEntity: Image::class, inversedBy: 'companies')]
    #[Groups(['company.post', 'company.put', 'company.read', 'offer.read'])]
    private Collection $images;

    // #[ORM\Column(length: 255, nullable: true)]
    // #[ORM\ManyToMany(mappedBy: 'image', targetEntity: Image::class)]
    // #[ORM\JoinTable()]
    // #[Groups(['company.post', 'company.put', 'company.read', 'offer.read'])]
    // private Collection $images;

    public function __construct()
    {
        $this->offers = new ArrayCollection();
        $this->applications = new ArrayCollection();
        // $this->images = new ArrayCollection();
    }

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

    public function getPublished(): ?\DateTimeInterface
    {
        return $this->published;
    }


    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getOffers(): iterable
    {
        return $this->offers;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }


    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    #[ORM\PrePersist]
    public function initValues(): void
    {
        $slugify = new Slugify();
        $this->slug = $slugify->slugify($this->title);
        $this->published = new \DateTime();
    }


    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Application $application): static
    {
        if (!$this->applications->contains($application)) {
            $this->applications->add($application);
            $application->setCompany($this);
        }

        return $this;
    }

    public function removeApplication(Application $application): static
    {
        if ($this->applications->removeElement($application)) {
            // set the owning side to null (unless already changed)
            if ($application->getCompany() === $this) {
                $application->setCompany(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getLocX(): ?float
    {
        return $this->locX;
    }

    public function setLocX(?float $loc_x): static
    {
        $this->locX = $loc_x;

        return $this;
    }

    public function getLocY(): ?float
    {
        return $this->locY;
    }

    public function setLocY(?float $loc_y): static
    {
        $this->locY = $loc_y;

        return $this;
    }





    /**
     * @return Collection<int, Image>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
        }

        return $this;
    }

    public function removeImage(Image $image): static
    {
        $this->images->removeElement($image);

        return $this;
    }

    public function __toString(): string
    {
        return $this->title;
    }
}