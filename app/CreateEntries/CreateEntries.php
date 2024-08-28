<?php

namespace App\CreateEntries;

use Exception;
use App\Models\Constellation, App\Models\Region, App\Models\System, App\Models\Stargate, App\Models\Station;
use App\Services\Decorators\RetryDecorator;
use App\Helpers\Utility;

class CreateEntries
{
    public function createRegions()
    {
        echo "CREATING REGIONS \n";

        // Get the list of regions
        $server_response = Utility::curlConnectAndGetResponse(config('constants.eveSwaggerUrls')['regions']);

        // Handle individual regions
        $region_list = json_decode($server_response);

        $previous_index = -1;
        foreach($region_list as $index => $region_id){
            if ($index - $previous_index != 1) {
                echo "INDEX MISMATCH, abort function \n";
                Region::truncate();
                break;
            }

            $regionServerResponse = Utility::curlConnectAndGetResponse(config('constants.eveSwaggerUrls')['regions'] . $region_id);

            $regionServerResponse = json_decode($regionServerResponse);

            $regionData = [
                'id' => $regionServerResponse->region_id,
                'name' => $regionServerResponse->name,
                'description' => $regionServerResponse->description,
            ];
            
            $region = Region::firstOrCreate(
                ['id' => $regionServerResponse->region_id],
                $regionData
            );
            
            if ($region->wasRecentlyCreated) {
                echo("Region {$region->id} successfully created! \n");
            } else {
                echo("Region {$region->id} already exists! \n");
            }

            // Maximum 30 requests per second
            if (intval($region_id) % 30 === 0 ) {
                echo "SLEEPING \n";
                sleep(1);
            }
            $previous_index = $index;
        }
        echo "FUNCTION FINISHED";
    }

    public function createConstellations()
    {
        echo "CREATING CONSTELLATIONS \n";

        $server_response = Utility::curlConnectAndGetResponse(config('constants.eveSwaggerUrls')['constellations']);

        // Handle individual constellations
        $constellation_list = json_decode($server_response);

        $previous_index = -1;
        foreach($constellation_list as $index => $constellation_id){
            if ($index - $previous_index != 1) {
                echo "INDEX MISMATCH, abort function \n";
                Constellation::truncate();
                break;
            }

            $constellationServerResponse = Utility::curlConnectAndGetResponse(config('constants.eveSwaggerUrls')['constellations'] . $constellation_id);

            $constellationServerResponse = json_decode($constellationServerResponse);

            $constellationData = [
                'id' => $constellationServerResponse->constellation_id,
                'name' => $constellationServerResponse->name,
                'x' => floatval($constellationServerResponse->position->x),
                'y' => floatval($constellationServerResponse->position->y),
                'z' => floatval($constellationServerResponse->position->z),
                'region_id' => $constellationServerResponse->region_id,
            ];
            
            $constellation = Constellation::firstOrCreate(
                ['id' => $constellationServerResponse->constellation_id],
                $constellationData
            );
            
            if ($constellation->wasRecentlyCreated) {
                echo("Constellation {$constellation->id} successfully created! \n");
            } else {
                echo("Constellation {$constellation->id} already exists! \n");
            }

            // Maximum 30 requests per second
            if (intval($constellation_id) % 30 === 0 ) {
                echo "SLEEPING \n";
                sleep(1);
            }
            $previous_index = $index;
        }
        echo "FUNCTION FINISHED";
    }

    public function createStargateOrStation($entityClass, $entityId, $urlKey)
    {
        $serverResponse = Utility::curlConnectAndGetResponse(config('constants.eveSwaggerUrls')[$urlKey . "s"] . $entityId);
        
        $serveResponse = json_decode($serverResponse);

        $entityData = [
            'id' => $serveResponse->{$urlKey . '_id'},
            'name' => $serveResponse->name,
            'x' => floatval($serveResponse->position->x),
            'y' => floatval($serveResponse->position->y),
            'z' => floatval($serveResponse->position->z),
            'system_id' => $serveResponse->system_id,
        ];

        $entity = $entityClass::firstOrCreate(
            ['id' => $serveResponse->{$urlKey . '_id'}],
            $entityData
        );
        
        $entityClassName = class_basename($entityClass);
        if ($entity->wasRecentlyCreated) {
            echo("===> {$entityClassName} {$entity->id} successfully created!\n");
        } else {
            echo("===> {$entityClassName} {$entity->id} already exists!\n");
        }
    }

