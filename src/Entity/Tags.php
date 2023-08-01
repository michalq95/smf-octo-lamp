<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\TagsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TagsRepository::class)]
#[ApiFilter(
    SearchFilter::class,
    properties: ['accepted' => 'exact']
)]
#[ApiResource(
    operations: [
        new Post(denormalizationContext: ['groups' => ['tags.post']]),
        new Put(denormalizationContext: ['groups' => ['tags.put']]),
        new GetCollection(security: "true"),
        new Delete()
    ],
    security: "is_granted('ROLE_MOD')"
)]
class Tags
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["tags.post", 'offer.read'])]
    private ?string $name = null;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(["tags.post", 'tags.put'])]
    private ?bool $accepted = null;

    #[ORM\ManyToMany(targetEntity: Offer::class, inversedBy: 'tags')]
    private Collection $offer;

    public function __construct()
    {
        $this->offer = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function isAccepted(): ?bool
    {
        return $this->accepted;
    }

    public function setAccepted(bool $accepted): static
    {
        $this->accepted = $accepted;

        return $this;
    }

    /**
     * @return Collection<int, Offer>
     */
    public function getOffer(): Collection
    {
        return $this->offer;
    }

    public function addOffer(Offer $offer): static
    {
        if (!$this->offer->contains($offer)) {
            $this->offer->add($offer);
        }

        return $this;
    }

    public function removeOffer(Offer $offer): static
    {
        $this->offer->removeElement($offer);

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}