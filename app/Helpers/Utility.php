<?php
namespace App\Helpers;

class Utility
{
    public static function curlConnectAndGetResponse($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); 
        
        $server_response = curl_exec($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            echo 'CURL error: ' . curl_error($ch) . "\n";
            exit(1);
        } else if ($http_code >= 500) {
            echo "Server error: HTTP code $http_code. Exiting...\n";
            curl_close($ch);
            exit(1);
        }

        curl_close($ch);
        return $server_response;
    }


}
?>