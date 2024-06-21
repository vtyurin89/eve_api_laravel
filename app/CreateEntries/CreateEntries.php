<?php

namespace App\CreateEntries;

use Exception;
use App\Models\Constellation, App\Models\Region, App\Models\System, App\Models\Stargate, App\Models\Station;
use App\Services\Decorators\RetryDecorator;

class CreateEntries
{
    public $eveSwaggerUrls = [
        'systems' => "https://esi.evetech.net/dev/universe/systems/",
        'stars' => "https://esi.evetech.net/dev/universe/stars/",
        'regions' => "https://esi.evetech.net/dev/universe/regions/",
        'constellations' => "https://esi.evetech.net/dev/universe/constellations/",
        'stargates' => "https://esi.evetech.net/dev/universe/stargates/",
        'stations'=> "https://esi.evetech.net/dev/universe/stations/",
    ];

    public function createRegions()
    {
        echo "CREATING REGIONS \n";

        // Get the list of regions
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->eveSwaggerUrls['regions']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        
        $server_response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'CURL error: ' . curl_error($ch) . "\n";
        } else {
            echo "CURL retrieved region list successfully! \n";
        }

        curl_close($ch);

        // Handle individual regions
        $region_list = json_decode($server_response);

        $previous_index = -1;
        foreach($region_list as $index => $region_id){
            if ($index - $previous_index != 1) {
                echo "INDEX MISMATCH, abort function \n";
                Region::truncate();
                break;
            }

            $curlRegionSession = curl_init();
            curl_setopt($curlRegionSession, CURLOPT_URL, $this->eveSwaggerUrls['regions'] . $region_id);
            curl_setopt($curlRegionSession, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlRegionSession, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($curlRegionSession, CURLOPT_SSL_VERIFYHOST, false); 

            $regionServerResponse = curl_exec($curlRegionSession);
            curl_close($curlRegionSession);
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

        // Get the list of constellations
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->eveSwaggerUrls['constellations']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        
        $server_response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'CURL error: ' . curl_error($ch) . "\n";
        } else {
            echo "CURL retrieved constellation list successfully! \n";
        }

        curl_close($ch);

        // Handle individual constellations
        $constellation_list = json_decode($server_response);

        $previous_index = -1;
        foreach($constellation_list as $index => $constellation_id){
            if ($index - $previous_index != 1) {
                echo "INDEX MISMATCH, abort function \n";
                Constellation::truncate();
                break;
            }

            $curlConstellationSession = curl_init();
            curl_setopt($curlConstellationSession, CURLOPT_URL, $this->eveSwaggerUrls['constellations'] . $constellation_id);
            curl_setopt($curlConstellationSession, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlConstellationSession, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($curlConstellationSession, CURLOPT_SSL_VERIFYHOST, false); 

            $constellationServerResponse = curl_exec($curlConstellationSession);
            curl_close($curlConstellationSession);
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
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->eveSwaggerUrls[$urlKey . "s"] . $entityId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        
        $serveResponse = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'CURL error: ' . curl_error($ch) . "\n";
            return;
        } 

        curl_close($ch);
        $serveResponse = json_decode($serveResponse);

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
            $curlSystemSession = curl_init();
            curl_setopt($curlSystemSession, CURLOPT_URL, $this->eveSwaggerUrls['systems'] . $system_id);
            curl_setopt($curlSystemSession, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlSystemSession, CURLOPT_SSL_VERIFYPEER, false); 
            curl_setopt($curlSystemSession, CURLOPT_SSL_VERIFYHOST, false); 

            $systemServerResponse = curl_exec($curlSystemSession);
            curl_close($curlSystemSession);
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

    public function createSystemsStargatesStations()
    {
        echo "CREATING SYSTEMS, STARGATES AND STATIONS \n";

        // Get the list of systems
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->eveSwaggerUrls['systems']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        
        $server_response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            echo 'CURL error: ' . curl_error($ch) . "\n";
        } else if ($http_code >= 500) {
            echo "Server error: HTTP code $http_code. Exiting...\n";
            curl_close($ch);
            exit(1);
        } else {
            echo "CURL retrieved system list successfully! \n";
        }

        curl_close($ch);

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
            
            // Maximum 30 requests per second
            if (intval($system_id) % 30 === 0 ) {
                echo "SLEEPING \n";
                sleep(1);
            }
            $previous_index = $index;
        }
        echo "FUNCTION FINISHED";
    }

    public function createAll() {
        $this->createRegions();
        $this->createConstellations();
        $this->createSystemsStargatesStations();
    }
}

?>
