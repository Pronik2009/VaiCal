<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\String\Slugger\AsciiSlugger;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass=CityRepository::class)
 *
 * @ApiResource(
 *     collectionOperations={"get","post"={
 *          "method"="POST",
 *          "path"="/cities/new",
 *          "controller"="NewCityController::class",
 *          "openapi_context"={
 *              "summary"="Propose new city to database",
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
 * @ApiFilter(SearchFilter::class, properties={"slug": "exact", "name": "exact"})
 */
class City
{
    /**
     * @Groups({"read"})
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255)
     */
    private string $slug;

    /**
     * @ORM\OneToMany(targetEntity=Year::class, mappedBy="city", orphanRemoval=true)
     */
    private $years;

    /**
     * @Groups({"read"})
     * @ORM\Column(type="smallint", nullable=true)
     * @Assert\GreaterThanOrEqual(-12)
     * @Assert\LessThanOrEqual(12)
     */
    private ?int $zone;

    /**
     * @ORM\OneToOne(targetEntity=Device::class, mappedBy="city", cascade={"persist", "remove"})
     */
    private $device;

    public function __construct()
    {
        $this->years = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        $slugger = new AsciiSlugger();
        $this->setSlug($slugger->slug($name));

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection|Year[]
     */
    public function getYears(): Collection
    {
        return $this->years;
    }

    public function addYear(Year $year): self
    {
        if (!$this->years->contains($year)) {
            $this->years[] = $year;
            $year->setCity($this);
        }

        return $this;
    }

    public function removeYear(Year $year): self
    {
        if ($this->years->removeElement($year)) {
            // set the owning side to null (unless already changed)
            if ($year->getCity() === $this) {
                $year->setCity(null);
            }
        }

        return $this;
    }

    public function getZone(): ?int
    {
        return $this->zone;
    }

    public function setZone(?int $zone): self
    {
        $this->zone = $zone;

        return $this;
    }

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function setDevice(?Device $device): self
    {
        // unset the owning side of the relation if necessary
        if ($device === null && $this->device !== null) {
            $this->device->setCity(null);
        }

        // set the owning side of the relation if necessary
        if ($device !== null && $device->getCity() !== $this) {
            $device->setCity($this);
        }

        $this->device = $device;

        return $this;
    }
}
