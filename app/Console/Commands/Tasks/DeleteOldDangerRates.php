<?php

namespace App\Console\Commands\Tasks;
use Carbon\Carbon;
use App\Models\Constellation, App\Models\Region, App\Models\System, App\Models\Stargate, App\Models\Station, App\Models\DangerRating;

use function PHPUnit\Framework\isNull;

class DeleteOldDangerRates
{
    private $timeNow;
    private $timeStartingPoint;
    private $DangerRateLifeInHours;

    public function __construct()
    {   
        $this->DangerRateLifeInHours  = config('constants.DangerRateLifeInHours');
        $this->timeNow = Carbon::now();
        $this->timeStartingPoint = Carbon::now()->subHours($this->DangerRateLifeInHours);
    }

    public function countOutdatedDangerRatingObjects()
    {
        $count = DangerRating::whereNotBetween('created_at', [$this->timeStartingPoint, $this->timeNow])->count();
        return $count;
    }

    public function deleteOutdatedDangerRatingObjects() {
        DangerRating::whereNotBetween('created_at', [$this->timeStartingPoint,  $this->timeNow])->delete();
    }

    public function execute () {
        $deletedCount = $this->countOutdatedDangerRatingObjects();
        $outdatedObjects = $this->deleteOutdatedDangerRatingObjects();
        $msg = $deletedCount != 0 ? "$deletedCount outdated DangerRating objects deleted successfully.\n" : "No DangerRating objects deleted.\n";
        echo $msg;
    }
}



?>