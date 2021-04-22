<?php

namespace App\Service;

use App\Entity\City;
use App\Entity\Year;
use Doctrine\ORM\EntityManagerInterface;

class ImportService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param array $file
     * @param string $cityName
     */
    public function parseAndSave(array $file, string $cityName): void
    {
        $years = $this->parseFileToYear($file);
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
}
