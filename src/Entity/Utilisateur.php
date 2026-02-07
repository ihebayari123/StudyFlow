<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $motDePasse = null;

    #[ORM\Column(length: 255)]
    private ?string $role = null;

    #[ORM\Column(length: 255)]
    private ?string $statutCompte = null;

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

    public function __construct()
    {
        $this->cours = new ArrayCollection();
        $this->quizzes = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->produits = new ArrayCollection();
        $this->stressSurveys = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): static { $this->nom = $nom; return $this; }
    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): static { $this->prenom = $prenom; return $this; }
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $email): static { $this->email = $email; return $this; }
    public function getMotDePasse(): ?string { return $this->motDePasse; }
    public function setMotDePasse(string $motDePasse): static { $this->motDePasse = $motDePasse; return $this; }
    public function getRole(): ?string { return $this->role; }
    public function setRole(string $role): static { $this->role = $role; return $this; }
    public function getStatutCompte(): ?string { return $this->statutCompte; }
    public function setStatutCompte(string $statutCompte): static { $this->statutCompte = $statutCompte; return $this; }

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

}
