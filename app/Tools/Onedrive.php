<?php

namespace App\Tools;

use Stevenmaguire\OAuth2\Client\Provider\Microsoft;

use Kunnu\OneDrive\Client;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\ClientException;
use League\OAuth2\Client\Token\AccessToken;

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
    protected $provider;
    protected $client;

    const FOLDER_ID = 'F5BE5C4B17A7DF0A%21105';

    function __construct()
    {
        $this->appKey = config('mirrorizer.onedrive_app_key');
        $this->appSecret = config('mirrorizer.onedrive_app_secret');
        $this->redirectUri = config('mirrorizer.onedrive_redirect_uri');
        $this->redirectUriLogout = config('mirrorizer.onedrive_redirect_uri_logout');
        $this->credentialsPath = config('mirrorizer.onedrive_credentials_path');


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

        $this->provider = $provider;
    }

    public function generateCredentials(Request $request)
    {

        if (!isset($_GET['code'])) {

            // If we don't have an authorization code then get one
            $authUrl = $this->provider->getAuthorizationUrl($options);

            // dd($authUrl);
            $_SESSION['oauth2state'] = $this->provider->getState();
            header('Location: '. $authUrl);
            exit;

        // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {

            // unset($_SESSION['oauth2state']);
            exit('Invalid state');

        } else {

            // Try to get an access token (using the authorization code grant)
            $token = $this->provider->getAccessToken('authorization_code', [
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

    protected function refreshToken($oldCredentials)
    {

        try {

            $newAccessToken = $this->provider->getAccessToken('refresh_token', [
                'refresh_token' => $oldCredentials->getRefreshToken()
            ]);

            file_put_contents($this->credentialsPath, json_encode($newAccessToken));

        } catch (Exception $e) {

            return $e->getMessage();

        }

        return $newAccessToken;
    }

    protected function setupClient($token)
    {
        //Create a Guzzle Client
        $guzzle = new Guzzle;

        //Initialize the OneDrive Client
        $this->client = new Client($token, $guzzle);

        return $this->client;
    }

    /**
     * Upload file to OneDrive
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

        $credentials = json_decode(file_get_contents($this->credentialsPath), true);

        if (is_null($credentials))
            throw new \Exception("Credentials not found!", 1);

        $token = new AccessToken($credentials);

        // Token expired. Get new token
        if ($token->hasExpired()) {
            $token = $this->refreshToken($token);
        }

        // setup $this->client
        $this->setupClient($token->getToken());

        try {

            $file = $this->client->uploadFile($fullPath , $fileName, static::FOLDER_ID);
            $resp['data'] = $file;
    
        } catch (\Exception $e) {

            $resp['error'] = 'Error when uploading to OneDrive ' . $e->getMessage();
            return $resp;  

        }

        // set permission to public
        $permission = $this->setPermissionToPublic($file->id);

        if ($permission instanceof \Exception) {

            $resp['error'] = 'Error when set permission';
            return $resp;

        } else {

            $resp['permission'] = $permission;

        }


        return $resp;
    }

    private function setPermissionToPublic($id)
    {
        try {
    
            return $this->client->createShareLink($id, 'view');
    
        } catch (\Exception $e) {

            return $e;

        }
    }
}
