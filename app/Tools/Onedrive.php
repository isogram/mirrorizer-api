<?php

namespace App\Tools;

use Stevenmaguire\OAuth2\Client\Provider\Microsoft;

use Illuminate\Http\Request;

/**
* Onedrive Class
*/
class Onedrive
{

    protected $appKey;
    protected $appSecret;
    protected $app;
    protected $redirectUri;
    protected $redirectUriLogout;
    protected $credentialsPath;

    const DROPBOX_PATH = '/UPLOADED';

    function __construct()
    {
        $this->appKey = config('mirrorizer.onedrive_app_key');
        $this->appSecret = config('mirrorizer.onedrive_app_secret');
        $this->redirectUri = config('mirrorizer.onedrive_redirect_uri');
        $this->redirectUriLogout = config('mirrorizer.onedrive_redirect_uri_logout');
        $this->credentialsPath = config('mirrorizer.onedrive_credentials_path');
    }

    public function generateCredentials(Request $request)
    {

        $provider = new Microsoft([
            'clientId'          => $this->appKey,
            'clientSecret'      => $this->appSecret,
            'redirectUri'       => $this->redirectUri,
        ]);

        $options = [
            'scope' => array_merge(
                $provider->defaultScopes,
                ['files.readwrite', 'offline_access']
            ),
        ];

        if (!isset($_GET['code'])) {

            // If we don't have an authorization code then get one
            $authUrl = $provider->getAuthorizationUrl($options);

            // dd($authUrl);
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: '. $authUrl);
            exit;

        // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {

            // unset($_SESSION['oauth2state']);
            exit('Invalid state');

        } else {

            // Try to get an access token (using the authorization code grant)
            $token = $provider->getAccessToken('authorization_code', [
                 'code' => $_GET['code']
            ]);

            try {

                file_put_contents($this->credentialsPath, json_encode($token));

            } catch (Exception $e) {

                return $e->getMessage();

            }

            return true;

        }

    }

    public function logout()
    {
        # code...
    }

    // /**
    //  * Upload file to Dropbox
    //  * @param string $fullPath Full path of file
    //  * @param string $fileName Name of file
    //  * @return void
    //  */
    // public function upload($fullPath, $fileName)
    // {
    //     $resp = [
    //         'error' => null,
    //         'data' => null,
    //         'permission' => null,
    //     ];

    //     // set dropbox file
    //     $this->setDropboxFile($fullPath);

    //     try {

    //         // upload to dropbox
    //         $file = $this->dropbox->upload($this->getDropboxFile(), static::DROPBOX_PATH . '/' . $fileName, ['autorename' => true]);
    //         $resp['data'] = $file->getData();

    //     } catch (\Exception $e) {

    //         $resp['error'] = 'Error when uploading to Dropbox';
    //         return $resp;

    //     }

    //     // uploaded file
    //     $uploadedFilePath = $file->path_display;

    //     // set permission to public
    //     $permission = $this->setPermissionToPublic($uploadedFilePath);

    //     if ($permission instanceof \Exception) {
    //         // error here
    //         // decode json string
    //         $errs = json_decode($permission->getMessage(), true);
    //         if ($errs) {
    //             if (isset($errs['error']['.tag']) && $errs['error']['.tag'] == 'shared_link_already_exists') {
    //                 // pass it .. this is not necessary error

    //                 $links = $this->getSharedLinks($uploadedFilePath);

    //                 if ($links instanceof \Exception) {

    //                     $resp['error'] = 'Error when get shared links';
    //                     return $resp;

    //                 } else {

    //                     $resp['permission'] = $links->getDecodedBody()['links'][0];

    //                 }

    //             } else {

    //                 $resp['error'] = 'Error when set permission';
    //                 return $resp;

    //             }
    //         }
    //     } else {

    //         $resp['permission'] = $permission->getDecodedBody();

    //     }


    //     return $resp;
    // }

    // private function setDropboxFile($fullPath)
    // {
    //     $this->dropboxFile = new KannuDropboxFile($fullPath);
    // }

    // private function getDropboxFile()
    // {
    //     return $this->dropboxFile;
    // }

    // private function setPermissionToPublic($filePath)
    // {

    //     try {
    
    //         $response =  $this->dropbox->postToAPI(
    //             "/sharing/create_shared_link_with_settings",
    //             [
    //                 "path" => $filePath,
    //                 "settings" => ['requested_visibility' => 'public']
    //             ]
    //         );
            
    //     } catch (\Exception $e) {

    //         return $e;

    //     }

    //     return $response;
    // }

    // private function getSharedLinks($filePath)
    // {

    //     try {
    
    //         $response =  $this->dropbox->postToAPI(
    //             "/sharing/get_shared_links",
    //             [
    //                 "path" => $filePath
    //             ]
    //         );
            
    //     } catch (\Exception $e) {

    //         return $e;

    //     }

    //     return $response;
    // }
}
