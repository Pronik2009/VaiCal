<?php

namespace App\Service;

use DateInterval;
use DateMalformedStringException;
use DateTime;
use DateTimeZone;
use RuntimeException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validation;

class ValidatorService
{
    public const string NEW_CITY_ASSERT = 'newCity';
    public const string CHECK_DEVICE_ASSERT = 'deviceCheck';
    public const string NEW_DEVICE_ASSERT = 'deviceRegister';
    public const string UPDATE_DEVICE_ASSERT = 'deviceUpdate';
    public const string CHECK_LANGUAGE_ASSERT = 'languageCheck';
    private array $newCityAssert;
    private array $tokenAssert;
    private array $deviceUuid;
    private array $deviceRegister;
    private array $deviceUpdateVerification;
    private array $deviceUpdate;
    private array $languageShortName;

    public function __construct()
    {
        // TODO: refactor this init when PHP 8.1 upgrade
        $this->tokenAssert = ['token' => [
            new Assert\NotBlank(),
            new Assert\Type('string'),
            new Assert\Length([
                'min' => 32,
                'max' => 32,
            ]),
            new Assert\Callback([__CLASS__, "validateSecurityHash"]),
        ]];
        $this->newCityAssert = [
            'name' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Length([
                    'min' => 2,
                    'max' => 40,
                    'minMessage' => "Name must be at least {{ limit }} characters long",
                    'maxMessage' => "Name cannot be longer than {{ limit }} characters",
                ]),
            ],
            'lat' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Length([
                    'min' => 7,
                    'max' => 18,
                    'minMessage' => "Latitude must be at least {{ limit }} characters long",
                    'maxMessage' => "Latitude cannot be longer than {{ limit }} characters",
                ]),
                new Assert\Regex('/^(\-?\d+(\.\d+)?)+$/', 'Coords can contain only numbers, "." and "-"'),
            ],
            'lon' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Length([
                    'min' => 7,
                    'max' => 18,
                    'minMessage' => "Longitude must be at least {{ limit }} characters long",
                    'maxMessage' => "Longitude cannot be longer than {{ limit }} characters",
                ]),
                new Assert\Regex('/^(\-?\d+(\.\d+)?)+$/', 'Coords can contain only numbers, "." and "-"'),
            ]
        ];
        $this->deviceUuid = [
            'uuid' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                 new Assert\Length([
                     'min' => 16,
                     'max' => 36,
                     'minMessage' => "Uuid must be at least {{ limit }} characters long",
                     'maxMessage' => "Uuid cannot be longer than {{ limit }} characters",
                 ]),
                new Assert\Regex(
                    '/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$|[0-9a-f]{16}+$/',
                    'Uuid can contain only hexadecimal symbols, and "-" in format "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"'
                ),
            ]
        ];
        $this->deviceRegister = [
            'model' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Length([
                    'min' => 4,
                    'max' => 250,
                    'minMessage' => "Uuid must be at least {{ limit }} characters long",
                    'maxMessage' => "Uuid cannot be longer than {{ limit }} characters",
                ]),
            ],
            'platform' => [
                new Assert\NotBlank(),
                new Assert\Choice([
                    'Android',
                    'iOS',
                ]),
            ],
            'version' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Regex('/[0-9\.]+$/', 'Version can contain only digits or "."'),
            ],
            'manufacturer' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Length([
                    'min' => 2,
                    'max' => 250,
                    'minMessage' => "Uuid must be at least {{ limit }} characters long",
                    'maxMessage' => "Uuid cannot be longer than {{ limit }} characters",
                ]),
            ],
            'serial' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
            ],
        ];
        $this->deviceUpdate = [
            'firebaseToken' => [
                new Assert\NotBlank(),
                //new Assert\Length([
                //     'min' => 163,
                //     'max' => 163,
                //     'minMessage' => "Uuid must be at least {{ limit }} characters long",
                //     'maxMessage' => "Uuid cannot be longer than {{ limit }} characters",
                // ]),
                new Assert\Type('string'),
                //new Assert\Regex('/[a-zA-Z0-9\_\:\-]+$/', 'Version can contain only alphanumeric or "_"'),
            ],
            'city' => [
                new Assert\NotBlank(),
                new Assert\Type('int'),
            ],
        ];
        $this->deviceUpdateVerification = [
            'notification' => [
                new Assert\Type('Boolean'),
            ],
            'notifyDay' => [
                new Assert\NotBlank(),
                new Assert\Type('int'),
                new Assert\Choice([0,1,2]),
            ],
            'notifyTime' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Length([
                    'min' => 5,
                    'max' => 5,
                    'minMessage' => "String must be at least {{ limit }} characters long",
                    'maxMessage' => "String cannot be longer than {{ limit }} characters",
                ]),
                new Assert\Regex('/(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]+$/', 'Time can contain only hours, minutes or ":", as HH:MM'),
            ],
        ];
        $this->languageShortName = [
            'shortName' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
                new Assert\Length([
                    'min' => 2,
                    'max' => 5,
                    'minMessage' => "Short name must be at least {{ limit }} characters long",
                    'maxMessage' => "Short name cannot be longer than {{ limit }} characters",
                ]),
                new Assert\Regex('/[a-z]{2}-[A-Z]{2}+$/', 'Short name must be in format "xx-XX"'),
        ]];
    }

    /**
     * @throws DateMalformedStringException
     */
    public static function validateSecurityHash($object, ExecutionContextInterface $context): void
    {
        $date = new DateTime('now', new DateTimeZone('UTC'));
        $key1 = $date->format('YmdH');
        $key2 = $date->format('i');
        if ((int)$key2[1] < 9) {
            $key2[1] = '0';
        } else {
            $date->add(new DateInterval('PT1M'));
            $key1 = $date->format('YmdH');
            $key2 = $date->format('i');
        }
        $hash = md5($_ENV['SECRET_PHRASE'] . $key1 . $key2);

//        print_r($hash . PHP_EOL);die();

        if ($object !== $hash) {
            $context->buildViolation('Token is invalid, get out!')
                ->addViolation();
        }
    }

    /**
     * @param array $data
     * @param string $assertType
     *
     * @return ConstraintViolationListInterface
     */
    public function validate(array $data, string $assertType): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();
        $asserts = match ($assertType) {
            self::NEW_CITY_ASSERT => array_merge($this->newCityAssert, $this->tokenAssert),
            self::CHECK_DEVICE_ASSERT => array_merge($this->deviceUuid, $this->tokenAssert),
            self::CHECK_LANGUAGE_ASSERT => array_merge($this->languageShortName),
            self::NEW_DEVICE_ASSERT => array_merge($this->deviceRegister, $this->deviceUuid, $this->deviceUpdate, $this->tokenAssert),
            self::UPDATE_DEVICE_ASSERT => array_merge($this->deviceUuid, $this->deviceUpdate, $this->deviceUpdateVerification, $this->tokenAssert),
            default => throw new RuntimeException('Assert type not recognized'),
        };
        $constraints = new Assert\Collection($asserts);

        return $validator->validate($data, $constraints);
    }
}
