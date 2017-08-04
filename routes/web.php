<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use App\Tools\GoogleDrive;
use App\Tools\Dropbox;
use App\Tools\Onedrive;

$app->get('/', function () use ($app) {

    $responseData = [
        'error'         => false,
        'message'       => 'Hello :)',
        'result'        => null
    ];

    return response($responseData);

});

$app->group(['prefix' => 'sample'], function() use ($app) {

    $app->get('/uploader', function () use ($app){

        $type = $app->make('request')->input('type', null);
        $file = '/var/www/mirrorizerApi/storage/app/uploads/emshidiq/2017/04/20/1492663705_FnxIPiHl_EFIN.jpeg';
        $name = 'file.jpg';

        switch (strtolower($type)) {
            case 'google':
                $uploader = new GoogleDrive;
                break;

            case 'dropbox':
                $uploader = new Dropbox;
                break;

            case 'onedrive':
                $uploader = new Onedrive;
                $uploader->generateCredentials();
                die;
                break;
            
            default:
                return response("WRONG TYPE");
                break;
        }

        $response = $uploader->upload($file, $name);

        return response($response);

    });

});

$app->group(['prefix' => 'auth'], function () use ($app) {

    $app->get('microsoft', 'AuthController@getMicrosoftAuth');

});

// members
$app->group(['prefix' => 'members'], function () use ($app) {

    $app->post('register', 'MemberController@postRegister');

    $app->get('verify/{code}', ['as' => 'members.verify', 'uses' => 'MemberController@getVerify']);
    
    $app->post('login', 'MemberController@postLogin');

    $app->post('reset-password', 'MemberController@postResetPassword');

    $app->post('change-password', ['middleware' => 'auth', 'uses' => 'MemberController@postChangePassword']);

    $app->post('change-email', ['middleware' => 'auth', 'uses' => 'MemberController@postChangeEmail']);

});

// uploads
$app->group(['prefix' => 'uploads', 'middleware' => 'auth'], function () use ($app) {

    $app->get('/', 'UploadController@getIndex');
    $app->post('/', 'UploadController@postNew');

    $app->get('/{upload_id}', 'UploadController@getDetail');
    $app->post('/{upload_id}', 'UploadController@postEdit');

});

// directory
$app->group(['prefix' => 'directory', 'middleware' => 'auth'], function () use ($app) {

    $app->get('/', 'DirectoryController@getIndex');
    $app->post('/', 'DirectoryController@postNew');

    $app->get('/{folder_id}', 'DirectoryController@getDetail');
    $app->post('/{folder_id}', 'DirectoryController@postEdit');

});
