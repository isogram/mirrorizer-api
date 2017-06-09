<?php

namespace App\Tools;

use GuzzleHttp\Client;

/**
* Openload Uploader
*/

class OpenloadUploader
{
    const OPENLOAD_HOST         = 'https://api.openload.co';
    const OPENLOAD_USER         = '24ad2f0339be3301';
    const OPENLOAD_PASS         = '-thTvIen';
    const OPENLOAD_FOLDER_ID    = '1889465';

    protected $client;
    protected $file;
    protected $fileName;

    function __construct($file, $fileName)
    {
        $this->file = $file;
        $this->fileName = $fileName;
        $this->client = new Client();
    }

    private function getUploadURL()
    {
        $rawResponse = $this->client->request('GET', self::OPENLOAD_HOST . '/1/file/ul', [
            'query' => [
                'login'     => self::OPENLOAD_USER,
                'key'       => self::OPENLOAD_PASS,
                'folder'    => self::OPENLOAD_FOLDER_ID,
            ]
        ]);

        $response = json_decode((string) $rawResponse->getBody());

        // OK
        if ($response->status == 200) {
            return $response->result->url;
        }

        return false;
    }

    public function upload()
    {
        $uploadTo = $this->getUploadURL();

        if ($uploadTo) {
            
            $rawResponse = $this->client->request('POST', $uploadTo, [
                'multipart' => [
                    [
                        'name'     => $this->fileName,
                        'contents' => fopen($this->file, 'r')
                    ]
                ],
            ]);

            $response = json_decode((string) $rawResponse->getBody());

            return $response;

        }

        return false;

    }
}