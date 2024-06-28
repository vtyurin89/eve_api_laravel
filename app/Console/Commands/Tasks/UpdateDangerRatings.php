<?php

namespace App\Console\Commands\Tasks;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\System, App\Models\DangerRating;
use App\Helpers\Utility;

class UpdateDangerRatings
{
    /** This class updates database every hour, creating a DangerRating object for every System object. */ 
    
    private $defaultSystemValues = [
        'npc_kills' => 0,
        'pod_kills'=> 0,
        'ship_kills'=> 0,
        'ship_jumps'=> 0,
    ];
    private $allSystemData = [];
    private $newDangerRatingValues = [];

    public function processSystemKills()
    {
        $serverResponse = Utility::curlConnectAndGetResponse(config('constants.eveSwaggerUrls')['system_kills']);
        $systemKills = json_decode($serverResponse);

        foreach($systemKills as $oneSystemData) {
            if (!isset($this->allSystemData[$oneSystemData->system_id])) {
                $this->allSystemData[$oneSystemData->system_id] = [];
            }
            $this->allSystemData[$oneSystemData->system_id]['npc_kills'] = $oneSystemData->npc_kills;
            $this->allSystemData[$oneSystemData->system_id]['pod_kills'] = $oneSystemData->pod_kills;
            $this->allSystemData[$oneSystemData->system_id]['ship_kills'] = $oneSystemData->ship_kills;   
        }
    }
    
    public function processSystemJumps()
    {
        $serverResponse = Utility::curlConnectAndGetResponse(config('constants.eveSwaggerUrls')['system_jumps']);
        $systemJumps = json_decode($serverResponse);


        foreach($systemJumps as $oneSystemData) {
            if (!isset($this->allSystemData[$oneSystemData->system_id])) {
                $this->allSystemData[$oneSystemData->system_id] = [];
            }
            $this->allSystemData[$oneSystemData->system_id]['ship_jumps'] = $oneSystemData->ship_jumps; 
        }
    }

    public function processMissingSystems() {
        // Eve Swagger API only returns info about systems where kills and ship jumps happened.
        // Now we need to add systems where no kills and no ship jumps happened.

        $systemIdsWhereThingsHappened = array_keys($this->allSystemData);
        $missingSystems = System::whereNotIn('id', $systemIdsWhereThingsHappened)->get();
        $missingSystemsArray = $missingSystems->mapWithKeys(function ($system) {
            return [$system->id => $this->defaultSystemValues];
        })->toArray();
        $this->allSystemData = $this->allSystemData + $missingSystemsArray;
    }

    public function calculateRating() {
        $eventRates = config('constants.systemEventRates');

        $this->newDangerRatingValues = array_map(function($systemId, $system) use ($eventRates) {
            $ratingChange = array_reduce(array_keys($system), function($carry, $key) use ($eventRates, $system) {
                return $carry + (isset($system[$key]) ? $eventRates[$key] * $system[$key] : 0);
            }, 0);
            return [
                'system_id' => $systemId,
                'value' => $ratingChange,
                'created_at' => Carbon::now(),
            ];
        }, array_keys($this->allSystemData), $this->allSystemData);
    }

    public function createNewRatingObjects() {
        // Only create new objects if the latest ones were created at least 59 minutes ago.
        $minTimeInMinutes = config('constants.dangerRatingUpdateInMinutes');

        $lastDangerRating = DangerRating::latest()->first();

        if ($lastDangerRating && $lastDangerRating->created_at->diffInMinutes(Carbon::now()) < $minTimeInMinutes) {
            echo "DangerRating objects were created less than 59 minutes ago. Aborting creation.\n";
        } else {
            DB::transaction(function () {
                DangerRating::insert($this->newDangerRatingValues);
            });
        }
    }

    public function execute()
    {
        $this->processSystemKills();
        $this->processSystemJumps();
        $this->processMissingSystems();
        $this->calculateRating();
        $this->createNewRatingObjects();
    }

    public function __invoke()
    {
        $this->execute();
    }
}

?>