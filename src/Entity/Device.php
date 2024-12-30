<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DeviceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
#[UniqueEntity('uuid')]
/**
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
 *                  "city"=99999999,
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
 *     itemOperations={"update"={
 *          "method"="PATCH",
 *          "path"="/devices/update/{id}",
 *          "controller"="DeviceController::class",
 *          "openapi_context"={
 *              "summary"="Update device in database",
 *              "description"="# Anonymous queries will be rejected.
 *      Accept queries only from front APP.
 *      Update at least firebaseToken or City parameters in JSON, and security token.
 *      Require correct uuid and security token",
 *              "requestBody"={"content"={"application/merge-patch+json"={"schema"={},"example"={
 *                  "firebaseToken"="abcdefghijklmnopqrstuvwxzy0123456789",
 *                  "city"=99999999,
 *                  "notification"=true,
 *                  "notifyDay"=1,
 *                  "notifyTime"="07:00",
 *                  "uuid"="1234567890abcdef",
 *                  "token"="someHashHereABCDEFG1234567890blablabla",
 *              }}}}
 *          }
 *     }},
 *     order={"name"="ASC"},
 *     normalizationContext={"groups"={"read"}},
 *     paginationEnabled=false
 * )
 */
class Device
{
    use TimestampableEntity;

    public const NOTIFICATION_ENABLE = true;
    public const NOTIFICATION_BEFORE_DAY = 1;
    public const NOTIFICATION_TIME = '07:00';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 170)]
    #[Assert\NotBlank]
    private string $model;

    #[ORM\Column(type: 'string', length: 170)]
    #[Assert\Choice(['Android', 'iOS'])]
    private string $platform;

    #[ORM\Column(type: 'string', length: 170, unique: true)]
    #[Assert\Regex('/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$|[0-9a-f]{16}/')]
    private string $uuid;

    #[ORM\Column(type: 'string', length: 170)]
    #[Assert\Regex('/[\d\.]/')]
    private string $version;

    #[ORM\Column(type: 'string', length: 170)]
    #[Assert\NotBlank]
    private string $manufacturer;

    #[ORM\Column(type: 'string', length: 170)]
    #[Assert\NotBlank]
    private string $serial;

    #[ORM\ManyToOne(targetEntity: City::class, inversedBy: 'devices')]
    #[ORM\JoinColumn(nullable: false)]
    private ?City $city;

    #[ORM\Column(type: 'string', length: 170)]
    private string $UserAgent;

    #[ORM\Column(type: 'string', length: 170)]
    private string $IP;

    #[ORM\Column(type: 'string', length: 170)]
    private string $firebaseToken;

    #[ORM\Column(type: 'boolean')]
    private bool $notification = self::NOTIFICATION_ENABLE;

    #[ORM\Column(type: 'smallint')]
    #[Assert\Choice([0, 1, 2])]
    private int $notifyDay = self::NOTIFICATION_BEFORE_DAY;

    #[ORM\Column(type: 'string', length: 5)]
    #[Assert\Length(5)]
    #[Assert\Regex('/(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]/')]
    private string $notifyTime = self::NOTIFICATION_TIME;

    #[ORM\ManyToOne(targetEntity: Language::class, inversedBy: 'devices')]
    private ?Language $language = null;


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

    /**
     * @return string
     */
    public function getUserAgent(): string
    {
        return $this->UserAgent;
    }

    /**
     * @param string $UserAgent
     */
    public function setUserAgent(string $UserAgent): void
    {
        $this->UserAgent = $UserAgent;
    }

    /**
     * @return string
     */
    public function getIP(): string
    {
        return $this->IP;
    }

    /**
     * @param string $IP
     */
    public function setIP(string $IP): void
    {
        $this->IP = $IP;
    }

    /**
     * @return string
     */
    public function getFirebaseToken(): string
    {
        return $this->firebaseToken;
    }

    /**
     * @param string $firebaseToken
     */
    public function setFirebaseToken(string $firebaseToken): void
    {
        $this->firebaseToken = $firebaseToken;
    }

    public function getNotification(): bool
    {
        return $this->notification;
    }

    public function setNotification(bool $notification): self
    {
        $this->notification = $notification;

        return $this;
    }

    public function getNotifyDay(): int
    {
        return $this->notifyDay;
    }

    public function setNotifyDay(int $notifyDay): self
    {
        $this->notifyDay = $notifyDay;

        return $this;
    }

    public function getNotifyTime(): string
    {
        return $this->notifyTime;
    }

    public function setNotifyTime(string $notifyTime): self
    {
        $this->notifyTime = $notifyTime;

        return $this;
    }

    public function isNotification(): ?bool
    {
        return $this->notification;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): static
    {
        $this->language = $language;

        return $this;
    }
}
