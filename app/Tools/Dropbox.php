<?php

namespace App\Tools;

use Kunnu\Dropbox\Dropbox as KunnuDropbox;
use Kunnu\Dropbox\DropboxApp as KunnuDropboxApp;
use Kunnu\Dropbox\DropboxFile as KannuDropboxFile;

/**
* Dropbox Class
*/
class Dropbox
{

    protected $appKey;
    protected $appSecret;
    protected $accessToken;
    protected $app;
    protected $dropbox;
    protected $dropboxFile;

    const DROPBOX_PATH = '/UPLOADED';

    function __construct()
    {
        $this->appKey = config('mirrorizer.dropbox_app_key');
        $this->appSecret = config('mirrorizer.dropbox_app_secret');
        $this->accessToken = config('mirrorizer.dropbox_access_token');

        //Configure Dropbox Application
        $this->app = new KunnuDropboxApp($this->appKey, $this->appSecret, $this->accessToken);

        //Configure Dropbox service
        $this->dropbox = new KunnuDropbox($this->app);
    }

    public function generateCredentials()
    {

        # code

    }

    /**
     * Upload file to Dropbox
     * @param string $fullPath Full path of file
     * @param string $fileName Name of file
     * @return void
     */
    public function upload($fullPath, $fileName)
    {
        $resp = [
            'error' => null,
            'data' => null,
            'permission' => null,
        ];

        // set dropbox file
        $this->setDropboxFile($fullPath);

        try {

            // upload to dropbox
            $file = $this->dropbox->upload($this->getDropboxFile(), static::DROPBOX_PATH . '/' . $fileName, ['autorename' => true]);
            $resp['data'] = $file->getData();

        } catch (\Exception $e) {

            $resp['error'] = 'Error when uploading to Dropbox';
            return $resp;

        }

        // uploaded file
        $uploadedFilePath = $file->path_display;

        // set permission to public
        $permission = $this->setPermissionToPublic($uploadedFilePath);

        if ($permission instanceof \Exception) {
            // error here
            // decode json string
            $errs = json_decode($permission->getMessage(), true);
            if ($errs) {
                if (isset($errs['error']['.tag']) && $errs['error']['.tag'] == 'shared_link_already_exists') {
                    // pass it .. this is not necessary error

                    $links = $this->getSharedLinks($uploadedFilePath);

                    if ($links instanceof \Exception) {

                        $resp['error'] = 'Error when get shared links';
                        return $resp;

                    } else {

                        $resp['permission'] = $links->getDecodedBody()['links'][0];

                    }

                } else {

                    $resp['error'] = 'Error when set permission';
                    return $resp;

                }
            }
        } else {

            $resp['permission'] = $permission->getDecodedBody();

        }


        return $resp;
    }

    private function setDropboxFile($fullPath)
    {
        $this->dropboxFile = new KannuDropboxFile($fullPath);
    }

    private function getDropboxFile()
    {
        return $this->dropboxFile;
    }

    private function setPermissionToPublic($filePath)
    {

        try {
    
            $response =  $this->dropbox->postToAPI(
                "/sharing/create_shared_link_with_settings",
                [
                    "path" => $filePath,
                    "settings" => ['requested_visibility' => 'public']
                ]
            );
            
        } catch (\Exception $e) {

            return $e;

        }

        return $response;
    }

    private function getSharedLinks($filePath)
    {

        try {
    
            $response =  $this->dropbox->postToAPI(
                "/sharing/get_shared_links",
                [
                    "path" => $filePath
                ]
            );
            
        } catch (\Exception $e) {

            return $e;

        }

        return $response;
    }
}