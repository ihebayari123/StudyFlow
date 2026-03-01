<?php

namespace App\DTO;

class UserNaturalSearchDTO
{
    private ?string $role = null;
    private ?string $statutCompte = null;
    private ?bool $emailVerified = null;
    private ?\DateTimeInterface $createdAtFrom = null;
    private ?\DateTimeInterface $createdAtTo = null;
    private ?bool $neverLoggedIn = null;
    private ?int $minFailedAttempts = null;

    // Getters et setters...
    public function getRole(): ?string { return $this->role; }
    public function setRole(?string $role): self { $this->role = $role; return $this; }
    
    public function getStatutCompte(): ?string { return $this->statutCompte; }
    public function setStatutCompte(?string $statutCompte): self { $this->statutCompte = $statutCompte; return $this; }
    
    public function getEmailVerified(): ?bool { return $this->emailVerified; }
    public function setEmailVerified(?bool $emailVerified): self { $this->emailVerified = $emailVerified; return $this; }
    
    public function getCreatedAtFrom(): ?\DateTimeInterface { return $this->createdAtFrom; }
    public function setCreatedAtFrom(?\DateTimeInterface $createdAtFrom): self { $this->createdAtFrom = $createdAtFrom; return $this; }
    
    public function getCreatedAtTo(): ?\DateTimeInterface { return $this->createdAtTo; }
    public function setCreatedAtTo(?\DateTimeInterface $createdAtTo): self { $this->createdAtTo = $createdAtTo; return $this; }
    
    public function getNeverLoggedIn(): ?bool { return $this->neverLoggedIn; }
    public function setNeverLoggedIn(?bool $neverLoggedIn): self { $this->neverLoggedIn = $neverLoggedIn; return $this; }
    
    public function getMinFailedAttempts(): ?int { return $this->minFailedAttempts; }
    public function setMinFailedAttempts(?int $minFailedAttempts): self { $this->minFailedAttempts = $minFailedAttempts; return $this; }
}