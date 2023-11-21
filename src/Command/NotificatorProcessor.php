<?php

namespace App\Command;

use App\Entity\City;
use App\Entity\Device;
use App\Entity\Year;
use Doctrine\ORM\EntityManagerInterface;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Factory;

class NotificatorProcessor
{
    private const AFTER_TOMORROW = 'Послезавтра';
    private const TOMORROW = 'Завтра';
    private const TODAY = 'Сегодня';
    private const EVENT_1 = 'Нитьянанда Прабху';
    private const EVENT_2 = 'Гаура-Пурнима, Шри Чайтанья Махапрабху';
    private const EVENT_3 = 'Рама Навами, Рамачандра';
    private const EVENT_4 = 'Наришимха';
    private const EVENT_6 = 'Баларама';
    private const EVENT_7 = 'Джанмастами, Кришна';
    private const EVENT_8 = 'Бхактиведанта Свами Прабхупада';
    private const EVENT_9 = 'Радхастами, Шримати Радхарани';
    private const EVENT_A = 'Говардхана Пуджа';
    private const EVENT_B = 'Ратха-Ятра';
    private const EVENT_SP = 'Сиддхасварупананда Парамахамса Прабхупада';
    private const EVENT_CR = 'Рождество, Иисус Христос ';
    private const CHRISTMAS_KEY = 'CR';
    private const APPEREANCE = ' Явление';
    private const EKADASI = ' Экадаши';
    private EntityManagerInterface $database;

    public function __construct(EntityManagerInterface $database)
    {
        $this->database = $database;
    }

    private function factory(): Factory
    {
        return (new Factory)->withServiceAccount(dirname(__FILE__, 3) . '/config/VaiCal_credentials.json');
    }

    private function getNumberDayMonth(array $allMonth, int $numberMonth, int $year): int
    {
        $month_days = 0;

        if ($allMonth[$numberMonth] === 'January' ||
            $allMonth[$numberMonth] === 'March' ||
            $allMonth[$numberMonth] === 'May' ||
            $allMonth[$numberMonth] === 'July' ||
            $allMonth[$numberMonth] === 'August' ||
            $allMonth[$numberMonth] === 'October' ||
            $allMonth[$numberMonth] === 'December') {

            $month_days = 31;

        } elseif ($allMonth[$numberMonth] === 'April' ||
            $allMonth[$numberMonth] === 'June' ||
            $allMonth[$numberMonth] === 'September' ||
            $allMonth[$numberMonth] === 'November') {

            $month_days = 30;

        } elseif ($allMonth[$numberMonth] === 'February') {

            if (($year % 4) === 0) {
                $month_days = 29;
            } else {
                $month_days = 28;
            }

        }

        return $month_days;
    }

    private function getDeviceEvent(Year $events, string $fullNameMonth): array
    {
        $deviceEvent = [];

        if ($fullNameMonth === 'January') {
            $deviceEvent = current($events)->getJan();
        } elseif ($fullNameMonth === 'February') {
            $deviceEvent = current($events)->getFeb();
        } elseif ($fullNameMonth === 'March') {
            $deviceEvent = current($events)->getMar();
        } elseif ($fullNameMonth === 'April') {
            $deviceEvent = current($events)->getApr();
        } elseif ($fullNameMonth === 'May') {
            $deviceEvent = current($events)->getMay();
        } elseif ($fullNameMonth === 'June') {
            $deviceEvent = current($events)->getJun();
            $searchKey = array_key_exists(14, $deviceEvent);

            if ($searchKey) {
                $deviceEvent['14(2)'] = 'SP';
            } else {
                $deviceEvent[14] = 'SP';
            }

        } elseif ($fullNameMonth === 'July') {
            $deviceEvent = current($events)->getJul();
        } elseif ($fullNameMonth === 'August') {
            $deviceEvent = current($events)->getAug();
        } elseif ($fullNameMonth === 'September') {
            $deviceEvent = current($events)->getSem();
        } elseif ($fullNameMonth === 'October') {
            $deviceEvent = current($events)->getOct();
        } elseif ($fullNameMonth === 'November') {
            $deviceEvent = current($events)->getNov();
        } elseif ($fullNameMonth === 'December') {
            $deviceEvent = current($events)->getDem();

            $searchKey = array_key_exists(25, $deviceEvent);

            if ($searchKey) {
                $deviceEvent['25(2)'] = self::CHRISTMAS_KEY;
            } else {
                $deviceEvent[25] = self::CHRISTMAS_KEY;
            }

        }

        return $deviceEvent;
    }

