<?php

namespace App\Entity;

use App\Repository\WellBeingScoreRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WellBeingScoreRepository::class)]
class WellBeingScore
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Recommandation médicale : obligatoire, 10–255 caractères (back)
     */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La recommandation médicale est obligatoire.')]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: 'La recommandation doit contenir au moins {{ limit }} caractères.',
        maxMessage: 'La recommandation ne peut pas dépasser {{ limit }} caractères.'
    )]
    private ?string $recommendation = null;

    /**
     * Plan d'action : obligatoire, 8–255 caractères (back)
     */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le plan d'action est obligatoire.")]
    #[Assert\Length(
        min: 8,
        max: 255,
        minMessage: "Le plan d'action doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le plan d'action ne peut pas dépasser {{ limit }} caractères."
    )]
    private ?string $actionPlan = null;

    /**
     * Observations cliniques : obligatoire, 6–500 caractères (back)
     */
    #[ORM\Column(length: 500)]
    #[Assert\NotBlank(message: 'Les observations cliniques sont obligatoires.')]
    #[Assert\Length(
        min: 6,
        max: 500,
        minMessage: 'Les observations doivent contenir au moins {{ limit }} caractères.',
        maxMessage: 'Les observations ne peuvent pas dépasser {{ limit }} caractères.'
    )]
    private ?string $comment = null;

    /**
     * Score : entier obligatoire entre 0 et 100 (back)
     */
    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank(message: 'Le score est obligatoire.')]
    #[Assert\Type(type: 'integer', message: 'Le score doit être un nombre entier.')]
    #[Assert\Range(
        min: 0,
        max: 100,
        notInRangeMessage: 'Le score doit être compris entre {{ min }} et {{ max }}.'
    )]
    private ?int $score = null;

    /**
     * survey_id UNIQUE — relation 1-à-1 (contrainte back via UNIQUE INDEX)
     */
    #[ORM\OneToOne(inversedBy: 'wellBeingScore', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false, unique: true)]
    #[Assert\NotNull(message: 'Le sondage associé est obligatoire.')]
    private ?StressSurvey $survey = null;

    // ── Getters & Setters ──────────────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getRecommendation(): ?string { return $this->recommendation; }
    public function setRecommendation(string $recommendation): static
    {
        $this->recommendation = $recommendation;
        return $this;
    }

    public function getActionPlan(): ?string { return $this->actionPlan; }
    public function setActionPlan(string $actionPlan): static
    {
        $this->actionPlan = $actionPlan;
        return $this;
    }

    public function getComment(): ?string { return $this->comment; }
    public function setComment(string $comment): static
    {
        $this->comment = $comment;
        return $this;
    }

    public function getScore(): ?int { return $this->score; }
    public function setScore(int $score): static
    {
        $this->score = $score;
        return $this;
    }

    public function getSurvey(): ?StressSurvey { return $this->survey; }
    public function setSurvey(StressSurvey $survey): static
    {
        $this->survey = $survey;
        return $this;
    }
}
