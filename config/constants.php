<?php

return [
    'eveSwaggerUrls' => [
        'systems' => "https://esi.evetech.net/dev/universe/systems/",
        'stars' => "https://esi.evetech.net/dev/universe/stars/",
        'regions' => "https://esi.evetech.net/dev/universe/regions/",
        'constellations' => "https://esi.evetech.net/dev/universe/constellations/",
        'stargates' => "https://esi.evetech.net/dev/universe/stargates/",
        'stations'=> "https://esi.evetech.net/dev/universe/stations/",
        'system_kills'=> "https://esi.evetech.net/dev/universe/system_kills/",
        'system_jumps'=> "https://esi.evetech.net/dev/universe/system_jumps/"
    ],

    'systemEventRates' => [
        'ship_jumps'=> 1,
        'ship_kills'=> 200,
        'pod_kills'=> 200,
        'npc_kills'=> 1,
    ],

    'systemSecurityLevels' => [
        'high-sec'=> [0.45, 1],
        'low-sec'=> [0.045, 0.44999999999999],
        'null-sec'=> [-0.94999999999999, 0.044999999999999],
        'wormhole'=> [-1, -0.95],
        'unspecified'=> [-1, 1],
    ],

    'dangerRatingUpdateInMinutes' => 59,

    'DangerRateLifeInHours' =>168,
];

?>