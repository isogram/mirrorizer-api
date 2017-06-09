<?php

namespace App\Tools;

/**
* Mirrorizer Class
*/

class Mirrorizer
{

    /**
     * You can remove service you want to take out
     * 
     * @var array contains active hosts
     */
    private $hosts = ['tusfiles', 'openload', 'google_drive', 'uppit'];

    public function __construct($uploadId)
    {
        $this->isExist($file);
    }

    public function isExist($file)
    {
        if (!is_file($file)) {
            throw new \Exception("File doest not exist at {$file}");
        }
    }

    public function getActiveHosts()
    {
        return $this->hosts();
    }

    public function start()
    {
        $hosts = $this->getActiveHosts();

        foreach ($hosts as $host) {

            // handle tusfiles
            if($host == 'tusfiles') {    

            }

        }

    }


}