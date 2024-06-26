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

        // $dangerRatingSubquery = DangerRating::select(DB::raw('SUM(value) as rate_sum'))
        //         ->whereColumn('system_id', 'systems.id')
        //         ->whereBetween('timestamp', [$timeStartingPoint, Carbon::now()])
        //         ->groupBy('system_id');

        $systems = System::where('security_status','>=',$systemSecurityLevels[$security_status][0])
                           ->where('security_status','<=',$systemSecurityLevels[$security_status][1])
                           ->get();
        
        return $systems;
    }
}
