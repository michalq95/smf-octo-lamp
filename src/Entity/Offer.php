<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\MyOfferController;
use App\Repository\OfferRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Cocur\Slugify\Slugify;
use Symfony\Component\Validator\Constraints as Assert;



#[ORM\Entity(repositoryClass: OfferRepository::class)]
#[ApiFilter(
    SearchFilter::class,
    properties: ['title' => 'partial', 'content' => 'partial', 'company.title' => 'partial', 'tags.name' => 'exact']
)]
#[ApiFilter(
    DateFilter::class,
    properties: ['published']
)]
#[ApiFilter(
    RangeFilter::class,
    properties: ['BracketLow', 'BracketHigh']
)]
#[ApiResource(
    order: ['published' => 'DESC'],
    paginationEnabled: false,
    operations: [
        new Post(
            security: "is_granted('ROLE_USER') and user.getCompany()",
            denormalizationContext: ['groups' => ['offer.post']],
            validationContext: ['groups' => ['Default', 'offer.post']]
        ),
        new Get(),
        new Put(
            security: "(is_granted('ROLE_USER') and object.getCompany()==user.getCompany()) or is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['offer.put']],
            validationContext: ['groups' => ['Default', 'offer.put']]
        ),
        new GetCollection(),
        new GetCollection(
            uriTemplate: '/myoffers',
            security: 'is_granted("ROLE_USER")',
            controller: MyOfferController::class
        ),
        new Delete(security: "(is_granted('ROLE_USER') and object.getCompany()==user.getCompany()) or is_granted('ROLE_ADMIN')")
    ],
    normalizationContext: ['groups' => ['offer.read']],
)]
#[ApiResource(
    uriTemplate: '/companies/{id}/offers',
    uriVariables: [
        'id' => new Link(fromClass: Company::class, fromProperty: 'offers')
    ],
    normalizationContext: ['groups' => ['offer.read']],
    // denormalizationContext: ['groups' => ['offer.write']],
    operations: [new GetCollection]
)]
// #[ApiFilter(
//     SearchFilter::class,
//     properties: ['status' => 'exact']
// )]
#[ORM\HasLifecycleCallbacks]

class Offer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['offer.post', 'offer.read'])]
    #[Assert\NotNull(groups: ['offer.post'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['offer.post', 'offer.read', 'offer.put'])]
    #[Assert\NotNull(groups: ['offer.post'])]
    private ?string $content = null;

    #[ORM\ManyToOne(targetEntity: "Company", inversedBy: "offers")]
    #[Groups(['offer.read'])]
    private ?Company $company = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['offer.read'])]
    private ?\DateTimeInterface $published = null;

    #[ORM\OneToMany(mappedBy: 'offer', targetEntity: Application::class)]
    private Collection $applications;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['offer.read'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expire_date = null;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Groups(['offer.post', 'offer.read', 'offer.put'])]
    #[Assert\Expression(
        "this.getBracketLow() !== null || this.getBracketHigh() === null",
        message: "Include lower end of the bracket",
        groups: ['offer.post', 'offer.put']
    )]
    private ?int $BracketLow = null;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Assert\Expression(
        "this.getBracketHigh() !== null || this.getBracketLow() === null",
        message: "Include higher end of the bracket! Higher end should be higher than lower end!",
        groups: ['offer.post', 'offer.put']
    )]
    #[Groups(['offer.post', 'offer.read', 'offer.put'])]
    private ?int $BracketHigh = null;

    #[ORM\Column(length: 3, nullable: true)]
    #[Assert\Expression(
        "this.getCurrency() !== null || this.getBracketLow() === null",
        message: "Include currency",
        groups: ['offer.post', 'offer.put']
    )]
    #[Groups(['offer.post', 'offer.read', 'offer.put'])]
    private ?string $currency = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['offer.post', 'offer.read', 'offer.put'])]
    private ?string $icon = null;

    #[Groups(['offer.post', 'offer.put'])]
    private ?string $commaSeparatedtags = null;

    #[Groups('offer.read')]
    #[ORM\ManyToMany(targetEntity: Tags::class, mappedBy: 'offer')]
    private Collection $tags;

    #[Groups(['offer.read'])]
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $status = null;

    public function __construct()
    {
        $this->applications = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function initValues(): void
    {
        $slugify = new Slugify();
        $this->slug = (string)$this->id . $slugify->slugify($this->title);
        $this->published = new \DateTime();
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getPublished(): ?\DateTimeInterface
    {
        return $this->published;
    }

    public function setPublished(\DateTimeInterface $published): static
    {
        $this->published = $published;

        return $this;
    }


    public function getCompany(): ?Company
    {
        return $this->company;
    }


    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection<int, Application>
     */
    public function getApplications(): Collection
    {
        return $this->applications;
    }

    public function addApplication(Application $application): static
    {
        if (!$this->applications->contains($application)) {
            $this->applications->add($application);
            $application->setOffer($this);
        }

        return $this;
    }

    public function removeApplication(Application $application): static
    {
        if ($this->applications->removeElement($application)) {
            // set the owning side to null (unless already changed)
            if ($application->getOffer() === $this) {
                $application->setOffer(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getExpireDate(): ?\DateTimeInterface
    {
        return $this->expire_date;
    }

    public function setExpireDate(?\DateTimeInterface $expire_date): static
    {
        $this->expire_date = $expire_date;

        return $this;
    }

    public function getBracketLow(): ?int
    {
        return $this->BracketLow;
    }

    public function setBracketLow(?int $bracket_low): static
    {
        $this->BracketLow = $bracket_low;

        return $this;
    }

    public function getBracketHigh(): ?int
    {
        return $this->BracketHigh;
    }

    public function setBracketHigh(?int $bracket_high): static
    {
        $this->BracketHigh = $bracket_high;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return Collection<int, Tags>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tags $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addOffer($this);
        }

        return $this;
    }

    public function removeTag(Tags $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeOffer($this);
        }

        return $this;
    }


    public function getCommaSeparatedtags(): ?string
    {
        return $this->commaSeparatedtags;
    }


    public function setCommaSeparatedtags(?string $commaSeparatedtags): self
    {
        $this->commaSeparatedtags = $commaSeparatedtags;

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
    public function __toString(): string
    {
        return $this->title;
    }
}