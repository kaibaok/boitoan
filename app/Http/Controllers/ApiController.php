<?php 
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Helpers\Lunar;
use App\Helpers\Utils;
use Carbon\Carbon;

class ApiController extends Controller
{
    public function AuspiciousDay(Request $request)
    {
        $params = $request->all();
        $now = !empty($params['date']) ? Carbon::createFromFormat('Y-m-d', $params['date']) : Carbon::now();
        $dueDate = Utils::CalculateDueDate($now->format('Y-m-d'));
        $canChi = Lunar::GetCanChiYear($now->year);
        return response()->json([
            'thien_can_dia_chi' => "{$canChi['can']} {$canChi['chi']}" ,
            'ngay_du_sinh' => $dueDate,
        ]);
    }
}
