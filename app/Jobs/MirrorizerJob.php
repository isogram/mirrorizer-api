<?php

namespace App\Jobs;

use App\Models\Upload;

use App\Tools\GoogleDrive;
use App\Tools\Dropbox;
use App\Tools\Onedrive;

class MirrorizerJob extends Job
{

    protected $upload;
    protected $type;
    protected $uniqueId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Upload $upload, $type)
    {
        $arrType = ['GDRIVE', 'ONEDRIVE', 'DROPBOX'];
        $type = trim(strtoupper($type));

        if (!isset($type) || empty($type) || !in_array($type, $arrType))
            throw new \Exception("Needs type request (GDRIVE, ONEDRIVE, DROPBOX)");

        $this->upload = $upload;
        $this->type = $type;
        $this->uniqueId = str_random();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $this->ecoStart();
        $this->eco("upload_id : " . $this->upload->id);
        $this->eco("provider : $this->type");

        // get member
        $member = $this->upload->member;

        // set provider
        switch ($this->type) {
            case 'GDRIVE':
                $provider = new GoogleDrive;
                break;

            case 'ONEDRIVE':
                $provider = new Onedrive;
                break;

            case 'DROPBOX':
                $provider = new Dropbox;
                break;

            default:
                throw new \Exception("Unknown type");
                break;
        }

        // links
        $links = $this->upload->links;
        $link = $links->where('vendor', $this->type)->first();

        $this->eco("processing file");

        // set status to processing
        $link->status = 'PROCESSING';

        try {

            $link->save();

        } catch (\Exception $e) {

            $this->eco("error when updating status link [1]");
            $this->ecoEnd();

            return $e->getMessage();

        }

        $this->eco("getting file to upload");

        // set destination path
        $storage = config('mirrorizer.full_upload_path');
        $uploadPath = config('mirrorizer.upload_directory');
        $userDestinationPath = $member->username . '/' . date('Y/m/d');
        $destinationPath = $storage . '/' . $userDestinationPath;

        $fileName = $this->upload->str_id . '_' . $this->upload->name;
        $file = $destinationPath . '/' . $fileName;

        // upload process
        $this->eco("uploading file");

        $jsonResponse = $provider->upload($file, $fileName);

        $link->json_response = json_encode($jsonResponse);

        if ($jsonResponse['error']) {

            $this->eco("upload error");

            $link->status = 'FAILED';

        } else {

            $this->eco("upload success");

            $link->status = 'SUCCESS';

            switch ($this->type) {
                case 'GDRIVE':
                    $url = "https://drive.google.com/uc?id=" . $jsonResponse['data']['id'];
                    break;

                case 'ONEDRIVE':
                    $url = $jsonResponse['permission']->link->webUrl;
                    break;

                case 'DROPBOX':
                    $url = $jsonResponse['permission']['url'];
                    break;

                default:
                    throw new \Exception("Unknown type");
                    break;
            }

            $link->url = $url;


        }

        try {

            $link->save();

        } catch (\Exception $e) {

            $this->eco("error when updating status link [2]");
            $this->ecoEnd();

            return $e->getMessage();

        }

        $this->ecoEnd();

    }


    protected function eco($string = '', $newline = true)
    {
        $str = sprintf("[%s] %s", $this->uniqueId, $string);

        if ($newline)
            $str .= "\n";

        echo $str;
    }

    protected function ecoStart()
    {
        $this->eco( str_pad(" start ", 50, "=", STR_PAD_BOTH) );
    }

    protected function ecoEnd()
    {
        $this->eco( str_pad(" end ", 50, "=", STR_PAD_BOTH) );
    }

}
