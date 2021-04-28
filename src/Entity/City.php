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


/**
 * @ORM\Entity(repositoryClass=CityRepository::class)
 *
 * @ApiResource(
 *     collectionOperations={"get"},
 *     itemOperations={"get"},
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
    private $id;

    /**
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Groups({"read"})
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity=Year::class, mappedBy="city", orphanRemoval=true)
     */
    private $years;

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
}
