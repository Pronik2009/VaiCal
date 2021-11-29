<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DeviceRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=DeviceRepository::class)
 * @ApiResource(
 *     collectionOperations={"post","get"={
 *          "method"="POST",
 *          "path"="/devices/",
 *          "controller"="NewCityController::class",
 *          "openapi_context"={
 *              "summary"="Check device exist in database",
 *              "description"="# Anonymous queries will be rejected.
 *      Accept queries only from front APP.
 *      Require two parameters in JSON, as city slug and security token
 *      On success return {id} of new city request, can be used while connect to employers",
 *              "requestBody"={"content"={"application/json"={"schema"={},"example"={
 *                  "name"="Perímetro Urbano Santiago de Cali",
 *                  "lat"="48.46012365355584",
 *                  "lon"="-35.04221496410461",
 *                  "token"="someHashHereABCDEFG1234567890blablabla",
 *              }}}}
 *          }
 *     }},
 *     itemOperations={"get"},
 *     order={"name"="ASC"},
 *     normalizationContext={"groups"={"read"}},
 *     paginationEnabled=false
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={"uuid": "exact"})
 */
class Device
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     */
    private string $model;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\Choice({"Android", "iOS"})
     */
    private string $platform;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\Length(16)
     */
    private string $uuid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $version;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $manufacturer;

    /**
     * @ORM\Column(type="string", length=255)
     *
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
