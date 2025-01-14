<?php

namespace App\Helpers;

class Lunar
{
    public const LIST_CAN = ["Canh", "Tân", "Nhâm", "Quý", "Giáp", "Ất", "Bính", "Đinh", "Mậu", "Kỉ"];
    public const LIST_CHI = ["Thân", "Dậu", "Tuất", "Hợi", "Tý", "Sửu", "Dần", "Mẹo", "Thìn", "Tị", "Ngọ", "Mùi"];

    public static function GetCanChiYear(int $year) {
        $can = self::LIST_CAN[$year % 10];
        $chi = self::LIST_CHI[$year % 12];
        return ["can" => $can, "chi" => $chi];
    }
}
