<?php

namespace App\Entity;

use App\Repository\VisitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisitRepository::class)]
class Visit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 45)]
    private ?string $ip = null;

    #[ORM\Column(length: 255)]
    private ?string $url = null;

    #[ORM\Column(length: 45)]
    private ?string $method = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $visitedAt = null;

    #[ORM\Column]
    private ?int $visitedBy = 0;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $userAgent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): static
    {
        $this->ip = $ip;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(string $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function getVisitedAt(): ?\DateTimeImmutable
    {
        return $this->visitedAt;
    }

    public function setVisitedAt(\DateTimeImmutable $visitedAt): static
    {
        $this->visitedAt = $visitedAt;

        return $this;
    }

    public function getVisitedBy(): ?int
    {
        return $this->visitedBy;
    }

    public function setVisitedBy(int $visitedBy): static
    {
        $this->visitedBy = $visitedBy;

        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;

        return $this;
    }
}
