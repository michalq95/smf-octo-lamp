<?php

namespace App\Entity;

use ApiPlatform\Action\PlaceholderAction;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\ResetPasswordAction;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity('name')]
#[ApiResource(
    operations: [
        new Get(security: "is_granted('IS_AUTHENTICATED_FULLY')"), new Post(),
        new Put(
            name: "put-reset-password",
            uriTemplate: "/users/{id}/reset",
            controller: ResetPasswordAction::class,
            denormalizationContext: ['groups' => 'put-reset-password'],
        )
    ],
    normalizationContext: ['groups' => ['user.read']],
    denormalizationContext: ['groups' => ['user.write']],
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user.read', 'user:admin:read', 'user:owner:read'])]
    private ?int $id = null;

    #[Groups(['user.write', 'user:admin:read', 'user:owner:read'])]
    #[Assert\Email]
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['user:admin:read', 'user:owner:read'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(groups: ["user.write"])]
    #[Groups(['user.write'])]
    #[Assert\Regex(
        pattern: "/^.{6,}$/",
        message: "Password needs at least 6 characters",
        groups: ["user.write"]
    )]
    #[Assert\Length(min: 5, max: 255)]
    private ?string $password = null;

    #[ORM\Column(nullable: true, type: 'integer')]
    private $passwordChangeDate;


    #[Assert\NotBlank(groups: ["user.write"])]
    #[Assert\Expression(
        "this.getPassword()===this.getPasswordConfirm()",
        message: "Passwords do not match",
        groups: ["user.write"]
    )]
    #[Groups(['user.write'])]
    private ?string $passwordConfirm = null;


    #[ORM\OneToOne(mappedBy: 'owner', cascade: ['persist', 'remove'])]
    #[Groups(['user.read', 'user:admin:read', 'user:owner:read'])]
    private ?Company $company = null;


    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Application::class)]
    #[Groups(['user.read', 'user:admin:read', 'user:owner:read'])]
    private Collection $applications;


    #[ORM\Column(length: 255)]
    #[Groups(['user.read', 'user.write', 'user:admin:read', 'user:owner:read'])]
    #[Assert\Length(min: 5, max: 255, groups: ["user.write"])]
    private ?string $name = null;

    #[Assert\Length(min: 5, max: 255, groups: ['put-reset-password'])]
    #[Groups(['put-reset-password'])]
    private $newPassword;

    #[Assert\Length(min: 5, max: 255, groups: ['put-reset-password'])]
    #[Groups(['put-reset-password'])]
    #[Assert\Expression(
        "this.getNewPassword()===this.getNewPasswordConfirm()",
        message: "Passwords do not match",
        groups: ['put-reset-password']
    )]
    private $newpasswordConfirm;

    #[Assert\Length(min: 5, max: 255)]
    #[Groups(['put-reset-password'])]
    #[UserPassword(groups: ['put-reset-password'])]
    private $oldPassword;

    #[ORM\Column(type: "boolean")]
    private $activated = false;

    #[ORM\Column(type: "string", length: 40, nullable: true)]
    private ?string $activationToken = null;

    public function getId(): ?int
    {
        return $this->id;
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



    public function __construct()
    {
        $this->applications = new ArrayCollection();
        $this->activated = false;
        $this->activationToken = null;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }


    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    /**
     * Get the value of company
     */
    public function getCompany(): ?Company
    {
        return $this->company;
    }

    /**
     * Set the value of company
     */
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
            $application->setOwner($this);
        }

        return $this;
    }

    public function removeApplication(Application $application): static
    {
        if ($this->applications->removeElement($application)) {
            // set the owning side to null (unless already changed)
            if ($application->getAuthor() === $this) {
                $application->setOwner(null);
            }
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }


    public function getPasswordConfirm(): ?string
    {
        return $this->passwordConfirm;
    }


    public function setPasswordConfirm(?string $passwordConfirm): self
    {
        $this->passwordConfirm = $passwordConfirm;

        return $this;
    }


    public function getOldPassword()
    {
        return $this->oldPassword;
    }


    public function setOldPassword($oldPassword): self
    {
        $this->oldPassword = $oldPassword;

        return $this;
    }


    public function getNewpasswordConfirm()
    {
        return $this->newpasswordConfirm;
    }

    public function setNewpasswordConfirm($newpasswordConfirm): self
    {
        $this->newpasswordConfirm = $newpasswordConfirm;

        return $this;
    }

    public function getNewPassword()
    {
        return $this->newPassword;
    }

    public function setNewPassword($newPassword): self
    {
        $this->newPassword = $newPassword;

        return $this;
    }


    public function getPasswordChangeDate()
    {
        return $this->passwordChangeDate;
    }


    public function setPasswordChangeDate($passwordChangeDate): self
    {
        $this->passwordChangeDate = $passwordChangeDate;

        return $this;
    }


    public function getActivated()
    {
        return $this->activated;
    }


    public function setActivated($activated): self
    {
        $this->activated = $activated;

        return $this;
    }


    public function getActivationToken(): ?string
    {
        return $this->activationToken;
    }


    public function setActivationToken(?string $activationToken): self
    {
        $this->activationToken = $activationToken;

        return $this;
    }

    public function __toString(): string
    {
        return $this->email;
    }
}