    public function createIndividualSystem($system_id)
        {   
            $systemServerResponse = Utility::curlConnectAndGetResponse(config('constants.eveSwaggerUrls')['systems'] . $system_id);
            $systemServerResponse = json_decode($systemServerResponse);

            $systemData = [
                'id' => $systemServerResponse->system_id,
                'name' => $systemServerResponse->name,
                'x' => floatval($systemServerResponse->position->x),
                'y' => floatval($systemServerResponse->position->y),
                'z' => floatval($systemServerResponse->position->z),
                'security_class' => $systemServerResponse->security_class ?? null,
                'security_status' => isset($systemServerResponse->security_status) ? floatval($systemServerResponse->security_status) : null,
                'constellation_id' => $systemServerResponse->constellation_id,
            ];
            
            $system = System::firstOrCreate(
                ['id' => $systemServerResponse->system_id],
                $systemData
            );
            
            if ($system->wasRecentlyCreated) {
                echo("System {$system->id} successfully created! \n");
            } else {
                echo("System {$system->id} already exists! \n");
            }
            return $systemServerResponse;
        }

    public function createSystems()
    {
        echo "CREATING SYSTEMS \n";

        // Get the list of systems
        $server_response = Utility::curlConnectAndGetResponse(config('constants.eveSwaggerUrls')['systems']);

        $system_list = json_decode($server_response);

        $previous_index = -1;
        foreach($system_list as $index => $system_id){
            if ($index - $previous_index != 1) {
                echo "INDEX MISMATCH, abort function \n";
                System::truncate();
                break;
            }
            
            // Handle individual system
            $retryDecorator = new RetryDecorator([$this, 'createIndividualSystem']);
            try {
                $systemServerResponse = $retryDecorator->execute($system_id);
            } catch (Exception $e) {
                echo "Function failed after max retries: " . $e->getMessage() . "\n";
                throw new Exception("Something went wrong when creating a System!");
            }
            
            // Maximum 30 requests per second
            if (intval($system_id) % 30 === 0 ) {
                echo "SLEEPING \n";
                sleep(1);
            }
            $previous_index = $index;
        }
        echo "SYSTEMS CREATED! \n";
    }

    public function createStargates() {
        echo "CREATING STARGATES \n";

        // Get the list of systems
        $server_response = Utility::curlConnectAndGetResponse(config('constants.eveSwaggerUrls')['systems']);

        $system_list = json_decode($server_response);

        $previous_index = -1;
        foreach($system_list as $index => $system_id){
            if ($index - $previous_index != 1) {
                echo "INDEX MISMATCH, abort function \n";
                break;
            }
            
            $systemServerResponse = Utility::curlConnectAndGetResponse(config('constants.eveSwaggerUrls')['systems'] . $system_id);
            $systemServerResponse = json_decode($systemServerResponse);

            // Creating stargates 
            if (isset($systemServerResponse->stargates)) {
                foreach($systemServerResponse->stargates as $stargateId) {
                    $retryDecorator = new RetryDecorator([$this, 'createStargateOrStation']);
                    try {
                        $retryDecorator->execute(Stargate::class, $stargateId, 'stargate');
                    } catch (Exception $e) {
                        echo "Function failed after max retries: " . $e->getMessage() . "\n";
                        throw new Exception("Something went wrong when creating a Stargate!");
                    }
                }
            }
            if (intval($system_id) % 5 === 0 ) {
                echo "SLEEPING \n";
                sleep(1);
            }
            $previous_index = $index;
        }
        echo "STARGATES CREATED! \n";
    }

    
    public function createStations() {
        echo "CREATING STATIONS \n";

        // Get the list of systems
        $server_response = Utility::curlConnectAndGetResponse(config('constants.eveSwaggerUrls')['systems']);

        $system_list = json_decode($server_response);

        $previous_index = -1;
        foreach($system_list as $index => $system_id){
            if ($index - $previous_index != 1) {
                echo "INDEX MISMATCH, abort function \n";
                break;
            }
            
            $systemServerResponse = Utility::curlConnectAndGetResponse(config('constants.eveSwaggerUrls')['systems'] . $system_id);
            $systemServerResponse = json_decode($systemServerResponse);

            // Creating stations
            if (isset($systemServerResponse->stations)) {
                foreach($systemServerResponse->stations as $stationId) {
                    $retryDecorator = new RetryDecorator([$this, 'createStargateOrStation']);
                    try {
                        $retryDecorator->execute(Station::class, $stationId, 'station');
                    } catch (Exception $e) {
                        echo "Function failed after max retries: " . $e->getMessage() . "\n";
                        throw new Exception("Something went wrong when creating a Station!");
                    }
                }
            } 

            if (intval($system_id) % 5 === 0 ) {
                echo "SLEEPING \n";
                sleep(1);
            }
            $previous_index = $index;
        }
        echo "STATIONS CREATED! \n";
    }


    public function createAll() {
        $this->createRegions();
        $this->createConstellations();
        $this->createSystems();
        $this->createStargates();
        $this->createStations();
    }
}

?>
