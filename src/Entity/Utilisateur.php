<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;



#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[UniqueEntity(
    fields: ['email'], 
    message: 'Cet email est déjà utilisé.',
    groups: ['registration', 'admin']  // Ajoutez les groupes si nécessaire
)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Le nom est obligatoire.", groups: ['registration', 'admin'])]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÿ '-]+$/",
        message: "Le nom ne peut contenir que des lettres, espaces, apostrophes ou tirets.",
        groups: ['registration', 'admin']
    )]
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[Assert\NotBlank(message: "Le prénom est obligatoire.", groups: ['registration', 'admin'])]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÿ '-]+$/",
        message: "Le prénom ne peut contenir que des lettres, espaces, apostrophes ou tirets.",
        groups: ['registration', 'admin']
    )]
    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[Assert\NotBlank(message: "L'email est obligatoire.", groups: ['registration', 'admin'])]
    #[Assert\Email(message: "Veuillez saisir un email valide.", groups: ['registration', 'admin'])]
    #[ORM\Column(length: 255)]
    private ?string $email = null;


    #[Assert\NotBlank(message: "Le mot de passe est obligatoire.", groups: ['admin'])]
    #[Assert\Length(
        min: 6,
        minMessage: "Le mot de passe doit faire au moins {{ limit }} caractères",
        max: 4096,
        groups: ['admin']
    )]
    #[Assert\Regex(
        pattern: "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{6,}$/",
        message: "Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre.",
        groups: ['admin']
    )]
    #[ORM\Column(length: 255)]
    private ?string $motDePasse = null;

    // Contrainte seulement pour l'admin, pas pour l'inscription
    #[Assert\NotBlank(message: "Le rôle est obligatoire.", groups: ['admin'])]
    #[ORM\Column(length: 255)]
    private ?string $role = null;

    // Contrainte seulement pour l'admin, pas pour l'inscription
    #[Assert\NotBlank(message: "L'etat du compte est obligatoire.", groups: ['admin'])]
    #[ORM\Column(length: 255)]
    private ?string $statutCompte = null;   

    #[ORM\Column(type: 'integer')]
    private ?int $loginFrequency = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastLogin = null;

    #[ORM\Column(type: 'integer')]
    private ?int $failedLoginAttempts = 0;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $emailVerified = false;

    #[ORM\Column(type: 'string', length: 64, nullable: true, unique: true)]
    private ?string $emailVerificationToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $emailVerificationTokenExpiresAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $emailVerifiedAt = null;

    /**
     * @var Collection<int, Cours>
     */
    #[ORM\OneToMany(targetEntity: Cours::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $cours;

    /**
     * @var Collection<int, Quiz>
     */
    #[ORM\OneToMany(targetEntity: Quiz::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $quizzes;

    /**
     * @var Collection<int, Event>
     */
    #[ORM\OneToMany(targetEntity: Event::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $events;

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\OneToMany(targetEntity: Produit::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $produits;

    /**
     * @var Collection<int, StressSurvey>
     */
    #[ORM\OneToMany(targetEntity: StressSurvey::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $stressSurveys;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'user')]
    private Collection $notifications;

    /**
     * @var Collection<int, PasswordResetToken>
     */
    #[ORM\OneToMany(targetEntity: PasswordResetToken::class, mappedBy: 'user')]
    private Collection $passwordResetTokens;

    public function __construct()
    {
        $this->cours = new ArrayCollection();
        $this->quizzes = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->produits = new ArrayCollection();
        $this->stressSurveys = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->passwordResetTokens = new ArrayCollection(); 
    }

    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(?string $nom): static { $this->nom = $nom; return $this; }
    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(?string $prenom): static { $this->prenom = $prenom; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(?string $email): static { $this->email = $email; return $this; }
    public function getMotDePasse(): ?string { return $this->motDePasse; }
    public function setMotDePasse(?string $motDePasse): static { $this->motDePasse = $motDePasse; return $this; }
    public function getRole(): ?string { return $this->role; }
    public function setRole(?string $role): static { $this->role = $role; return $this; }
    public function getStatutCompte(): ?string { return $this->statutCompte; }
    public function setStatutCompte(?string $statutCompte): static { $this->statutCompte = $statutCompte; return $this; }

    public function getCours(): Collection { return $this->cours; }
    public function addCour(Cours $cour): static {
        if (!$this->cours->contains($cour)) {
            $this->cours->add($cour);
            $cour->setUser($this); // <- corrigé
        }
        return $this;
    }
    public function removeCour(Cours $cour): static {
        if ($this->cours->removeElement($cour)) {
            if ($cour->getUser() === $this) {
                $cour->setUser(null);
            }
        }
        return $this;
    }

    public function getQuizzes(): Collection { return $this->quizzes; }
    public function addQuiz(Quiz $quiz): static {
        if (!$this->quizzes->contains($quiz)) {
            $this->quizzes->add($quiz);
            $quiz->setUser($this); // <- corrigé
        }
        return $this;
    }
    public function removeQuiz(Quiz $quiz): static {
        if ($this->quizzes->removeElement($quiz)) {
            if ($quiz->getUser() === $this) {
                $quiz->setUser(null);
            }
        }
        return $this;
    }

    public function getEvents(): Collection { return $this->events; }
    public function addEvent(Event $event): static {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setUser($this); // <- corrigé
        }
        return $this;
    }
    public function removeEvent(Event $event): static {
        if ($this->events->removeElement($event)) {
            if ($event->getUser() === $this) {
                $event->setUser(null);
            }
        }
        return $this;
    }

    public function getProduits(): Collection { return $this->produits; }
    public function addProduit(Produit $produit): static {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->setUser($this); // <- corrigé
        }
        return $this;
    }
    public function removeProduit(Produit $produit): static {
        if ($this->produits->removeElement($produit)) {
            if ($produit->getUser() === $this) {
                $produit->setUser(null);
            }
        }
        return $this;
    }

    public function getStressSurveys(): Collection { return $this->stressSurveys; }
    public function addStressSurvey(StressSurvey $survey): static {
        if (!$this->stressSurveys->contains($survey)) {
            $this->stressSurveys->add($survey);
            $survey->setUser($this); // <- corrigé
        }
        return $this;
    }
    public function removeStressSurvey(StressSurvey $survey): static {
        if ($this->stressSurveys->removeElement($survey)) {
            if ($survey->getUser() === $this) {
                $survey->setUser(null);
            }
        }
        return $this;
    }

    public function getUserIdentifier(): string
{
    return (string) $this->email;
}

