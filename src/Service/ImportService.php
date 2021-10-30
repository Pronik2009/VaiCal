<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\Year;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class ImportService
{
    private const HOLIDAYS = [
        // Mandatory leave 0, 1 and 2 rows untouched - this is Ekadasi ankors. All another can be in any order
        '(suitable for fasting)',
        '   Break fast',
        '  Fasting for ',
        'Appearance of Sri Nityananda Prabhu',
        'Appearance of Sri Caitanya Mahaprabhu',
        'Appearance of Lord Sri Ramacandra',
        'Appearance of Lord Nrsimhadeva',
        'Lord Balarama -- Appearance',
        'Appearance of Lord Sri Krsna',
        'Srila Prabhupada -- Appearance',
        'Appearance of Srimati Radharani',
        'Govardhana Puja',
        '  Ratha Yatra',
//        'Srila Prabhupada -- Disappearance',
    ];
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param array $file
     * @param string $cityName
     * @param string|null $format
     */
    public function parseAndSave(array $file, string $cityName, string $format = null): void
    {
        if ($format === 'gcal') {
            $fileClear = $this->clearUnusedRows($file);
            $years = $this->parseFileClearToYear($fileClear);
        } else {
            $years = $this->parseFileToYear($file);
        }
        $city = $this->getCityByName($cityName);

        foreach ($years as $key => $value) {
            $year = new Year();
            $year->setCity($city);
            $year->setValue($key);
            $year->setJan($value[1] ?? null);
            $year->setFeb($value[2] ?? null);
            $year->setMar($value[3] ?? null);
            $year->setApr($value[4] ?? null);
            $year->setMay($value[5] ?? null);
            $year->setJun($value[6] ?? null);
            $year->setJul($value[7] ?? null);
            $year->setAug($value[8] ?? null);
            $year->setSem($value[9] ?? null);
            $year->setOct($value[10] ?? null);
            $year->setNov($value[11] ?? null);
            $year->setDem($value[12] ?? null);

            $this->em->persist($year);
        }
        $this->em->flush();
    }

    /**
     * @param array $file
     *
     * @return array
     */
    private function parseFileToYear(array $file): array
    {
        // Parse data to array [key=year; value=[all Other]]
        $year = [];
        $key = 0;
        foreach ($file as $row) {
            $len = strlen($row);
            if ($len === 4) {
                $key = $row;
                $year[$key] = [];
            } else {
                $year[$key][] .= $row;
            }
        }

        // Parse each new array (all_other) to readable state
        foreach ($year as $key => $value) {
            $month = [];
            $monthIndex = 0;
            foreach ($value as $item) {
                $len = strlen($item);
                if ($len === 2) {
                    $monthIndex = (int)$item;
                    $month[$monthIndex] = [];
                } else {
                    $day = substr($item, 1, 2);
                    $holiday = substr($item, 4, 1);
                    $month[$monthIndex][(int)$day] = $holiday;
                }
            }
            $year[$key] = $month;
        }

        return $year;
    }

    /**
     * @param string $cityName
     *
     * @return City
     */
    private function getCityByName(string $cityName): City
    {
        $city = $this->em->getRepository(City::class)->findOneBy(["name" => $cityName]);

        if (!$city) {
            $city = new City();
            $city->setName($cityName);
            $this->em->persist($city);
            $this->em->flush();
        }

        return $city;
    }

    /**
     * @param array $file
     *
     * @return array
     */
    private function clearUnusedRows(array $file): array
    {
        $result = [];

        //чистим все пустые строки
        $i = 0;
        $count = count($file);
        while ($i < $count) {
            if (empty($file[$i])){
                unset($file[$i]);
            }
            $i++;
        }
        $file = array_values($file);

        //удаляем все строки не с датами в начале кроме нужных
        $i = 0;
        $count = count($file);
        while ($i < $count) {
            if (!is_numeric($file[$i][1]) && !$this->inHolidays($file[$i])){
                unset($file[$i]);
            }
            $i++;
        }
        $file = array_values($file);

        //складываем искомое в одностроковый формат
        $i = 0;
        $count = count($file);
        while ($i < $count) {
            // найден Экадаши - оставляем только дату,
            // забираем к ней имя Экадаши из следующей строки,
            // и выход из поста из позаследующей сстроки
            if (is_numeric($file[$i][1]) && strpos($file[$i], $this::HOLIDAYS[0])) {
                $result[] = substr($file[$i], 0, 11) . ' '
                    . substr($file[$i+1], 17) . ' '
                    . substr($file[$i+3], 17);
                $i+=2;
                continue;
            }
            //найден один из праздников - оставляем только текст, забираем дату из предыдущей строки (exclude "break fast" because Nityananda issue)
            if (!is_numeric($file[$i][1]) && $this->inHolidays($file[$i]) && !strpos($file[$i], $this::HOLIDAYS[1])) {
                //check if previous row have date, if not - take previous from previous (Nityananda issue)
                $dateSubStr = is_numeric($file[$i-1][1]) ? substr($file[$i-1], 0, 11) : substr($file[$i-2], 0, 11);
                $result[] = $dateSubStr . ' ' . substr($file[$i], 17);
            }
            //ничего интересного не найдено
            $i++;
        }

        return $result;
    }

    /**
     * @param string $string
     * @param bool $responseFormat
     *
     * @return bool|int
     * Use $responseFormat = TRUE only in final stage, when surely have some holiday's text
     */
    private function inHolidays(string $string, bool $responseFormat = false)
    {
        foreach ($this::HOLIDAYS as $key=>$HOLIDAY) {
            if (strpos($string, $responseFormat ? trim($HOLIDAY) : $HOLIDAY)!==false) {
                return $responseFormat ? $key : true;
            }
        }

        return false;
    }

    /**
     * @param array $file
     *
     * @return array
     */
    private function parseFileClearToYear(array $file): array
    {
        // Parse data to array [key=year; value=[all Other]]
        $years = [];

        //search and create all possible keys
        foreach ($file as $row) {
            $date = DateTimeImmutable::createFromFormat(' j M Y', substr($row, 0, 11));
            $year = $date->format('Y');
            $month = $date->format('n');
            $day = $date->format('j');

            if (!array_key_exists($year, $years)){
                $years[$year] = [];
            }
            if (!array_key_exists($month, $years[$year])){
                $years[$year][$month] = [];
            }
            $years[$year][$month][$day] = $this->holidayCodeFromString($row);
        }

        return $years;
    }

    /**
     * @param string $row
     *
     * @return array|string
     */
    private function holidayCodeFromString(string $row)
    {
        $row = substr($row, 12);
        switch ($this->inHolidays($row, true)) {
            case 1:
                $ekadasiPosition = strpos($row, ' Ekadasi');
                $ekadasiName = substr($row, 12, $ekadasiPosition - strlen($row));
                $lightTime = str_contains($row, '(DST)') ? 'DST' : 'LT';
                $exitTime = substr(substr($row, $ekadasiPosition + 20), 0, $lightTime === 'DST' ? -6 : -5);

                return [
                    'holiday' => '0',
                    'ekadasi_name' => $ekadasiName,
                    'exit_time' => $exitTime,
                    'light_time' => $lightTime,
                ];
            case 3:
                return '1';
            case 4:
                return '2';
            case 5:
                return '3';
            case 6:
                return '4';
            case 7:
                return '6';
            case 8:
                return '7';
            case 9:
                return '8';
            case 10:
                return '9';
            case 11:
                return 'A';
            case 12:
                return 'B';
            case 13:
                return 'C';
            default:
                return 'ERROR!';
        }
    }
}
