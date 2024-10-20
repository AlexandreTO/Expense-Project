<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class Expense
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(["default", "create", "update"])]
    #[Assert\NotBlank(groups: ["default", "create"])]
    private ?string $category;

    #[ORM\Column(type: 'decimal', scale: 3)]
    #[Groups(["default", "create", "update"])]
    #[Assert\NotBlank(groups: ["default", "create"])]
    #[Assert\Positive]
    private ?float $amount;

    #[ORM\Column(type: 'date')]
    #[Groups(["default", "create", "update"])]
    #[Assert\NotBlank(groups: ["default", "create"])]
    private ?\DateTimeInterface $date;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }
}
