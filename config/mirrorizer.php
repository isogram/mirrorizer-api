<?php

return [

    'upload_directory' => '/uploads',
    'full_upload_path' => storage_path('app') . '/uploads',

    'mirror_provider' => [
        'GDRIVE', // Google Drive
        'DROPBOX', // Dropbox
        'ONEDRIVE', // Microsoft OneDrive
    ],

    'google_application_name'      => env('APPLICATION_NAME', 'Mirrorizer API'),
    'google_credentials_path'      => __DIR__ . '/google/' . env('CREDENTIALS_NAME', 'google-drive.json'),
    'google_client_secret_path'    => __DIR__ . '/google/client-secret.json',
    'google_directory_to_save'     => env('DIRECTORY_TO_SAVE', '0BxzXl76nV9ktYjBxcTduc1hRUEU'),

    'dropbox_app_key'              => env('DROPBOX_APP_KEY', '0bhx1zt38fbjcxu'),
    'dropbox_app_secret'           => env('DROPBOX_APP_SECRET', 'r4ucym6sbqytwsc'),
    'dropbox_access_token'         => env('DROPBOX_ACCESS_TOKEN', 'cv_D_uHdp1AAAAAAAAAACcD68Lti4cBNZgOty4JLDPnxud1XMnydKYOZYpBjAKiz'),

    'onedrive_app_key'             => env('ONEDRIVE_APP_KEY', '4746b654-3587-474d-a4cb-f27d18e7d46a'),
    'onedrive_app_secret'          => env('ONEDRIVE_APP_SECRET', 'CA14F474AF262DF3C351C4B1C646D0DE3ABDA591'),
    'onedrive_redirect_uri'        => env('ONEDRIVE_REDIRECT_URI', 'https://isogram.tk:8092/auth/microsoft'),
];