    private function initEvent(string|array $event): string
    {
        if (is_array($event)) {
            $event = $event['ekadasi_name'];
        }

        return match ($event) {
            'Utpanna' => 'Утпанна' . self::EKADASI,
            'Saphala' => 'Са-пхала' . self::EKADASI,
            'Moksada' => 'Мокшада' . self::EKADASI,
            'Utthana' => 'Уттхана' . self::EKADASI,
            'Rama' => 'Рама' . self::EKADASI,
            'Pasankusa' => 'Пашанкуша' . self::EKADASI,
            'Parama' => 'Парама' . self::EKADASI,
            'Padmini' => 'Падмини' . self::EKADASI,
            'Indira' => 'Индира' . self::EKADASI,
            'Parsva' => 'Паршва' . self::EKADASI,
            'Annada' => 'Аннада (Аджа)' . self::EKADASI,
            'Pavitropana' => 'Павитра' . self::EKADASI,
            'Kamika' => 'Камика' . self::EKADASI,
            'Sayana' => 'Дева-шаяни (Падма)' . self::EKADASI,
            'Yogini' => 'Йогини' . self::EKADASI,
            'Pandava Nirjala' => 'Нирджала (Пандава, Бхима)' . self::EKADASI,
            'Apara' => 'Апара' . self::EKADASI,
            'Mohini' => 'Мохини' . self::EKADASI,
            'Varuthini' => 'Варутхини' . self::EKADASI,
            'Kamada' => 'Камада' . self::EKADASI,
            'Papamocani' => 'Папа-мочани' . self::EKADASI,
            'Amalaki vrata' => 'Амалаки' . self::EKADASI,
            'Vijaya' => 'Виджая' . self::EKADASI,
            'Bhaimi' => 'Джая (Бхаими)' . self::EKADASI,
            'Sat-tila' => 'Шат-тила' . self::EKADASI,
            'Putrada' => 'Путрада' . self::EKADASI,
            '1' => self::EVENT_1 . self::APPEREANCE,
            '2' => self::EVENT_2 . self::APPEREANCE,
            '3' => self::EVENT_3 . self::APPEREANCE,
            '4' => self::EVENT_4 . self::APPEREANCE,
            '6' => self::EVENT_6 . self::APPEREANCE,
            '7' => self::EVENT_7 . self::APPEREANCE,
            '8' => self::EVENT_8 . self::APPEREANCE,
            '9' => self::EVENT_9 . self::APPEREANCE,
            'A' => self::EVENT_A . self::APPEREANCE,
            'B' => self::EVENT_B . self::APPEREANCE,
            'SP' => self::EVENT_SP . self::APPEREANCE,
            self::CHRISTMAS_KEY => self::EVENT_CR . self::APPEREANCE,
        };
    }

