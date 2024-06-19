<?php

namespace App\CreateEntries;

use App\Models\Constellation;

class CreateEntries
{
    public $eveSwaggerUrls = [
        'systems' => "https://esi.evetech.net/dev/universe/systems/",
        'stars' => "https://esi.evetech.net/dev/universe/stars/",
        'regions' => "https://esi.evetech.net/dev/universe/regions/",
        'constellations' => "https://esi.evetech.net/dev/universe/constellations/",
    ];

    public function createConstellations()
    {
        echo "STARTING FUNCTION \n";

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
}

?>
