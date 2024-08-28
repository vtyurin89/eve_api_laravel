<?php
namespace App\Helpers;

class Utility
{
    public static function curlConnectAndGetResponse($url)
    {
        $max_attempts = 10;  
        $attempt = 0;        

        while ($attempt < $max_attempts) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $server_response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                echo 'CURL error: ' . curl_error($ch) . "\n";
                curl_close($ch);
                exit(1);
            }

            curl_close($ch);

            if ($http_code >= 500) {
                $attempt++;
                echo "Server error: HTTP code $http_code. Retrying attempt $attempt of $max_attempts...\n";
                sleep(1);  
            } else {
                return $server_response; 
            }
        }

        echo "Failed to connect after $max_attempts attempts. Exiting...\n";
        exit(1); 
    }


}
?>