<?php

namespace App\Helpers;

use Carbon\Carbon;

class Utils
{
    /**
     * Format a date to a specific format.
     *
     * @param string $date
     * @param string $format
     * @return string
     */
    public static function ConvertStringToDate($date, $format = 'Y-m-d')
    {
        return Carbon::parse($date)->format($format);
    }

    public static function CalculateDueDate($currentDate)
    {
        // Tạo đối tượng Carbon từ ngày hiện tại
        $date = Carbon::createFromFormat('Y-m-d', $currentDate);

        // Cộng thêm 7 ngày
        $date->addDays(7);

        // Cộng thêm 9 tháng
        $date->addMonths(9);

        // Trả về ngày dự sinh dưới dạng dd/mm/yyyy
        return $date->format('d/m/Y');
    }


}

