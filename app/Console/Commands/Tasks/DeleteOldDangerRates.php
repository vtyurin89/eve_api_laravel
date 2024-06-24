<?php

namespace App\Console\Commands\Tasks;
use Carbon\Carbon;
use App\Models\Constellation, App\Models\Region, App\Models\System, App\Models\Stargate, App\Models\Station, App\Models\DangerRating;

class DeleteOldDangerRates
{
    private $timeNow;
    private $timeStartingPoint;
    private $DangerRateLifeInHours;

    public function __construct()
    {   
        $DangerRateLifeInHours  = config('constants.DangerRateLifeInHours');
        $this->timeNow = Carbon::now();
        $this->timeStartingPoint = Carbon::now()->subHours($DangerRateLifeInHours);
    }

    public function getOutdatedDangerRatingObjects() {
        $outdatedObjects = DangerRating::whereNotBetween('timestamp', [$this->timeStartingPoint,  $this->timeNow])->get();
        echo count($outdatedObjects) . "\n";
        echo $this->timeStartingPoint . "\n";
        echo $this->timeNow . "\n";

        return $outdatedObjects;
    }

    public function execute () {
        $this->getOutdatedDangerRatingObjects();
    }
}



?>