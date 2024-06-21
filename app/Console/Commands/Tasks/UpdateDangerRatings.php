<?php

namespace App\Console\Commands\Tasks;
use App\Models\Constellation, App\Models\Region, App\Models\System, App\Models\Stargate, App\Models\Station;
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
        foreach($missingSystems as $system) {
            echo $system->id . "\n";
        }
    }

    public function execute()
    {
        $this->processSystemKills();
        $this->processSystemJumps();
        $this->processMissingSystems();
        // print_r($this->allSystemData);
    }
}

?>