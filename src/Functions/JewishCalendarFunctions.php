<?php
namespace App\Functions;

// code from http://www.david-greve.de/luach-code/jewish-php.html, with several modifications for the purposes of
// wrapping up into a class
class JewishCalendarFunctions
{
    public const TISHRI = 1;
    public const HESHVAN = 2;
    public const KISLEV = 3;
    public const TEVET = 4;
    public const SHEVAT = 5;
    public const ADAR_I = 6;
    public const ADAR_II = 7;
    public const ADAR = 7;
    public const NISAN = 8;
    public const IYAR = 9;
    public const SIVAN = 10;
    public const TAMMUZ = 11;
    public const AV = 12;
    public const ELUL = 13;

    public const SUNDAY = 0;
    public const MONDAY = 1;
    public const TUESDAY = 2;
    public const WEDNESDAY = 3;
    public const THURSDAY = 4;
    public const FRIDAY = 5;
    public const SATURDAY = 6;

    public const MONTH_NAMES_LEAP_YEAR = [
        'Tishri', 'Heshvan', 'Kislev', 'Tevet',
        'Shevat', 'Adar I', 'Adar II', 'Nisan',
        'Iyar', 'Sivan', 'Tammuz', 'Av', 'Elul'
    ];

    public const MONTH_NAMES_NON_LEAP_YEAR = [
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
            return self::MONTH_NAMES_LEAP_YEAR[$jewishMonth - 1];
        else
            return self::MONTH_NAMES_NON_LEAP_YEAR[$jewishMonth - 1];
    }

    /**
     * @return int[] Date in jewish calendar, as [ year, month, day ]
     */
    public static function getJewishDate(\DateTimeInterface $dt): array
    {
        $jdCurrent = gregoriantojd($dt->format('m'), $dt->format('d'), $dt->format('Y'));
        $jewishDate = jdtojewish($jdCurrent);
        list($jewishMonth, $jewishDay, $jewishYear) = explode('/', $jewishDate);

        return [ $jewishYear, $jewishMonth, $jewishDay ];
    }