    private function getObjectEvents(int $year, ?int $id): Year
    {
        return $this->database->getRepository(Year::class)
            ->createQueryBuilder('y')
            ->andWhere('y.value = ' . $year)
            ->andWhere('y.city = :city')
            ->setParameter('city', $id)
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws MessagingException
     * @throws FirebaseException
     */
    private function sendNotification(string $deviceToken, string $title, string $body, Device $device): void
    {
        $factory = $this->factory();
        $messaging = $factory->createMessaging();

//        $notification = Notification::fromArray([
//            'title' => $title,
//            'body' => $body,
//            'sound' => true,
//        ]);

        $notification = Notification::create($title, $body);

        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification($notification);

        $result = $messaging->validateRegistrationTokens($deviceToken);

        if (!empty($result['valid'])) {
            $messaging->send($message);
        }

// по идее нужно удалять этот девайс
//            dump(
//                $result['unknown'],
//                $result['valid'],
//                $device
//            );
    }

    /**
     * @throws MessagingException
     * @throws FirebaseException
     */
    final public function initNotification(): int
    {
        $count = 0;

        $devices = $this->database->getRepository(Device::class)->findAll();

        $listAllMonth = ['January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];

        /** @var Device $device */
        foreach ($devices as $device) {
            $currentDate = getdate();
            $currentYear = $currentDate['year'];
            $currentDayNumber = $currentDate['mday'];
            $currentNumberMonth = $currentDate['mon'] - 1;
            $currentHours = $currentDate['hours'];
            $currentMinutes = $currentDate['minutes'];
            $cityId = $device->getCity()?->getId();
            $notifyDay = $device->getNotifyDay();
            $notifyTime = $device->getNotifyTime();
            $firebaseToken = $device->getFirebaseToken();
            $statusNotification = $device->getNotification();
            $timeZone = '';

            if ($currentMinutes <= 6) {
                $currentMinutes = 0;
            } elseif ($currentMinutes > 30 && $currentMinutes <= 36) {
                $currentMinutes = 30;
            }

            if ($currentMinutes < 10) $currentMinutes = '0' . $currentMinutes;

            if ($statusNotification) {
                $city = $this->database->getRepository(City::class)
                    ->createQueryBuilder('y')
                    ->andWhere('y.id = ' . $cityId)
                    ->getQuery()
                    ->getResult();

                if (!empty($city)) {
                    $timeZone = $city[0]->getZone();
                }

                $timeDevice = $currentHours + $timeZone;

                if ($timeDevice < 0) {
                    $timeDevice = $timeDevice + 24;

                    if ($timeDevice < 10) $timeDevice = '0' . $timeDevice;

                    $timeDevice = $timeDevice . ':' . $currentMinutes;

                    if ($timeDevice === $notifyTime) {

                        if ($currentDayNumber === 1) { // если 1 число

                            if ($currentNumberMonth === 0) { // если январь, то заходим в прошлый год на 31 декабря
                                $currentYear = $currentYear - 1;
                                $currentNumberMonth = 11;
                                $numberDayMonth = $this->getNumberDayMonth($listAllMonth, $currentNumberMonth, $currentYear);
                                $currentDayNumber = $numberDayMonth;
                                $events = $this->getObjectEvents($currentYear, $cityId);
                                $deviceEvent = $this->getDeviceEvent($events, $listAllMonth[$currentNumberMonth]);
                            } else { // то заходим в прошлый месяц
                                $currentNumberMonth = $currentNumberMonth - 1;
                                $numberDayMonth = $this->getNumberDayMonth($listAllMonth, $currentNumberMonth, $currentYear);
                                $currentDayNumber = $numberDayMonth;
                                $events = $this->getObjectEvents($currentYear, $cityId);
                                $deviceEvent = $this->getDeviceEvent($events, $listAllMonth[$currentNumberMonth]);
                            }

                        } else { // то заходим во вчерашний день
                            $numberDayMonth = $this->getNumberDayMonth($listAllMonth, $currentNumberMonth, $currentYear);
                            $currentDayNumber = $currentDayNumber - 1;
                            $events = $this->getObjectEvents($currentYear, $cityId);
                            $deviceEvent = $this->getDeviceEvent($events, $listAllMonth[$currentNumberMonth]);
                        }

                    } else {
                        continue;
                    }

                } elseif ($timeDevice >= 24) {

                    if ($timeDevice === 24) {
                        $timeDevice = 0;
                    } else {
                        $timeDevice = $timeDevice - 24;
                    }

                    if ($timeDevice < 10) $timeDevice = '0' . $timeDevice;

                    $timeDevice = $timeDevice . ':' . $currentMinutes;
                    $numberDayMonth = $this->getNumberDayMonth($listAllMonth, $currentNumberMonth, $currentYear);

                    if ($timeDevice === $notifyTime) {

                        if ($currentDayNumber === $numberDayMonth) { // если последний день месяца

                            if ($currentNumberMonth === 11) { // если декабрь, то заходим в будущий год на 1 января
                                $currentYear = $currentYear + 1;
                                $currentNumberMonth = 0;
                                $numberDayMonth = $this->getNumberDayMonth($listAllMonth, $currentNumberMonth, $currentYear);
                                $currentDayNumber = 1;
                                $events = $this->getObjectEvents($currentYear, $cityId);
                                $deviceEvent = $this->getDeviceEvent($events, $listAllMonth[$currentNumberMonth]);
                            } else { // то заходим в следующий месяц на 1 число
                                $currentNumberMonth = $currentNumberMonth + 1;
                                $currentDayNumber = 1;
                                $numberDayMonth = $this->getNumberDayMonth($listAllMonth, $currentNumberMonth, $currentYear);
                                $events = $this->getObjectEvents($currentYear, $cityId);
                                $deviceEvent = $this->getDeviceEvent($events, $listAllMonth[$currentNumberMonth]);
                            }

                        } else { // заходим в следующий день
                            $currentDayNumber = $currentDayNumber + 1;
                            $events = $this->getObjectEvents($currentYear, $cityId);
                            $deviceEvent = $this->getDeviceEvent($events, $listAllMonth[$currentNumberMonth]);
                        }

                    } else {
                        continue;
                    }

                } else {
                    if ($timeDevice < 10) {
                        $timeDevice = '0' . $timeDevice;
                    }

                    $timeDevice = $timeDevice . ':' . $currentMinutes;

                    if ($timeDevice === $notifyTime) {
                        $this->getNumberDayMonth($listAllMonth, $currentNumberMonth, $currentYear);
                        $events = $this->getObjectEvents($currentYear, $cityId);
                        $deviceEvent = $this->getDeviceEvent($events, $listAllMonth[$currentNumberMonth]);
                    } else {
                        continue;
                    }

                }

                if ($events !== null) {

                    foreach ($deviceEvent as $number => $event) {

                        if ($number === '14(2)') {
                            $number = 14;
                        } elseif ($number === '25(2)') {
                            $number = 25;
                        }

                        if ($notifyDay === 0) {

                            if ($currentDayNumber === $number) {
                                $titleNotIf = self::TODAY;
                                $bodyNotIf = $this->initEvent($event);
                                $this->sendNotification($firebaseToken, $titleNotIf, $bodyNotIf, $device);$count++;
                            } else {
                                continue;
                            }

                        } elseif ($notifyDay === 1) {

                            if ($currentDayNumber === $number) {
                                $titleNotIf = self::TODAY;
                                $bodyNotIf = $this->initEvent($event);
                                $this->sendNotification($firebaseToken, $titleNotIf, $bodyNotIf, $device);$count++;
                            } else {
                                $numberDayMonth = $this->getNumberDayMonth($listAllMonth, $currentNumberMonth, $currentYear);

                                if ($currentDayNumber === $numberDayMonth) { // если последний день месяца или года

                                    if ($currentNumberMonth === 11) { // если последний день года, то переходим в следующий год
                                        $currentYear = $currentYear + 1;
                                        $currentDayNumber = 1;
                                        $currentNumberMonth = 0;
                                        $events = $this->getObjectEvents($currentYear, $cityId);
                                        $deviceEventRepeat = $this->getDeviceEvent($events, $listAllMonth[$currentNumberMonth]);

                                        foreach ($deviceEventRepeat as $number => $event) {

                                            if ($currentDayNumber === $number) {
                                                $titleNotIf = self::TOMORROW;
                                                $bodyNotIf = $this->initEvent($event);
                                                $this->sendNotification($firebaseToken, $titleNotIf, $bodyNotIf, $device);$count++;
                                            }

                                        }

                                    } else { // если нет, то переходим в следующий месяц
                                        $currentNumberMonth = $currentNumberMonth + 1;
                                        $currentDayNumber = 1;
                                        $events = $this->getObjectEvents($currentYear, $cityId);
                                        $deviceEventRepeat = $this->getDeviceEvent($events, $listAllMonth[$currentNumberMonth]);

                                        foreach ($deviceEventRepeat as $number => $event) {

                                            if ($currentDayNumber === $number) {
                                                $titleNotIf = self::TOMORROW;
                                                $bodyNotIf = $this->initEvent($event);
                                                $this->sendNotification($firebaseToken, $titleNotIf, $bodyNotIf, $device);$count++;
                                            }

                                        }

                                    }

                                } else {

                                    if (($currentDayNumber + 1) === $number) {
                                        $titleNotIf = self::TOMORROW;
                                        $bodyNotIf = $this->initEvent($event);
                                        $this->sendNotification($firebaseToken, $titleNotIf, $bodyNotIf, $device);$count++;
                                    }

                                }

                            }

                        } elseif ($notifyDay === 2) {

                            if ($currentDayNumber === $number) {
                                $titleNotIf = self::TODAY;
                                $bodyNotIf = $this->initEvent($event);
                                $this->sendNotification($firebaseToken, $titleNotIf, $bodyNotIf, $device);$count++;
                            } else {

                                $numberDayMonth = $this->getNumberDayMonth($listAllMonth, $currentNumberMonth, $currentYear);

                                if (($currentDayNumber + 1) === $numberDayMonth) {

                                    if (($currentDayNumber + 1) === $number) {
                                        $titleNotIf = self::TOMORROW;
                                        $bodyNotIf = $this->initEvent($event);
                                        $this->sendNotification($firebaseToken, $titleNotIf, $bodyNotIf, $device);$count++;
                                    } else {

                                        if ($currentNumberMonth === 11) { // если последний день года, то переходим в следующий год
                                            $currentYear = $currentYear + 1;
                                            $currentDayNumberSub = 1;
                                            $currentNumberMonth = 0;
                                            $events = $this->getObjectEvents($currentYear, $cityId);
                                            $deviceEventRepeat = $this->getDeviceEvent($events, $listAllMonth[$currentNumberMonth]);

                                            foreach ($deviceEventRepeat as $number => $event) {

                                                if ($currentDayNumberSub === $number) {
                                                    $titleNotIf = self::AFTER_TOMORROW;
                                                    $bodyNotIf = $this->initEvent($event);
                                                    $this->sendNotification($firebaseToken, $titleNotIf, $bodyNotIf, $device);$count++;
                                                }

                                            }

                                        } else { // если нет, то переходим в следующий месяц
                                            $currentNumberMonth = $currentNumberMonth + 1;
                                            $currentDayNumberSub = 1;
                                            $events = $this->getObjectEvents($currentYear, $cityId);
                                            $deviceEventRepeat = $this->getDeviceEvent($events, $listAllMonth[$currentNumberMonth]);

                                            foreach ($deviceEventRepeat as $number => $event) {

                                                if ($currentDayNumberSub === $number) {
                                                    $titleNotIf = self::AFTER_TOMORROW;
                                                    $bodyNotIf = $this->initEvent($event);
                                                    $this->sendNotification($firebaseToken, $titleNotIf, $bodyNotIf, $device);$count++;
                                                }

                                            }

                                        }

                                    }

                                } elseif ($currentDayNumber === $numberDayMonth) {

                                    if ($currentNumberMonth === 11) { // если последний день года, то переходим в следующий год
                                        $currentYear = $currentYear + 1;
                                        $events = $this->getObjectEvents($currentYear, $cityId);
                                        $deviceEventRepeat = $this->getDeviceEvent($events, $listAllMonth[0]);

                                        foreach ($deviceEventRepeat as $number => $event) {

                                            if (1 === $number) {
                                                $titleNotIf = self::TOMORROW;
                                                $bodyNotIf = $this->initEvent($event);
                                                $this->sendNotification($firebaseToken, $titleNotIf, $bodyNotIf, $device);$count++;
                                            } elseif (2 === $number) {
                                                $titleNotIf = self::AFTER_TOMORROW;
                                                $bodyNotIf = $this->initEvent($event);
                                                $this->sendNotification($firebaseToken, $titleNotIf, $bodyNotIf, $device);$count++;
                                            }

                                        }

                                    } else { // если нет, то переходим в следующий месяц
                                        $currentNumberMonth = $currentNumberMonth + 1;
                                        $events = $this->getObjectEvents($currentYear, $cityId);
                                        $deviceEventRepeat = $this->getDeviceEvent($events, $listAllMonth[$currentNumberMonth]);

                                        foreach ($deviceEventRepeat as $number => $event) {

                                            if (1 === $number) {
                                                $titleNotIf = self::TOMORROW;
                                                $bodyNotIf = $this->initEvent($event);
                                                $this->sendNotification($firebaseToken, $titleNotIf, $bodyNotIf, $device);$count++;
                                            } elseif (2 === $number) {
                                                $titleNotIf = self::AFTER_TOMORROW;
                                                $bodyNotIf = $this->initEvent($event);
                                                $this->sendNotification($firebaseToken, $titleNotIf, $bodyNotIf, $device);$count++;
                                            }

                                        }

                                    }

                                } elseif (($currentDayNumber + 1) === $number) {
                                    $titleNotIf = self::TOMORROW;
                                    $bodyNotIf = $this->initEvent($event);
                                    $this->sendNotification($firebaseToken, $titleNotIf, $bodyNotIf, $device);$count++;
                                } elseif (($currentDayNumber + 2) === $number) {
                                    $titleNotIf = self::AFTER_TOMORROW;
                                    $bodyNotIf = $this->initEvent($event);
                                    $this->sendNotification($firebaseToken, $titleNotIf, $bodyNotIf, $device);$count++;
                                }

                            }

                        }
                    }

                }

            }
        }
        
        return $count;
    }
}
