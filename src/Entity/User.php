<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: false, unique: true)]
    #[Assert\NotBlank(message: "L'adresse email est obligatoire.", groups: ['default'])]
    #[Assert\Length(
        min: 6,
        max: 255,
        minMessage: "Votre adresse email doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Votre adresse email ne peut pas contenir plus de {{ limit }} caractères.",
        groups: ['default']
    )]
    #[Assert\Email(
        message: "Veuillez entrer une adresse email valide.",
        groups: ['default']
    )]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: false, unique: true)]
    #[Assert\NotBlank(groups: ['default'])]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: "Votre nom d'utilisateur doit contenir au moins {{limit}} caractères.",
        maxMessage: "Votre nom d'utilisateur ne peut pas contenir plus de {{limit}} caractères.",
        groups: ['default']
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9_-]+$/",
        message: "Votre nom d'utilisateur ne doit contenir que des lettres, des chiffres, des tirets (-) et des underscores (_)."
    )]
    private ?string $username = null;
    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: Types::JSON)]
    #[Assert\NotBlank(message: "L'utilisateur doit avoir au moins un rôle.", groups: ['default'])]
    #[Assert\All([
        new Assert\Choice(
            choices: ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN'],
            message: "Choisissez un rôle valide.",
            groups: ['default']
        )
    ])]
    private array $roles = ['ROLE_USER'];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: Types::STRING, length: 255,nullable: false)]
    private ?string $password = null;

    #[Assert\NotBlank(groups: ['password'])]
    #[Assert\Length(
        min: 8,
        minMessage: "Le mot de passe doit contenir au moins {{limit}} caractères.",
        groups: ['password']
    )]
    #[Assert\Regex(
        pattern: "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/",
        message: "Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre, et un caractère spécial.",
        groups: ['password']
    )]
    private ?string $plainPassword = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: false)]
    private ?DateTime $updatedAt = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $isVerified = false;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTime();
    }

    // Callbacks called before and after an entity is updated
    #[ORM\PreUpdate]
    public function updateUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTime();
    }

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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Add 'ROLE_USER' only if it's not already set
        if (!in_array('ROLE_USER', $roles, true)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        // Filtering roles to keep only valid roles if one day we use dynamic data source, for example script for migration or external API (if we have an admin panel)
        $validRoles = array_filter($roles, function($role) {
            return in_array($role, ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);
        });

        $this->roles = $validRoles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
      return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): static
    {
      $this->plainPassword = $plainPassword;

      return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}
