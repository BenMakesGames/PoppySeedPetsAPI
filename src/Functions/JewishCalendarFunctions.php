<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Functions;

// code from http://www.david-greve.de/luach-code/jewish-php.html, with several modifications for the purposes of
// wrapping up into a class
class JewishCalendarFunctions
{
    public const int Tishri = 1;
    public const int Heshvan = 2;
    public const int Kislev = 3;
    public const int Tevet = 4;
    public const int Shevat = 5;
    public const int AdarI = 6;
    public const int AdarII = 7;
    public const int Adar = 7;
    public const int Nisan = 8;
    public const int Iyar = 9;
    public const int Sivan = 10;
    public const int Tammuz = 11;
    public const int Av = 12;
    public const int Elul = 13;

    public const int Sunday = 0;
    public const int Monday = 1;
    public const int Tuesday = 2;
    public const int Wednesday = 3;
    public const int Thursday = 4;
    public const int Friday = 5;
    public const int Saturday = 6;

    public const array MonthNamesLeapYear = [
        'Tishri', 'Heshvan', 'Kislev', 'Tevet',
        'Shevat', 'Adar I', 'Adar II', 'Nisan',
        'Iyar', 'Sivan', 'Tammuz', 'Av', 'Elul'
    ];

    public const array MonthNamesNonLeapYear = [
        'Tishri', 'Heshvan', 'Kislev', 'Tevet',
        'Shevat', '', 'Adar', 'Nisan',
        'Iyar', 'Sivan', 'Tammuz', 'Av', 'Elul'
    ];

    public static function isJewishLeapYear(int $year): bool
    {
        return (
            $year % 19 == 0 || $year % 19 == 3 || $year % 19 == 6 ||
            $year % 19 == 8 || $year % 19 == 11 || $year % 19 == 14 ||
            $year % 19 == 17
        );
    }

    public static function getJewishMonthName(int $jewishMonth, int $jewishYear): string
    {
        if (self::isJewishLeapYear($jewishYear))
            return self::MonthNamesLeapYear[$jewishMonth - 1];
        else
            return self::MonthNamesNonLeapYear[$jewishMonth - 1];
    }

    /**
     * @return int[] Date in jewish calendar, as [ year, month, day ]
     */
    public static function getJewishDate(\DateTimeInterface $dt): array
    {
        $jdCurrent = gregoriantojd((int)$dt->format('m'), (int)$dt->format('d'), (int)$dt->format('Y'));
        $jewishDate = jdtojewish($jdCurrent);
        [$jewishMonth, $jewishDay, $jewishYear] = explode('/', $jewishDate);

        return [ (int)$jewishYear, (int)$jewishMonth, (int)$jewishDay ];
    }

    public static function isHanukkah(\DateTimeInterface $dt): bool
    {
        $jdCurrent = gregoriantojd((int)$dt->format('m'), (int)$dt->format('d'), (int)$dt->format('Y'));
        [$jewishYear, $jewishMonth, $jewishDay] = self::getJewishDate($dt);

        $hanukkahStart = jewishtojd(self::Kislev, 25, $jewishYear);
        $hanukkahNo = (int) ($jdCurrent-$hanukkahStart+1);

        return $hanukkahNo >= 1 && $hanukkahNo <= 8;
    }

