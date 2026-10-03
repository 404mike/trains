<?php

class GetTrainData {

    const string api = 'https://map-api.production.signalbox.io//api/locations';

    public function __construct()
    {
        $this->getData();
    }

    private function getData()
    {
        $timeNow = date('Y-m-d H:i:s');
        $endTime = date('Y-m-d H:i:s', strtotime('1 hour'));
        while($timeNow <= $endTime) {
            echo "Getting data at $timeNow. Reponse ";
            $this->apiCall();
        }

    }

    private function apiCall()
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => self::api,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,

            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'User-Agent: Mike/1.0',
            ],
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);

            throw new \RuntimeException(
                'API request failed: ' . $error
            );
        }

        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        echo $httpCode . "\n";

        curl_close($curl);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new \RuntimeException(
                sprintf('API returned HTTP %d: %s', $httpCode, $response)
            );
        }

        $this->saveResponse($response);
        sleep(20);
    }

    private function saveResponse(string $json): bool
    {
        try{
            file_put_contents("../data/".time().".json", $json);
            return true;
        }catch(Exception $e){
            echo 'Caught exception: ',  $e->getMessage(), "\n";
            return false;
        }
    }
}

new GetTrainData();