    public static function getJewishHoliday(
        \DateTimeInterface $dt, bool $isDiaspora, bool $postponeShushanPurimOnSaturday
    ): array
    {
        $result = [];

        $jdCurrent = gregoriantojd($dt->format('m'), $dt->format('d'), $dt->format('Y'));

        list($jewishYear, $jewishMonth, $jewishDay) = self::getJewishDate($dt);

        // Holidays in Elul
        if ($jewishDay == 29 && $jewishMonth == self::ELUL)
            $result[] = "Erev Rosh Hashanah";

        // Holidays in Tishri
        if ($jewishDay == 1 && $jewishMonth == self::TISHRI)
            $result[] = "Rosh Hashanah I";

        if ($jewishDay == 2 && $jewishMonth == self::TISHRI)
            $result[] = "Rosh Hashanah II";

        $jd = jewishtojd(self::TISHRI, 3, $jewishYear);
        $weekdayNo = jddayofweek($jd, CAL_DOW_DAYNO);

        if ($weekdayNo == self::SATURDAY) { // If the 3 Tishri would fall on Saturday ...
            // ... postpone Tzom Gedaliah to Sunday
            if ($jewishDay == 4 && $jewishMonth == self::TISHRI)
                $result[] = "Tzom Gedaliah";
        } else {
            if ($jewishDay == 3 && $jewishMonth == self::TISHRI)
                $result[] = "Tzom Gedaliah";
        }
        if ($jewishDay == 9 && $jewishMonth == self::TISHRI)
            $result[] = "Erev Yom Kippur";
        if ($jewishDay == 10 && $jewishMonth == self::TISHRI)
            $result[] = "Yom Kippur";
        if ($jewishDay == 14 && $jewishMonth == self::TISHRI)
            $result[] = "Erev Sukkot";
        if ($jewishDay == 15 && $jewishMonth == self::TISHRI)
            $result[] = "Sukkot I";
        if ($jewishDay == 16 && $jewishMonth == self::TISHRI && $isDiaspora)
            $result[] = "Sukkot II";

        if ($isDiaspora) {
            if ($jewishDay >= 17 && $jewishDay <= 20 && $jewishMonth == self::TISHRI)
                $result[] = "Hol Hamoed Sukkot";
        } else {
            if ($jewishDay >= 16 && $jewishDay <= 20 && $jewishMonth == self::TISHRI)
                $result[] = "Hol Hamoed Sukkot";
        }

        if ($jewishDay == 21 && $jewishMonth == self::TISHRI)
            $result[] = "Hoshana Rabbah";

        if ($isDiaspora) {
            if ($jewishDay == 22 && $jewishMonth == self::TISHRI)
                $result[] = "Shemini Azeret";
            if ($jewishDay == 23 && $jewishMonth == self::TISHRI)
                $result[] = "Simchat Torah";
            if ($jewishDay == 24 && $jewishMonth == self::TISHRI)
                $result[] = "Isru Chag";
        } else {
            if ($jewishDay == 22 && $jewishMonth == self::TISHRI)
                $result[] = "Shemini Azeret/Simchat Torah";
            if ($jewishDay == 23 && $jewishMonth == self::TISHRI)
                $result[] = "Isru Chag";
        }

        // Holidays in Kislev/Tevet
        $hanukkahStart = jewishtojd(self::KISLEV, 25, $jewishYear);
        $hanukkahNo = (int) ($jdCurrent-$hanukkahStart+1);

        if ($hanukkahNo == 1) $result[] = "Hanukkah I";
        if ($hanukkahNo == 2) $result[] = "Hanukkah II";
        if ($hanukkahNo == 3) $result[] = "Hanukkah III";
        if ($hanukkahNo == 4) $result[] = "Hanukkah IV";
        if ($hanukkahNo == 5) $result[] = "Hanukkah V";
        if ($hanukkahNo == 6) $result[] = "Hanukkah VI";
        if ($hanukkahNo == 7) $result[] = "Hanukkah VII";
        if ($hanukkahNo == 8) $result[] = "Hanukkah VIII";

        // Holidays in Tevet
        $jd = jewishtojd(self::TEVET, 10, $jewishYear);
        $weekdayNo = jddayofweek($jd, CAL_DOW_DAYNO);

        if ($weekdayNo == self::SATURDAY) { // If the 10 Tevet would fall on Saturday ...
            // ... postpone Tzom Tevet to Sunday
            if ($jewishDay == 11 && $jewishMonth == self::TEVET)
                $result[] = "Tzom Tevet";
        } else {
            if ($jewishDay == 10 && $jewishMonth == self::TEVET)
                $result[] = "Tzom Tevet";
        }

        // Holidays in Shevat
        if ($jewishDay == 15 && $jewishMonth == self::SHEVAT)
            $result[] = "Tu B'Shevat";

        // Holidays in Adar I
        if (self::isJewishLeapYear($jewishYear) && $jewishDay == 14 && $jewishMonth == self::ADAR_I)
            $result[] = "Purim Katan";

        if (self::isJewishLeapYear($jewishYear) && $jewishDay == 15 && $jewishMonth == self::ADAR_I)
            $result[] = "Shushan Purim Katan";

        // Holidays in Adar or Adar II
        if (self::isJewishLeapYear($jewishYear))
            $purimMonth = self::ADAR_II;
        else
            $purimMonth = self::ADAR;

        $jd = jewishtojd($purimMonth, 13, $jewishYear);
        $weekdayNo = jddayofweek($jd, CAL_DOW_DAYNO);

        if ($weekdayNo == self::SATURDAY) { // If the 13 Adar or Adar II would fall on Saturday ...
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
            if ($weekdayNo == self::SATURDAY) { // If the 15 Adar or Adar II would fall on Saturday ...
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
        $jd = jewishtojd(self::NISAN, $shabbatHagadolDay, $jewishYear);

        while (jddayofweek($jd, CAL_DOW_DAYNO) != self::SATURDAY) {
            $jd--;
            $shabbatHagadolDay--;
        }

        if ($jewishDay == $shabbatHagadolDay && $jewishMonth == self::NISAN)
            $result[] = "Shabbat Hagadol";
        if ($jewishDay == 14 && $jewishMonth == self::NISAN)
            $result[] = "Erev Pesach";
        if ($jewishDay == 15 && $jewishMonth == self::NISAN)
            $result[] = "Pesach I";
        if ($jewishDay == 16 && $jewishMonth == self::NISAN && $isDiaspora)
            $result[] = "Pesach II";

        if ($isDiaspora) {
            if ($jewishDay >= 17 && $jewishDay <= 20 && $jewishMonth == self::NISAN)
                $result[] = "Hol Hamoed Pesach";
        } else {
            if ($jewishDay >= 16 && $jewishDay <= 20 && $jewishMonth == self::NISAN)
                $result[] = "Hol Hamoed Pesach";
        }

        if ($jewishDay == 21 && $jewishMonth == self::NISAN)
            $result[] = "Pesach VII";

        if ($jewishDay == 22 && $jewishMonth == self::NISAN && $isDiaspora)
            $result[] = "Pesach VIII";

        if ($isDiaspora) {
            if ($jewishDay == 23 && $jewishMonth == self::NISAN)
                $result[] = "Isru Chag";
        } else {
            if ($jewishDay == 22 && $jewishMonth == self::NISAN)
                $result[] = "Isru Chag";
        }

        $jd = jewishtojd(self::NISAN, 27, $jewishYear);
        $weekdayNo = jddayofweek($jd, 0);

        if ($weekdayNo == self::FRIDAY) { // If the 27 Nisan would fall on Friday ...
            // ... then Yom Hashoah falls on Thursday
            if ($jewishDay == 26 && $jewishMonth == self::NISAN)
                $result[] = "Yom Hashoah";
        } else {
            if ($jewishYear >= 5757) { // Since 1997 (5757) ...
                if ($weekdayNo == self::SUNDAY) { // If the 27 Nisan would fall on Friday ...
                    // ... then Yom Hashoah falls on Thursday
                    if ($jewishDay == 28 && $jewishMonth == self::NISAN)
                        $result[] = "Yom Hashoah";
                } else {
                    if ($jewishDay == 27 && $jewishMonth == self::NISAN)
                        $result[] = "Yom Hashoah";
                }
            } else {
                if ($jewishDay == 27 && $jewishMonth == self::NISAN)
                    $result[] = "Yom Hashoah";
            }
        }

        // Holidays in Iyar

        $jd = jewishtojd(self::IYAR, 4, $jewishYear);
        $weekdayNo = jddayofweek($jd, CAL_DOW_DAYNO);

        // If the 4 Iyar would fall on Friday or Thursday ...
        // ... then Yom Hazikaron falls on Wednesday and Yom Ha'Atzmaut on Thursday
        if ($weekdayNo == self::FRIDAY) {
            if ($jewishDay == 2 && $jewishMonth == self::IYAR)
                $result[] = "Yom Hazikaron";
            if ($jewishDay == 3 && $jewishMonth == self::IYAR)
                $result[] = "Yom Ha'Atzmaut";
        } else {
            if ($weekdayNo == self::THURSDAY) {
                if ($jewishDay == 3 && $jewishMonth == self::IYAR)
                    $result[] = "Yom Hazikaron";
                if ($jewishDay == 4 && $jewishMonth == self::IYAR)
                    $result[] = "Yom Ha'Atzmaut";
            } else {
                if ($jewishYear >= 5764) { // Since 2004 (5764) ...
                    if ($weekdayNo == self::SUNDAY) { // If the 4 Iyar would fall on Sunday ...
                        // ... then Yom Hazicaron falls on Monday
                        if ($jewishDay == 5 && $jewishMonth == self::IYAR)
                            $result[] = "Yom Hazikaron";
                        if ($jewishDay == 6 && $jewishMonth == self::IYAR)
                            $result[] = "Yom Ha'Atzmaut";
                    } else {
                        if ($jewishDay == 4 && $jewishMonth == self::IYAR)
                            $result[] = "Yom Hazikaron";
                        if ($jewishDay == 5 && $jewishMonth == self::IYAR)
                            $result[] = "Yom Ha'Atzmaut";
                    }
                } else {
                    if ($jewishDay == 4 && $jewishMonth == self::IYAR)
                        $result[] = "Yom Hazikaron";
                    if ($jewishDay == 5 && $jewishMonth == self::IYAR)
                        $result[] = "Yom Ha'Atzmaut";
                }
            }
        }

        if ($jewishDay == 14 && $jewishMonth == self::IYAR)
            $result[] = "Pesach Sheini";
        if ($jewishDay == 18 && $jewishMonth == self::IYAR)
            $result[] = "Lag B'Omer";
        if ($jewishDay == 28 && $jewishMonth == self::IYAR)
            $result[] = "Yom Yerushalayim";

        // Holidays in Sivan
        if ($jewishDay == 5 && $jewishMonth == self::SIVAN)
            $result[] = "Erev Shavuot";
        if ($jewishDay == 6 && $jewishMonth == self::SIVAN)
            $result[] = "Shavuot I";
        if ($jewishDay == 7 && $jewishMonth == self::SIVAN && $isDiaspora)
            $result[] = "Shavuot II";

        if ($isDiaspora) {
            if ($jewishDay == 8 && $jewishMonth == self::SIVAN)
                $result[] = "Isru Chag";
        } else {
            if ($jewishDay == 7 && $jewishMonth == self::SIVAN)
                $result[] = "Isru Chag";
        }

        // Holidays in Tammuz
        $jd = jewishtojd(self::TAMMUZ, 17, $jewishYear);
        $weekdayNo = jddayofweek($jd, CAL_DOW_DAYNO);

        if ($weekdayNo == self::SATURDAY) { // If the 17 Tammuz would fall on Saturday ...
            // ... postpone Tzom Tammuz to Sunday
            if ($jewishDay == 18 && $jewishMonth == self::TAMMUZ)
                $result[] = "Tzom Tammuz";
        } else {
            if ($jewishDay == 17 && $jewishMonth == self::TAMMUZ)
                $result[] = "Tzom Tammuz";
        }

        // Holidays in Av
        $jd = jewishtojd(self::AV, 9, $jewishYear);
        $weekdayNo = jddayofweek($jd, CAL_DOW_DAYNO);

        if ($weekdayNo == self::SATURDAY) { // If the 9 Av would fall on Saturday ...
            // ... postpone Tisha B'Av to Sunday
            if ($jewishDay == 10 && $jewishMonth == self::AV)
                $result[] = "Tisha B'Av";
        } else {
            if ($jewishDay == 9 && $jewishMonth == self::AV)
                $result[] = "Tisha B'Av";
        }

        if ($jewishDay == 15 && $jewishMonth == self::AV)
            $result[] = "Tu B'Av";

        return $result;
    }
}
