<html>

<body>

    <?php

    function INT($d)
    {
        return floor($d);
    }

    function jdFromDate($dd, $mm, $yy)
    {
        $a = INT((14 - $mm) / 12);
        $y = $yy + 4800 - $a;
        $m = $mm + 12 * $a - 3;
        $jd = $dd + INT((153 * $m + 2) / 5) + 365 * $y + INT($y / 4) - INT($y / 100) + INT($y / 400) - 32045;
        if ($jd < 2299161) {
            $jd = $dd + INT((153 * $m + 2) / 5) + 365 * $y + INT($y / 4) - 32083;
        }
        return $jd;
    }

    function jdToDate($jd)
    {
        if ($jd > 2299160) { // After 5/10/1582, Gregorian calendar
            $a = $jd + 32044;
            $b = INT((4 * $a + 3) / 146097);
            $c = $a - INT(($b * 146097) / 4);
        } else {
            $b = 0;
            $c = $jd + 32082;
        }
        $d = INT((4 * $c + 3) / 1461);
        $e = $c - INT((1461 * $d) / 4);
        $m = INT((5 * $e + 2) / 153);
        $day = $e - INT((153 * $m + 2) / 5) + 1;
        $month = $m + 3 - 12 * INT($m / 10);
        $year = $b * 100 + $d - 4800 + INT($m / 10);
        //echo "day = $day, month = $month, year = $year\n";
        return array($day, $month, $year);
    }

    function getNewMoonDay($k, $timeZone)
    {
        $T = $k / 1236.85; // Time in Julian centuries from 1900 January 0.5
        $T2 = $T * $T;
        $T3 = $T2 * $T;
        $dr = M_PI / 180;
        $Jd1 = 2415020.75933 + 29.53058868 * $k + 0.0001178 * $T2 - 0.000000155 * $T3;
        $Jd1 = $Jd1 + 0.00033 * sin((166.56 + 132.87 * $T - 0.009173 * $T2) * $dr); // Mean new moon
        $M = 359.2242 + 29.10535608 * $k - 0.0000333 * $T2 - 0.00000347 * $T3; // Sun's mean anomaly
        $Mpr = 306.0253 + 385.81691806 * $k + 0.0107306 * $T2 + 0.00001236 * $T3; // Moon's mean anomaly
        $F = 21.2964 + 390.67050646 * $k - 0.0016528 * $T2 - 0.00000239 * $T3; // Moon's argument of latitude
        $C1 = (0.1734 - 0.000393 * $T) * sin($M * $dr) + 0.0021 * sin(2 * $dr * $M);
        $C1 = $C1 - 0.4068 * sin($Mpr * $dr) + 0.0161 * sin($dr * 2 * $Mpr);
        $C1 = $C1 - 0.0004 * sin($dr * 3 * $Mpr);
        $C1 = $C1 + 0.0104 * sin($dr * 2 * $F) - 0.0051 * sin($dr * ($M + $Mpr));
        $C1 = $C1 - 0.0074 * sin($dr * ($M - $Mpr)) + 0.0004 * sin($dr * (2 * $F + $M));
        $C1 = $C1 - 0.0004 * sin($dr * (2 * $F - $M)) - 0.0006 * sin($dr * (2 * $F + $Mpr));
        $C1 = $C1 + 0.0010 * sin($dr * (2 * $F - $Mpr)) + 0.0005 * sin($dr * (2 * $Mpr + $M));
        if ($T < -11) {
            $deltat = 0.001 + 0.000839 * $T + 0.0002261 * $T2 - 0.00000845 * $T3 - 0.000000081 * $T * $T3;
        } else {
            $deltat = -0.000278 + 0.000265 * $T + 0.000262 * $T2;
        };
        $JdNew = $Jd1 + $C1 - $deltat;
        //echo "JdNew = $JdNew\n";
        return INT($JdNew + 0.5 + $timeZone / 24);
    }

    function getSunLongitude($jdn, $timeZone)
    {
        $T = ($jdn - 2451545.5 - $timeZone / 24) / 36525; // Time in Julian centuries from 2000-01-01 12:00:00 GMT
        $T2 = $T * $T;
        $dr = M_PI / 180; // degree to radian
        $M = 357.52910 + 35999.05030 * $T - 0.0001559 * $T2 - 0.00000048 * $T * $T2; // mean anomaly, degree
        $L0 = 280.46645 + 36000.76983 * $T + 0.0003032 * $T2; // mean longitude, degree
        $DL = (1.914600 - 0.004817 * $T - 0.000014 * $T2) * sin($dr * $M);
        $DL = $DL + (0.019993 - 0.000101 * $T) * sin($dr * 2 * $M) + 0.000290 * sin($dr * 3 * $M);
        $L = $L0 + $DL; // true longitude, degree
        //echo "\ndr = $dr, M = $M, T = $T, DL = $DL, L = $L, L0 = $L0\n";
        // obtain apparent longitude by correcting for nutation and aberration
        $omega = 125.04 - 1934.136 * $T;
        $L = $L - 0.00569 - 0.00478 * sin($omega * $dr);
        $L = $L * $dr;
        $L = $L - M_PI * 2 * (INT($L / (M_PI * 2))); // Normalize to (0, 2*PI)
        return INT($L / M_PI * 6);
    }

    function getLunarMonth11($yy, $timeZone)
    {
        $off = jdFromDate(31, 12, $yy) - 2415021;
        $k = INT($off / 29.530588853);
        $nm = getNewMoonDay($k, $timeZone);
        $sunLong = getSunLongitude($nm, $timeZone); // sun longitude at local midnight
        if ($sunLong >= 9) {
            $nm = getNewMoonDay($k - 1, $timeZone);
        }
        return $nm;
    }

    function getLeapMonthOffset($a11, $timeZone)
    {
        $k = INT(($a11 - 2415021.076998695) / 29.530588853 + 0.5);
        $last = 0;
        $i = 1; // We start with the month following lunar month 11
        $arc = getSunLongitude(getNewMoonDay($k + $i, $timeZone), $timeZone);
        do {
            $last = $arc;
            $i = $i + 1;
            $arc = getSunLongitude(getNewMoonDay($k + $i, $timeZone), $timeZone);
        } while ($arc != $last && $i < 14);
        return $i - 1;
    }

    /* Comvert solar date dd/mm/yyyy to the corresponding lunar date */
    function convertSolar2Lunar($dd, $mm, $yy, $timeZone)
    {
        $dayNumber = jdFromDate($dd, $mm, $yy);
        $k = INT(($dayNumber - 2415021.076998695) / 29.530588853);
        $monthStart = getNewMoonDay($k + 1, $timeZone);
        if ($monthStart > $dayNumber) {
            $monthStart = getNewMoonDay($k, $timeZone);
        }
        $a11 = getLunarMonth11($yy, $timeZone);
        $b11 = $a11;
        if ($a11 >= $monthStart) {
            $lunarYear = $yy;
            $a11 = getLunarMonth11($yy - 1, $timeZone);
        } else {
            $lunarYear = $yy + 1;
            $b11 = getLunarMonth11($yy + 1, $timeZone);
        }
        $lunarDay = $dayNumber - $monthStart + 1;
        $diff = INT(($monthStart - $a11) / 29);
        $lunarLeap = 0;
        $lunarMonth = $diff + 11;
        if ($b11 - $a11 > 365) {
            $leapMonthDiff = getLeapMonthOffset($a11, $timeZone);
            if ($diff >= $leapMonthDiff) {
                $lunarMonth = $diff + 10;
                if ($diff == $leapMonthDiff) {
                    $lunarLeap = 1;
                }
            }
        }
        if ($lunarMonth > 12) {
            $lunarMonth = $lunarMonth - 12;
        }
        if ($lunarMonth >= 11 && $diff < 4) {
            $lunarYear -= 1;
        }
        return array($lunarDay, $lunarMonth, $lunarYear, $lunarLeap);
    }

    /* Convert a lunar date to the corresponding solar date */
    function convertLunar2Solar($lunarDay, $lunarMonth, $lunarYear, $lunarLeap, $timeZone)
    {
        if ($lunarMonth < 11) {
            $a11 = getLunarMonth11($lunarYear - 1, $timeZone);
            $b11 = getLunarMonth11($lunarYear, $timeZone);
        } else {
            $a11 = getLunarMonth11($lunarYear, $timeZone);
            $b11 = getLunarMonth11($lunarYear + 1, $timeZone);
        }
        $k = INT(0.5 + ($a11 - 2415021.076998695) / 29.530588853);
        $off = $lunarMonth - 11;
        if ($off < 0) {
            $off += 12;
        }
        if ($b11 - $a11 > 365) {
            $leapOff = getLeapMonthOffset($a11, $timeZone);
            $leapMonth = $leapOff - 2;
            if ($leapMonth < 0) {
                $leapMonth += 12;
            }
            if ($lunarLeap != 0 && $lunarMonth != $leapMonth) {
                return array(0, 0, 0);
            } else if ($lunarLeap != 0 || $off >= $leapOff) {
                $off += 1;
            }
        }
        $monthStart = getNewMoonDay($k + $off, $timeZone);
        return jdToDate($monthStart + $lunarDay - 1);
    }
    ?>

    <h3>&#272;&#7893;i ng&#224;y d&#432;&#417;ng ra ng&#224;y &#226;m</h3>

    <?php
    $date_array = getdate();
    $dd = $_REQUEST['dd'];
    $mm = $_REQUEST['mm'];
    $yy = $_REQUEST['yy'];
    if ($dd == 0) $dd = $date_array['mday'];
    if ($mm == 0) $mm = $date_array['mon'];
    if ($yy == 0) $yy = $date_array['year'];
    $al = convertSolar2Lunar($dd, $mm, $yy, 7.0);

    echo "<p><form action=\"\" method=\"POST\">\n";
    echo "Ng&#224;y: <input name=\"dd\" size=2 value=\"$dd\">\n";
    echo "Th&#225;ng: <input name=\"mm\" size=2 value=\"$mm\">\n";
    echo "N&#259;m: <input name=\"yy\" size=4 value=\"$yy\">\n";
    echo "<input type=\"submit\">\n";
    echo "</form>\n";

    echo "D&#432;&#417;ng l&#7883;ch: $dd/$mm/$yy => ";
    $s = "&#194;m l&#7883;ch: $al[0]/$al[1]/$al[2]";
    if ($al[3] == 1) $s = $s . " nhu&#7853;n";
    echo "$s\n";
    ?>
</body>

</html>