    public static function getJewishHoliday(
        \DateTimeInterface $dt, bool $isDiaspora, bool $postponeShushanPurimOnSaturday
    ): array
    {
        $result = [];

        $jdCurrent = gregoriantojd((int)$dt->format('m'), (int)$dt->format('d'), (int)$dt->format('Y'));

        [$jewishYear, $jewishMonth, $jewishDay] = self::getJewishDate($dt);

        // Holidays in Elul
        if ($jewishDay == 29 && $jewishMonth == self::Elul)
            $result[] = "Erev Rosh Hashanah";

        // Holidays in Tishri
        if ($jewishDay == 1 && $jewishMonth == self::Tishri)
            $result[] = "Rosh Hashanah I";

        if ($jewishDay == 2 && $jewishMonth == self::Tishri)
            $result[] = "Rosh Hashanah II";

        $jd = jewishtojd(self::Tishri, 3, $jewishYear);
        $weekdayNo = jddayofweek($jd, CAL_DOW_DAYNO);

        if ($weekdayNo == self::Saturday) { // If the 3 Tishri would fall on Saturday ...
            // ... postpone Tzom Gedaliah to Sunday
            if ($jewishDay == 4 && $jewishMonth == self::Tishri)
                $result[] = "Tzom Gedaliah";
        } else {
            if ($jewishDay == 3 && $jewishMonth == self::Tishri)
                $result[] = "Tzom Gedaliah";
        }
        if ($jewishDay == 9 && $jewishMonth == self::Tishri)
            $result[] = "Erev Yom Kippur";
        if ($jewishDay == 10 && $jewishMonth == self::Tishri)
            $result[] = "Yom Kippur";
        if ($jewishDay == 14 && $jewishMonth == self::Tishri)
            $result[] = "Erev Sukkot";
        if ($jewishDay == 15 && $jewishMonth == self::Tishri)
            $result[] = "Sukkot I";
        if ($jewishDay == 16 && $jewishMonth == self::Tishri && $isDiaspora)
            $result[] = "Sukkot II";

        if ($isDiaspora) {
            if ($jewishDay >= 17 && $jewishDay <= 20 && $jewishMonth == self::Tishri)
                $result[] = "Hol Hamoed Sukkot";
        } else {
            if ($jewishDay >= 16 && $jewishDay <= 20 && $jewishMonth == self::Tishri)
                $result[] = "Hol Hamoed Sukkot";
        }

        if ($jewishDay == 21 && $jewishMonth == self::Tishri)
            $result[] = "Hoshana Rabbah";

        if ($isDiaspora) {
            if ($jewishDay == 22 && $jewishMonth == self::Tishri)
                $result[] = "Shemini Azeret";
            if ($jewishDay == 23 && $jewishMonth == self::Tishri)
                $result[] = "Simchat Torah";
            if ($jewishDay == 24 && $jewishMonth == self::Tishri)
                $result[] = "Isru Chag";
        } else {
            if ($jewishDay == 22 && $jewishMonth == self::Tishri)
                $result[] = "Shemini Azeret/Simchat Torah";
            if ($jewishDay == 23 && $jewishMonth == self::Tishri)
                $result[] = "Isru Chag";
        }

        // Holidays in Kislev/Tevet
        $hanukkahStart = jewishtojd(self::Kislev, 25, $jewishYear);
        $hanukkahNo = $jdCurrent - $hanukkahStart + 1;

        if ($hanukkahNo == 1) $result[] = "Hanukkah I";
        if ($hanukkahNo == 2) $result[] = "Hanukkah II";
        if ($hanukkahNo == 3) $result[] = "Hanukkah III";
        if ($hanukkahNo == 4) $result[] = "Hanukkah IV";
        if ($hanukkahNo == 5) $result[] = "Hanukkah V";
        if ($hanukkahNo == 6) $result[] = "Hanukkah VI";
        if ($hanukkahNo == 7) $result[] = "Hanukkah VII";
        if ($hanukkahNo == 8) $result[] = "Hanukkah VIII";

        // Holidays in Tevet
        $jd = jewishtojd(self::Tevet, 10, $jewishYear);
        $weekdayNo = jddayofweek($jd, CAL_DOW_DAYNO);

        if ($weekdayNo == self::Saturday) { // If the 10 Tevet would fall on Saturday ...
            // ... postpone Tzom Tevet to Sunday
            if ($jewishDay == 11 && $jewishMonth == self::Tevet)
                $result[] = "Tzom Tevet";
        } else {
            if ($jewishDay == 10 && $jewishMonth == self::Tevet)
                $result[] = "Tzom Tevet";
        }

        // Holidays in Shevat
        if ($jewishDay == 15 && $jewishMonth == self::Shevat)
            $result[] = "Tu B'Shevat";

        // Holidays in Adar I
        if (self::isJewishLeapYear($jewishYear) && $jewishDay == 14 && $jewishMonth == self::AdarI)
            $result[] = "Purim Katan";

        if (self::isJewishLeapYear($jewishYear) && $jewishDay == 15 && $jewishMonth == self::AdarI)
            $result[] = "Shushan Purim Katan";

        // Holidays in Adar or Adar II
        if (self::isJewishLeapYear($jewishYear))
            $purimMonth = self::AdarII;
        else
            $purimMonth = self::Adar;

        $jd = jewishtojd($purimMonth, 13, $jewishYear);
        $weekdayNo = jddayofweek($jd, CAL_DOW_DAYNO);

        if ($weekdayNo == self::Saturday) { // If the 13 Adar or Adar II would fall on Saturday ...
            // ... move Ta'anit Esther to the preceding Thursday
            if ($jewishDay == 11 && $jewishMonth == $purimMonth)
                $result[] = "Ta'anith Esther";
        } else {
            if ($jewishDay == 13 && $jewishMonth == $purimMonth)
                $result[] = "Ta'anith Esther";
        }

        if ($jewishDay == 14 && $jewishMonth == $purimMonth)
            $result[] = "Purim";

        if ($postponeShushanPurimOnSaturday) {
            $jd = jewishtojd($purimMonth, 15, $jewishYear);
            $weekdayNo = jddayofweek($jd, CAL_DOW_DAYNO);
            if ($weekdayNo == self::Saturday) { // If the 15 Adar or Adar II would fall on Saturday ...
                // ... postpone Shushan Purim to Sunday
                if ($jewishDay == 16 && $jewishMonth == $purimMonth)
                    $result[] = "Shushan Purim";
            } else {
                if ($jewishDay == 15 && $jewishMonth == $purimMonth)
                    $result[] = "Shushan Purim";
            }
        } else {
            if ($jewishDay == 15 && $jewishMonth == $purimMonth)
                $result[] = "Shushan Purim";
        }

        // Holidays in Nisan
        $shabbatHagadolDay = 14;
        $jd = jewishtojd(self::Nisan, $shabbatHagadolDay, $jewishYear);

        while (jddayofweek($jd, CAL_DOW_DAYNO) != self::Saturday) {
            $jd--;
            $shabbatHagadolDay--;
        }

        if ($jewishDay == $shabbatHagadolDay && $jewishMonth == self::Nisan)
            $result[] = "Shabbat Hagadol";
        if ($jewishDay == 14 && $jewishMonth == self::Nisan)
            $result[] = "Erev Pesach";
        if ($jewishDay == 15 && $jewishMonth == self::Nisan)
            $result[] = "Pesach I";
        if ($jewishDay == 16 && $jewishMonth == self::Nisan && $isDiaspora)
            $result[] = "Pesach II";

        if ($isDiaspora) {
            if ($jewishDay >= 17 && $jewishDay <= 20 && $jewishMonth == self::Nisan)
                $result[] = "Hol Hamoed Pesach";
        } else {
            if ($jewishDay >= 16 && $jewishDay <= 20 && $jewishMonth == self::Nisan)
                $result[] = "Hol Hamoed Pesach";
        }

        if ($jewishDay == 21 && $jewishMonth == self::Nisan)
            $result[] = "Pesach VII";

        if ($jewishDay == 22 && $jewishMonth == self::Nisan && $isDiaspora)
            $result[] = "Pesach VIII";

        if ($isDiaspora) {
            if ($jewishDay == 23 && $jewishMonth == self::Nisan)
                $result[] = "Isru Chag";
        } else {
            if ($jewishDay == 22 && $jewishMonth == self::Nisan)
                $result[] = "Isru Chag";
        }

        $jd = jewishtojd(self::Nisan, 27, $jewishYear);
        $weekdayNo = jddayofweek($jd, 0);

        if ($weekdayNo == self::Friday) { // If the 27 Nisan would fall on Friday ...
            // ... then Yom Hashoah falls on Thursday
            if ($jewishDay == 26 && $jewishMonth == self::Nisan)
                $result[] = "Yom Hashoah";
        } else {
            if ($jewishYear >= 5757) { // Since 1997 (5757) ...
                if ($weekdayNo == self::Sunday) { // If the 27 Nisan would fall on Friday ...
                    // ... then Yom Hashoah falls on Thursday
                    if ($jewishDay == 28 && $jewishMonth == self::Nisan)
                        $result[] = "Yom Hashoah";
                } else {
                    if ($jewishDay == 27 && $jewishMonth == self::Nisan)
                        $result[] = "Yom Hashoah";
                }
            } else {
                if ($jewishDay == 27 && $jewishMonth == self::Nisan)
                    $result[] = "Yom Hashoah";
            }
        }

        // Holidays in Iyar

        $jd = jewishtojd(self::Iyar, 4, $jewishYear);
        $weekdayNo = jddayofweek($jd, CAL_DOW_DAYNO);

        // If the 4 Iyar would fall on Friday or Thursday ...
        // ... then Yom Hazikaron falls on Wednesday and Yom Ha'Atzmaut on Thursday
        if ($weekdayNo == self::Friday) {
            if ($jewishDay == 2 && $jewishMonth == self::Iyar)
                $result[] = "Yom Hazikaron";
            if ($jewishDay == 3 && $jewishMonth == self::Iyar)
                $result[] = "Yom Ha'Atzmaut";
        } else {
            if ($weekdayNo == self::Thursday) {
                if ($jewishDay == 3 && $jewishMonth == self::Iyar)
                    $result[] = "Yom Hazikaron";
                if ($jewishDay == 4 && $jewishMonth == self::Iyar)
                    $result[] = "Yom Ha'Atzmaut";
            } else {
                if ($jewishYear >= 5764) { // Since 2004 (5764) ...
                    if ($weekdayNo == self::Sunday) { // If the 4 Iyar would fall on Sunday ...
                        // ... then Yom Hazicaron falls on Monday
                        if ($jewishDay == 5 && $jewishMonth == self::Iyar)
                            $result[] = "Yom Hazikaron";
                        if ($jewishDay == 6 && $jewishMonth == self::Iyar)
                            $result[] = "Yom Ha'Atzmaut";
                    } else {
                        if ($jewishDay == 4 && $jewishMonth == self::Iyar)
                            $result[] = "Yom Hazikaron";
                        if ($jewishDay == 5 && $jewishMonth == self::Iyar)
                            $result[] = "Yom Ha'Atzmaut";
                    }
                } else {
                    if ($jewishDay == 4 && $jewishMonth == self::Iyar)
                        $result[] = "Yom Hazikaron";
                    if ($jewishDay == 5 && $jewishMonth == self::Iyar)
                        $result[] = "Yom Ha'Atzmaut";
                }
            }
        }

        if ($jewishDay == 14 && $jewishMonth == self::Iyar)
            $result[] = "Pesach Sheini";
        if ($jewishDay == 18 && $jewishMonth == self::Iyar)
            $result[] = "Lag B'Omer";
        if ($jewishDay == 28 && $jewishMonth == self::Iyar)
            $result[] = "Yom Yerushalayim";

        // Holidays in Sivan
        if ($jewishDay == 5 && $jewishMonth == self::Sivan)
            $result[] = "Erev Shavuot";
        if ($jewishDay == 6 && $jewishMonth == self::Sivan)
            $result[] = "Shavuot I";
        if ($jewishDay == 7 && $jewishMonth == self::Sivan && $isDiaspora)
            $result[] = "Shavuot II";

        if ($isDiaspora) {
            if ($jewishDay == 8 && $jewishMonth == self::Sivan)
                $result[] = "Isru Chag";
        } else {
            if ($jewishDay == 7 && $jewishMonth == self::Sivan)
                $result[] = "Isru Chag";
        }

        // Holidays in Tammuz
        $jd = jewishtojd(self::Tammuz, 17, $jewishYear);
        $weekdayNo = jddayofweek($jd, CAL_DOW_DAYNO);

        if ($weekdayNo == self::Saturday) { // If the 17 Tammuz would fall on Saturday ...
            // ... postpone Tzom Tammuz to Sunday
            if ($jewishDay == 18 && $jewishMonth == self::Tammuz)
                $result[] = "Tzom Tammuz";
        } else {
            if ($jewishDay == 17 && $jewishMonth == self::Tammuz)
                $result[] = "Tzom Tammuz";
        }

        // Holidays in Av
        $jd = jewishtojd(self::Av, 9, $jewishYear);
        $weekdayNo = jddayofweek($jd, CAL_DOW_DAYNO);

        if ($weekdayNo == self::Saturday) { // If the 9 Av would fall on Saturday ...
            // ... postpone Tisha B'Av to Sunday
            if ($jewishDay == 10 && $jewishMonth == self::Av)
                $result[] = "Tisha B'Av";
        } else {
            if ($jewishDay == 9 && $jewishMonth == self::Av)
                $result[] = "Tisha B'Av";
        }

        if ($jewishDay == 15 && $jewishMonth == self::Av)
            $result[] = "Tu B'Av";

        return $result;
    }
}
