<?php

namespace App\Command;

use App\Entity\City;
use App\Entity\Device;
use App\Entity\Year;
use Doctrine\ORM\EntityManagerInterface;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\Messaging as MessagingErrors;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Exception\NotFound;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Factory;

class NotificatorProcessor
{
    private const APPEREANCE = 'Явление ';
    private const DISAPPEREANCE = 'Уход ';
    private const EKADASI = ' Экадаши';
    private const AFTER_TOMORROW = 'Послезавтра'; 
    private const TOMORROW = 'Завтра';
    private const TODAY =    'Сегодня';
    private const EVENT_1 = 'Нитьянанды Прабху';
    private const EVENT_2 = 'Гаура-Пурнима, ' . self::APPEREANCE . 'Шри Чайтаньи Махапрабху';
    private const EVENT_3 = 'Рама Навами, ' . self::APPEREANCE . 'Рамачандры';
    private const EVENT_4 = 'Нришимхадева';
    private const EVENT_6 = 'Баларамы';
    private const EVENT_7 = 'Джанмастами, ' . self::APPEREANCE . ' Шри Кришны';
    private const EVENT_8 = 'Бхактиведанты Свами Прабхупада';
    private const EVENT_9 = 'Радхастами, ' . self::APPEREANCE . ' Шримати Радхарани';
    private const EVENT_A = 'Говардхана Пуджа';
    private const EVENT_B = 'Ратха-Ятра';
    private const EVENT_D = 'Бхактисиддханты Сарасвати Тхакура';
    private const EVENT_E = 'Бхактивиноды Тхакура';
    private const EVENT_SP = 'Вьясапуджа, ' . self::APPEREANCE . 'Сиддхасварупананды Парамахамсы Прабхупада';
    private const EVENT_CR = 'Рождество, ' . self::APPEREANCE . ' Иисуса Христоса';
    private const CHRISTMAS_KEY = 'CR';
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
            $deviceEvent = $events->getJan();
        } elseif ($fullNameMonth === 'February') {
            $deviceEvent = $events->getFeb();
        } elseif ($fullNameMonth === 'March') {
            $deviceEvent = $events->getMar();
        } elseif ($fullNameMonth === 'April') {
            $deviceEvent = $events->getApr();
        } elseif ($fullNameMonth === 'May') {
            $deviceEvent = $events->getMay();
        } elseif ($fullNameMonth === 'June') {
            $deviceEvent = $events->getJun();
            $searchKey = array_key_exists(14, $deviceEvent);

            if ($searchKey) {
                $deviceEvent['14(2)'] = 'SP';
            } else {
                $deviceEvent[14] = 'SP';
            }

        } elseif ($fullNameMonth === 'July') {
            $deviceEvent = $events->getJul();
        } elseif ($fullNameMonth === 'August') {
            $deviceEvent = $events->getAug();
        } elseif ($fullNameMonth === 'September') {
            $deviceEvent = $events->getSem();
        } elseif ($fullNameMonth === 'October') {
            $deviceEvent = $events->getOct();
        } elseif ($fullNameMonth === 'November') {
            $deviceEvent = $events->getNov();
        } elseif ($fullNameMonth === 'December') {
            $deviceEvent = $events->getDem();

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
            '1' => self::APPEREANCE . self::EVENT_1,
            '2' => self::EVENT_2,
            '3' => self::EVENT_3,
            '4' => self::APPEREANCE . self::EVENT_4,
            '6' => self::APPEREANCE . self::EVENT_6,
            '7' => self::EVENT_7,
            '8' => self::APPEREANCE . self::EVENT_8,
            '9' => self::EVENT_9,
            'A' => self::EVENT_A,
            'B' => self::EVENT_B,
            'C' => self::DISAPPEREANCE . self::EVENT_8,
            'D' => self::APPEREANCE . self::EVENT_D,
            'E' => self::APPEREANCE . self::EVENT_E,
            'SP' => self::EVENT_SP,
            self::CHRISTMAS_KEY => self::EVENT_CR,
        };
    }

    private function getObjectEvents(int $year, ?int $id): Year
    {
        $return_year = $this->database->getRepository(Year::class)
                                      ->createQueryBuilder('y')
                                      ->andWhere('y.value = ' . $year)
                                      ->andWhere('y.city = :city')
                                      ->setParameter('city', $id)
                                      ->getQuery()
                                      ->getResult();
        
        return current($return_year);
    }
    
    private function deviceCookie(Device $device) {
        
    }

    /**
     * @throws MessagingException
     * @throws FirebaseException
     */
    private function sendNotification(string $deviceToken, string $title, string|array $event, Device $device): bool
    {   
        $factory = $this->factory();
        $messaging = $factory->createMessaging();
        $notification = Notification::create($title, $this->initEvent($event));
        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification($notification);
        $contentMail = '';
        $themeMail = '';
        $deviceDelete = false;
        
        try {
            $messaging->send($message);
        } catch (MessagingErrors\NotFound $e) {
            $this->database->remove($device);
            $contentMail = 'Id Device - ' . $device->getId();
            $themeMail = 'Delete Device';
            $deviceDelete = true;
        } catch (MessagingErrors\InvalidMessage $e) {
            $contentMail = 'Id Device - ' . $device->getId() . '; ' . $e->getMessage();
            $themeMail = 'Invalid Message';
            
            if ($e->getMessage() === 'The registration token is not a valid FCM registration token') {
                $this->database->remove($device);
                $deviceDelete = true;
            }
            
        } catch (MessagingErrors\ServerUnavailable $e) {
            $retryAfter = $e->retryAfter();
            $contentMail = 'Id Device - ' . $device->getId() . '; ' . $e->getMessage();
            $themeMail = 'Server Unavailable';
        } catch (MessagingErrors\ServerError $e) {
            $contentMail = 'Id Device - ' . $device->getId() . '; ' . $e->getMessage();
            $themeMail = 'Server Error';
        } catch (MessagingException $e) {
            $contentMail = 'Id Device - ' . $device->getId() . '; ' . $e->getMessage();
            $themeMail = 'Error';
        }
        
        if ($contentMail !== '' && $themeMail !== '') mail($_ENV['EMAIL_ERROR_NOTIFICATION'], $themeMail, $contentMail); 
    
        if ($deviceDelete) {
             return true;
        } else {
            return false;
        }

    }

    /**
     * @throws MessagingException
     * @throws FirebaseException
     */
    final public function initNotification(): array
    {
        $success = 0;
        $fail = 0;
        $devices = $this->database->getRepository(Device::class)->findAll();
        $listAllMonth = [
            'January',
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

            if ($currentMinutes <= 59) {
                $currentMinutes = 0;
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
                
                // Здесь нужно подумать как добавлять час $timeDevice при переходе на летнее время

                if ($timeDevice < 0) {
                    $timeDevice = $timeDevice + 24;

                    if ($timeDevice < 10) $timeDevice = '0' . $timeDevice;

                    $timeDevice = $timeDevice . ':' . $currentMinutes;

                    if ($timeDevice === $notifyTime) {

                        if ($currentDayNumber === 1) { // если 1 число

                            if ($currentNumberMonth === 0) { // если январь, то заходим в прошлый год на 31 декабря
                                $currentYear = $currentYear - 1;
                                $currentDayNumber = 31;
                                $deviceEvent = $this->getDeviceEvent($this->getObjectEvents($currentYear, $cityId), $listAllMonth[11]);
                            } else { // то заходим в прошлый месяц
                                $currentNumberMonth = $currentNumberMonth - 1;
                                $numberDayMonth = $this->getNumberDayMonth($listAllMonth, $currentNumberMonth, $currentYear);
                                $currentDayNumber = $numberDayMonth;
                                $deviceEvent = $this->getDeviceEvent($this->getObjectEvents($currentYear, $cityId), $listAllMonth[$currentNumberMonth]);
                            }

                        } else { // то заходим во вчерашний день
                            $numberDayMonth = $this->getNumberDayMonth($listAllMonth, $currentNumberMonth, $currentYear);
                            $currentDayNumber = $currentDayNumber - 1;
                            $deviceEvent = $this->getDeviceEvent($this->getObjectEvents($currentYear, $cityId), $listAllMonth[$currentNumberMonth]);
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
                                $numberDayMonth = $this->getNumberDayMonth($listAllMonth, 0, $currentYear);
                                $currentDayNumber = 1;
                                $deviceEvent = $this->getDeviceEvent($this->getObjectEvents($currentYear, $cityId), $listAllMonth[0]);
                            } else { // то заходим в следующий месяц на 1 число
                                $currentNumberMonth = $currentNumberMonth + 1;
                                $currentDayNumber = 1;
                                $numberDayMonth = $this->getNumberDayMonth($listAllMonth, $currentNumberMonth, $currentYear);
                                $deviceEvent = $this->getDeviceEvent($this->getObjectEvents($currentYear, $cityId), $listAllMonth[$currentNumberMonth]);
                            }

                        } else { // заходим в следующий день
                            $currentDayNumber = $currentDayNumber + 1;
                            $deviceEvent = $this->getDeviceEvent($this->getObjectEvents($currentYear, $cityId), $listAllMonth[$currentNumberMonth]);
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
                        $numberDayMonth = $this->getNumberDayMonth($listAllMonth, $currentNumberMonth, $currentYear);
                        $deviceEvent = $this->getDeviceEvent($this->getObjectEvents($currentYear, $cityId), $listAllMonth[$currentNumberMonth]);
                    } else {
                        continue;
                    }

                }

                if (isset($deviceEvent)) {

                    foreach ($deviceEvent as $number => $event) {

                        if ($number === '14(2)') {
                            $number = 14;
                        } elseif ($number === '25(2)') {
                            $number = 25;
                        }

                        if ($currentDayNumber === $number) {
                            $this->sendNotification($firebaseToken, self::TODAY, $event, $device) ? $fail++ : $success++;
                        }

                        if ($notifyDay === 1 || $notifyDay === 2) {

                            if (($currentDayNumber + 1) === $number) {
                                $this->sendNotification($firebaseToken, self::TOMORROW, $event, $device) ? $fail++ : $success++;
                            }
                            
                            if ($notifyDay === 2) {

                                if (($currentDayNumber + 2) === $number) {
                                    $this->sendNotification($firebaseToken, self::AFTER_TOMORROW, $event, $device) ? $fail++ : $success++;
                                }
    
                            }

                        }
                        
                    }
                
                    // Заглянем в следующий месяц или следующий год, если последний или предпоследний день месяца
                    if ($notifyDay === 1 || $notifyDay === 2) {
                        $numberDayMonth = $this->getNumberDayMonth($listAllMonth, $currentNumberMonth, $currentYear);
                        
                        if (($currentDayNumber + 1) === $numberDayMonth && $notifyDay === 2) { // если предпоследний день месяца или года
            
                            if ($currentNumberMonth === 11) { // если декабрь, то переходим в следующий год
                                $currentYearSub = $currentYear + 1;
                                $deviceEventRepeat = $this->getDeviceEvent($this->getObjectEvents($currentYearSub, $cityId), 
                                                                           $listAllMonth[0]);

                                foreach ($deviceEventRepeat as $number => $event) {

                                    if (1 === $number) {
                                        $this->sendNotification($firebaseToken, self::AFTER_TOMORROW, $event, $device) ? $fail++ : $success++;
                                    }

                                }

                            } else { // если нет, то переходим в следующий месяц
                                $currentNumberMonthSub = $currentNumberMonth + 1;
                                $deviceEventRepeat = $this->getDeviceEvent($this->getObjectEvents($currentYear, $cityId), 
                                                                           $listAllMonth[$currentNumberMonthSub]);

                                foreach ($deviceEventRepeat as $number => $event) {

                                    if (1 === $number) {
                                        $this->sendNotification($firebaseToken, self::AFTER_TOMORROW, $event, $device) ? $fail++ : $success++;
                                    }

                                }

                            }
                                    
                        } elseif ($currentDayNumber === $numberDayMonth) { // если последний день месяца или года
                
                            if ($currentNumberMonth === 11) { // если последний день года, то переходим в следующий год
                                $currentYearSub = $currentYear + 1;
                                $deviceEventRepeat = $this->getDeviceEvent($this->getObjectEvents($currentYearSub, $cityId), 
                                                                           $listAllMonth[0]);
    
                                foreach ($deviceEventRepeat as $number => $event) {
    
                                    if (1 === $number) {
                                        $this->sendNotification($firebaseToken, self::TOMORROW, $event, $device) ? $fail++ : $success++;
                                    }
                                    
                                    if ($notifyDay === 2) {
                                    
                                        if (2 === $number) {
                                            $this->sendNotification($firebaseToken, self::AFTER_TOMORROW, $event, $device) ? $fail++ : $success++;
                                        }
                                        
                                    }
    
                                }
    
                            } else { // если нет, то переходим в следующий месяц
                                $currentNumberMonthSub = $currentNumberMonth + 1;
                                $deviceEventRepeat = $this->getDeviceEvent($this->getObjectEvents($currentYear, $cityId), 
                                                                           $listAllMonth[$currentNumberMonthSub]);
    
                                foreach ($deviceEventRepeat as $number => $event) {
    
                                    if (1 === $number) {
                                        $this->sendNotification($firebaseToken, self::TOMORROW, $event, $device) ? $fail++ : $success++;
                                    }
                                    
                                    if ($notifyDay === 2) {
                                    
                                        if (2 === $number) {
                                            $this->sendNotification($firebaseToken, self::AFTER_TOMORROW, $event, $device) ? $fail++ : $success++;
                                        }
                                        
                                    }
    
                                }
    
                            }
                                         
                        }
                        
                    }
        
                }
                
            }

        }

        if ($fail !== 0) {
            $this->database->flush();
        }
        
        return [ 
            'success' => $success, 
            'fail' => $fail,
        ];
    }
}
