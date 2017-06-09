<?php

namespace App\Tools;

use Stevenmaguire\OAuth2\Client\Provider\Microsoft;

/**
* Onedrive Class
*/
class Onedrive
{

    protected $appKey;
    protected $appSecret;
    protected $app;
    protected $redirectUri;

    const DROPBOX_PATH = '/UPLOADED';

    function __construct()
    {
        $this->appKey = config('mirrorizer.onedrive_app_key');
        $this->appSecret = config('mirrorizer.onedrive_app_secret');
        $this->redirectUri = config('mirrorizer.onedrive_redirect_uri');
    }

    public function generateCredentials()
    {

        $provider = new Microsoft([
            'clientId'          => $this->appKey,
            'clientSecret'      => $this->appSecret,
            'redirectUri'       => $this->redirectUri
        ]);

        if (!isset($_GET['code'])) {

            // If we don't have an authorization code then get one
            $authUrl = $provider->getAuthorizationUrl();
            // dd($authUrl);
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: '.$authUrl);
            exit;

        // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

            unset($_SESSION['oauth2state']);
            exit('Invalid state');

        } else {

            // Try to get an access token (using the authorization code grant)
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);

            // Optional: Now you have a token you can look up a users profile data
            try {

                // We got an access token, let's now get the user's details
                $user = $provider->getResourceOwner($token);

                // Use these details to create a new profile
                printf('Hello %s!', $user->getFirstname());

            } catch (Exception $e) {

                // Failed to get user details
                exit('Oh dear...');
            }

            // Use this to interact with an API on the users behalf
            echo $token->getToken();
        }

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