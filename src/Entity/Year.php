<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\YearRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ORM\Entity(repositoryClass=YearRepository::class)
 *
 * @ApiResource(
 *     collectionOperations={"get"={}},
 *     itemOperations={"get"={}},
 *     paginationEnabled=false
 * )
 *
 * @ApiFilter(SearchFilter::class, properties={"city": "exact", "value": "exact"})
 */
class Year
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="integer")
     */
    private int $value;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private ?array $jan = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private ?array $feb = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private ?array $mar = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private ?array $apr = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private ?array $may = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private ?array $jun = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private ?array $jul = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private ?array $aug = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private ?array $sem = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private ?array $oct = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private ?array $nov = [];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private ?array $dem = [];

    /**
     * @ORM\ManyToOne(targetEntity=City::class, inversedBy="years")
     * @ORM\JoinColumn(nullable=false)
     */
    private City $city;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getJan(): ?array
    {
        return $this->jan;
    }

    public function getJanStr(): ?array
    {
        return $this->formatArray($this->jan);
    }

    public function setJan(?array $jan): self
    {
        $this->jan = $jan;

        return $this;
    }

    public function getFeb(): ?array
    {
        return $this->feb;
    }

    public function getFebStr(): ?array
    {
        return $this->formatArray($this->feb);
    }

    public function setFeb(?array $feb): self
    {
        $this->feb = $feb;

        return $this;
    }

    public function getMar(): ?array
    {
        return $this->mar;
    }

    public function getMarStr(): ?array
    {
        return $this->formatArray($this->mar);
    }

    public function setMar(?array $mar): self
    {
        $this->mar = $mar;

        return $this;
    }

    public function getApr(): ?array
    {
        return $this->apr;
    }

    public function getAprStr(): ?array
    {
        return $this->formatArray($this->apr);
    }

    public function setApr(?array $apr): self
    {
        $this->apr = $apr;

        return $this;
    }

    public function getMay(): ?array
    {
        return $this->may;
    }

    public function getMayStr(): ?array
    {
        return $this->formatArray($this->may);
    }

    public function setMay(?array $may): self
    {
        $this->may = $may;

        return $this;
    }

    public function getJun(): ?array
    {
        return $this->jun;
    }

    public function getJunStr(): ?array
    {
        return $this->formatArray($this->jun);
    }

    public function setJun(?array $jun): self
    {
        $this->jun = $jun;

        return $this;
    }

    public function getJul(): ?array
    {
        return $this->jul;
    }

    public function getJulStr(): ?array
    {
        return $this->formatArray($this->jul);
    }

    public function setJul(?array $jul): self
    {
        $this->jul = $jul;

        return $this;
    }

    public function getAug(): ?array
    {
        return $this->aug;
    }

    public function getAugStr(): ?array
    {
        return $this->formatArray($this->aug);
    }

    public function setAug(?array $aug): self
    {
        $this->aug = $aug;

        return $this;
    }

    public function getSem(): ?array
    {
        return $this->sem;
    }

    public function getSemStr(): ?array
    {
        return $this->formatArray($this->sem);
    }

    public function setSem(?array $sem): self
    {
        $this->sem = $sem;

        return $this;
    }

    public function getOct(): ?array
    {
        return $this->oct;
    }

    public function getOctStr(): ?array
    {
        return $this->formatArray($this->oct);
    }

    public function setOct(?array $oct): self
    {
        $this->oct = $oct;

        return $this;
    }

    public function getNov(): ?array
    {
        return $this->nov;
    }

    public function getNovStr(): ?array
    {
        return $this->formatArray($this->nov);
    }

    public function setNov(?array $nov): self
    {
        $this->nov = $nov;

        return $this;
    }

    public function getDem(): ?array
    {
        return $this->dem;
    }

    public function getDemStr(): ?array
    {
        return $this->formatArray($this->dem);
    }

    public function setDem(?array $dem): self
    {
        $this->dem = $dem;

        return $this;
    }

    public function getCity(): City
    {
        return $this->city;
    }

    public function setCity(City $city): self
    {
        $this->city = $city;

        return $this;
    }

    private function formatArray(?array $array): array
    {
        $result = [];
        foreach ($array as $item) {
            if (is_array($item)) {
                $result[] = implode('-', $item);
            } else {
                $result[] = $item;
            }
        }

        return $result;
    }
}
