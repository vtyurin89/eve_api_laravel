<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\System, App\Models\DangerRating;
use Carbon\Carbon;

class ControllerSafeSystems extends Controller
{
    public function index(Request $request) {
        $systemSecurityLevels = config('constants.systemSecurityLevels');

        $security_status = $request->security_status;
        $security_status = array_key_exists($security_status, $systemSecurityLevels) ? $security_status : 'unspecified';

        $timeStartingPoint = Carbon::now()->subHours(config('constants.DangerRateLifeInHours'));
        $timeNow = Carbon::now();

        $systems = System::query()
                ->whereBetween('security_status', $systemSecurityLevels[$security_status])
                ->select('systems.*', DB::raw('(SELECT SUM(value) 
                                                FROM danger_ratings 
                                                WHERE danger_ratings.system_id = systems.id 
                                                  AND danger_ratings.created_at BETWEEN ? AND ?) as danger_rating'))
                ->addBinding([$timeStartingPoint, $timeNow], 'select')
                ->orderBy('danger_rating')
                ->limit(config('constants.queryResultCutSize'));
        
        $resultJson = $systems->get()->toJson();
        return  $resultJson;
    }
}