public function getRoles(): array
{
    return array_unique([
        $this->role,
        'ROLE_USER'
    ]);
}


public function eraseCredentials()
{
    // Clear temporary sensitive data if needed
}

public function getPassword(): string
{
    return $this->motDePasse;
}

public function getLoginFrequency(): ?int
{
    return $this->loginFrequency;
}

public function setLoginFrequency(int $loginFrequency): static
{
    $this->loginFrequency = $loginFrequency;
    return $this;
}

public function getLastLogin(): ?\DateTimeInterface
{
    return $this->lastLogin;
}

public function setLastLogin(?\DateTimeInterface $lastLogin): static
{
    $this->lastLogin = $lastLogin;
    return $this;
}

public function getFailedLoginAttempts(): ?int
{
    return $this->failedLoginAttempts;
}

public function setFailedLoginAttempts(int $failedLoginAttempts): static
{
    $this->failedLoginAttempts = $failedLoginAttempts;
    return $this;
}

/**
 * @return Collection<int, Notification>
 */
public function getNotifications(): Collection
{
    return $this->notifications;
}

public function addNotification(Notification $notification): static
{
    if (!$this->notifications->contains($notification)) {
        $this->notifications->add($notification);
        $notification->setUser($this);
    }

    return $this;
}

public function removeNotification(Notification $notification): static
{
    if ($this->notifications->removeElement($notification)) {
        // set the owning side to null (unless already changed)
        if ($notification->getUser() === $this) {
            $notification->setUser(null);
        }
    }

    return $this;
}

public function getCreatedAt(): ?\DateTimeInterface
{
    return $this->createdAt;
}

public function setCreatedAt(?\DateTimeInterface $createdAt): static
{
    $this->createdAt = $createdAt;
    return $this;
}

/**
 * @return Collection<int, PasswordResetToken>
 */
public function getPasswordResetTokens(): Collection
{
    return $this->passwordResetTokens;
}

public function addPasswordResetToken(PasswordResetToken $passwordResetToken): static
{
    if (!$this->passwordResetTokens->contains($passwordResetToken)) {
        $this->passwordResetTokens->add($passwordResetToken);
        $passwordResetToken->setUser($this);
    }

    return $this;
}

public function removePasswordResetToken(PasswordResetToken $passwordResetToken): static
{
    if ($this->passwordResetTokens->removeElement($passwordResetToken)) {
        // set the owning side to null (unless already changed)
        if ($passwordResetToken->getUser() === $this) {
            $passwordResetToken->setUser(null);
        }
    }

    return $this;
}

// Getters and setters
public function isEmailVerified(): bool { return $this->emailVerified; }
public function setEmailVerified(bool $emailVerified): self 
{ $this->emailVerified = $emailVerified; return $this; }

public function getEmailVerificationToken(): ?string { return $this->emailVerificationToken; }
public function setEmailVerificationToken(?string $token): self 
{ $this->emailVerificationToken = $token; return $this; }

public function getEmailVerificationTokenExpiresAt(): ?\DateTimeInterface 
{ return $this->emailVerificationTokenExpiresAt; }
public function setEmailVerificationTokenExpiresAt(?\DateTimeInterface $expiresAt): self 
{ $this->emailVerificationTokenExpiresAt = $expiresAt; return $this; }

public function getEmailVerifiedAt(): ?\DateTimeInterface { return $this->emailVerifiedAt; }
public function setEmailVerifiedAt(?\DateTimeInterface $verifiedAt): self 
{ $this->emailVerifiedAt = $verifiedAt; return $this; }

// Helper method to check if token is valid
public function isVerificationTokenValid(string $token): bool
{
    return $this->emailVerificationToken === $token 
        && $this->emailVerificationTokenExpiresAt > new \DateTime();
}

    public function markEmailAsVerified(): self
    {
        $this->emailVerified = true;
        $this->emailVerifiedAt = new \DateTime();
        $this->emailVerificationToken = null;
        $this->emailVerificationTokenExpiresAt = null;
        
        return $this;
    }

}

