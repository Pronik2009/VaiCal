<?php

namespace App\Entity;

use App\Repository\NewCityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: NewCityRepository::class)]
#[UniqueEntity('name')]
class NewCity
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 170, unique: true)]
    private string $Name;

    #[ORM\Column(type: 'string', length: 170)]
    private string $Latitude;

    #[ORM\Column(type: 'string', length: 170)]
    private string $Longitude;

    #[ORM\Column(type: 'string', length: 170)]
    private string $UserAgent;

    #[ORM\Column(type: 'string', length: 170)]
    private string $IP;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }

    public function getLatitude(): string
    {
        return $this->Latitude;
    }

    public function setLatitude(string $Latitude): self
    {
        $this->Latitude = $Latitude;

        return $this;
    }

    public function getLongitude(): string
    {
        return $this->Longitude;
    }

    public function setLongitude(string $Longitude): self
    {
        $this->Longitude = $Longitude;

        return $this;
    }

    public function getUserAgent(): string
    {
        return $this->UserAgent;
    }

    public function setUserAgent(string $UserAgent): self
    {
        $this->UserAgent = $UserAgent;

        return $this;
    }

    public function getIP(): string
    {
        return $this->IP;
    }

    public function setIP(string $IP): self
    {
        $this->IP = $IP;

        return $this;
    }
}