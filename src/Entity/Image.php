<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\UploadImageAction;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\OpenApi\Model;
use Symfony\Component\HttpFoundation\File\File;



#[ORM\Entity]
#[Vich\Uploadable]
#[ApiResource(
    order: ['id' => 'DESC'],
    operations: [
        new Post(
            uriTemplate: "/images",
            deserialize: false,
            controller: UploadImageAction::class,
            defaults: ["_api_recieve" => false],
            openapi: new Model\Operation(
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary'
                                    ]
                                ]
                            ]
                        ]
                    ])
                )
            )

        ), new Get(), new GetCollection()
    ]
)]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[Vich\UploadableField(mapping: "images", fileNameProperty: "url")]
    // #[Assert\NotNull]
    private ?File $file = null;

    #[ORM\Column(nullable: true)]
    private ?string $url = null;

    #[ORM\ManyToMany(targetEntity: Company::class, mappedBy: 'images')]
    private Collection $companies;

    public function __construct()
    {
        $this->companies = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }





    public function getFile(): ?File
    {
        return $this->file;
    }


    public function setFile($file): self
    {
        $this->file = $file;

        return $this;
    }


    public function getUrl(): ?string
    {
        return '/images/' . $this->url;
    }


    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function __toString()
    {
        return $this->id . ':' . $this->url;
    }

    /**
     * @return Collection<int, Company>
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): static
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
            $company->addImage($this);
        }

        return $this;
    }

    public function removeCompany(Company $company): static
    {
        if ($this->companies->removeElement($company)) {
            $company->removeImage($this);
        }

        return $this;
    }
}