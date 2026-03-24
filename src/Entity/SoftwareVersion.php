<?php

namespace App\Entity;

use App\Repository\SoftwareVersionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SoftwareVersionRepository::class)]
#[ORM\Table(name: 'software_version')]
#[ORM\Index(columns: ['system_version_alt'], name: 'idx_system_version_alt')]
#[ORM\HasLifecycleCallbacks]
class SoftwareVersion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $name = '';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $systemVersion = '';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private string $systemVersionAlt = '';

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $link = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $stLink = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $gdLink = null;

    #[ORM\Column]
    private bool $isLatest = false;

    #[ORM\Column]
    private bool $isLci = false;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $lciHwType = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getSystemVersion(): string
    {
        return $this->systemVersion;
    }

    public function setSystemVersion(string $systemVersion): static
    {
        $this->systemVersion = $systemVersion;
        return $this;
    }

    public function getSystemVersionAlt(): string
    {
        return $this->systemVersionAlt;
    }

    public function setSystemVersionAlt(string $systemVersionAlt): static
    {
        $this->systemVersionAlt = $systemVersionAlt;
        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;
        return $this;
    }

    public function getStLink(): ?string
    {
        return $this->stLink;
    }

    public function setStLink(?string $stLink): static
    {
        $this->stLink = $stLink;
        return $this;
    }

    public function getGdLink(): ?string
    {
        return $this->gdLink;
    }

    public function setGdLink(?string $gdLink): static
    {
        $this->gdLink = $gdLink;
        return $this;
    }

    public function isLatest(): bool
    {
        return $this->isLatest;
    }

    public function setIsLatest(bool $isLatest): static
    {
        $this->isLatest = $isLatest;
        return $this;
    }

    public function isLci(): bool
    {
        return $this->isLci;
    }

    public function setIsLci(bool $isLci): static
    {
        $this->isLci = $isLci;
        return $this;
    }

    public function getLciHwType(): ?string
    {
        return $this->lciHwType;
    }

    public function setLciHwType(?string $lciHwType): static
    {
        $this->lciHwType = $lciHwType;
        return $this;
    }

    /**
     * Auto-compute isLci and lciHwType from name before persist/update.
     */
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function computeLciFields(): void
    {
        $this->isLci = str_starts_with($this->name, 'LCI');

        if ($this->isLci) {
            if (stripos($this->name, 'CIC') !== false) {
                $this->lciHwType = 'CIC';
            } elseif (stripos($this->name, 'NBT') !== false) {
                $this->lciHwType = 'NBT';
            } elseif (stripos($this->name, 'EVO') !== false) {
                $this->lciHwType = 'EVO';
            } else {
                $this->lciHwType = null;
            }
        } else {
            $this->lciHwType = null;
        }
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->name, $this->systemVersion);
    }
}
