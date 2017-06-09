<?php

namespace App\Tools;

use \Google_Client, \Google_Service_Drive, \Google_Service_Drive_DriveFile, \Google_Service_Drive_Permission;

class GoogleDrive
{

    protected $applicationName;
    protected $credentialsPath;
    protected $clientSecretPath;
    protected $directoryToSave;
    protected $scopes;
    protected $client;
    
    function __construct()
    {

        $this->applicationName = config('mirrorizer.google_application_name');
        $this->credentialsPath = config('mirrorizer.google_credentials_path');
        $this->clientSecretPath = config('mirrorizer.google_client_secret_path');
        $this->directoryToSave = config('mirrorizer.google_directory_to_save');
        $this->scopes = implode(' ', array(Google_Service_Drive::DRIVE));

        $this->client = new Google_Client();
        $this->client->setApplicationName( $this->applicationName );
        $this->client->setScopes( $this->scopes );
        $this->client->setAuthConfigFile( $this->clientSecretPath );
        $this->client->setAccessType('offline');
    }

    public function generateCredentials()
    {
        // Request authorization from the user.
        $authUrl = $this->client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.
        $accessToken = $this->client->authenticate($authCode);

        // Store the credentials to disk.
        if(!file_exists(dirname($this->credentialsPath))) {
          mkdir(dirname($this->$credentialsPath), 0700, true);
        }

        file_put_contents($this->credentialsPath, json_encode($accessToken));
        printf("Credentials saved to %s\n", $this->credentialsPath);
    }

    /**
     * Returns an authorized API client.
     * @return Google_Client the authorized client object
     */
    public function setupClient() {

        // Load previously authorized credentials from a file.
        if (!file_exists($this->credentialsPath)) {

            throw new \Exception("Please generate credentials first by execute : php artisan gdrive:generate");

        }

        $accessToken = file_get_contents($this->credentialsPath);
        $this->client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($this->client->isAccessTokenExpired()) {
            $this->client->refreshToken($this->client->getRefreshToken());
            file_put_contents($this->credentialsPath, json_encode($this->client->getAccessToken()));
        }

        return $this->client;

    }

    /**
     * Upload file to Google Drive
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

      // Get the API client and construct the service object.
      $client = $this->setupClient();
      $service = new Google_Service_Drive($client);

      $file = new Google_Service_Drive_DriveFile();

      $currentFile = $fullPath;
      $currentFileInfo = pathinfo($currentFile);
      $currentFileMime = mime_content_type($currentFile);

      // Set the metadata
      $file->setName($fileName);
      $file->setDescription( $fileName . " is uploaded by automated system!" );
      $file->setMimeType($currentFileMime);

      $isDirectory = $this->directoryToSave;
      if ($isDirectory) {
        $file->setParents(array($this->directoryToSave));
      }

      try {
        $createdFile = $service->files->create($file, array(
                        'data' => file_get_contents($currentFile),
                        'mimeType' => $currentFileMime,
                        'uploadType'=> 'multipart',
                      ));
      } catch (\Exception $e) {

        $resp['error'] = $e->getMessage();
        return $resp;

      }

      // upload success
      $fileId = isset($createdFile->id) ? $createdFile->id : null;
      
      if ($fileId) {

        $permission = $this->setPermissionToPublic($createdFile->id);

        $resp['data'] = $createdFile;
        $resp['permission'] = $permission;

        return $resp;

      } else {

        $resp['error'] = 'Error when uploading to Google Drive';
        return $resp;

      }

    }

    private function setPermissionToPublic($fileId)
    {
        $client = $this->setupClient();
        $service = new Google_Service_Drive($client);

        // set permission
        $permission = new Google_Service_Drive_Permission();
        $permission->setType('anyone');
        $permission->setRole('reader');

        try {

            $perm = $service->permissions->create($fileId, $permission);
            
        } catch (\Exception $e) {
            return $e;
        }

        return $perm;
    }
}