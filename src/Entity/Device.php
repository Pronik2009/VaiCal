<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DeviceRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=DeviceRepository::class)
 * @ApiResource(
 *     collectionOperations={"register"={
 *          "method"="POST",
 *          "path"="/devices/register",
 *          "controller"="DeviceController::class",
 *          "openapi_context"={
 *              "summary"="Register new device in database",
 *              "description"="# Anonymous queries will be rejected.
 *      Accept queries only from front APP.
 *      Require all parameters in JSON, and security token",
 *              "requestBody"={"content"={"application/json"={"schema"={},"example"={
 *                  "model"="ZTE Blade A7 2019",
 *                  "platform"="Android",
 *                  "uuid"="1234567890abcdef",
 *                  "version"="1.2.3",
 *                  "manufacturer"="ZTE",
 *                  "serial"="unknown",
 *                  "firebaseToken"="abcdefghijklmnopqrstuvwxzy0123456789",
 *                  "city"="/api/cities/99999999",
 *                  "token"="someHashHereABCDEFG1234567890blablabla",
 *              }}}}
 *          }
 *     },
 *     "check"={
 *          "method"="POST",
 *          "path"="/devices/check",
 *          "controller"="DeviceController::class",
 *          "openapi_context"={
 *              "summary"="Check device is exist in database",
 *              "description"="# Anonymous queries will be rejected.
 *      Accept queries only from front APP.
 *      Require device uuid and security token",
 *              "requestBody"={"content"={"application/json"={"schema"={},"example"={
 *                  "uuid"="1234567890abcdef",
 *                  "token"="someHashHereABCDEFG1234567890blablabla",
 *              }}}}
 *          }
 *     }},
 *     itemOperations={},
 *     order={"name"="ASC"},
 *     normalizationContext={"groups"={"read"}},
 *     paginationEnabled=false
 * )
 */
class Device
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private string $model;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Choice({"Android", "iOS"})
     */
    private string $platform;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Unique()
     * @Assert\Regex("/[0-9a-f]{16}/")
     * @Assert\Length(16)
     */
    private string $uuid;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex("/[\d\.]/")
     */
    private string $version;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private string $manufacturer;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private string $serial;

    /**
     * @ORM\OneToOne(targetEntity=City::class, inversedBy="device", cascade={"persist", "remove"})
     */
    private ?City $city;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function setPlatform(string $platform): self
    {
        $this->platform = $platform;

        return $this;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function setUuid($uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getManufacturer(): string
    {
        return $this->manufacturer;
    }

    public function setManufacturer(string $manufacturer): self
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getSerial(): string
    {
        return $this->serial;
    }

    public function setSerial(string $serial): self
    {
        $this->serial = $serial;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): self
    {
        $this->city = $city;

        return $this;
    }
}
