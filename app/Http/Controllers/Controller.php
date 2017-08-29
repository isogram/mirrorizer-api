<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    private $responseData;

    function __construct()
    {

    }

    public function responseData($data = [], $errors = false, $message = null)
    {

        $this->responseData = [
            'error'         => $errors,
            'message'       => $message,
            'result'        => $data
        ];

        return $this->responseData;

    }

    public function resultItem($data, $type)
    {

        $ty = strtolower($type);
        $hystck = ['dir', 'file'];

        if ( !in_array($ty, $hystck) )
            throw new Exception("Cannot process the result item");

        if ($ty == 'dir') {
            $directoryId = $data->id;
            $uploadId = null;
            $parentId = $data->parent_id;
            $name = $data->name;
            $info = [];
            $links = [];
        } else {
            $directoryId = $data->directory_id;
            $uploadId = $data->id;
            $parentId = null;
            $name = $data->filename;
            $info = $data->info;
            $links = $data->links ? $data->links : [];
        }

        $arrResponse = [
            "type" => $ty,
            "directory_id" => $directoryId,
            "upload_id" => $uploadId,
            "parent_id" => $parentId,
            "name" => $name,
            "info" => $info,
            "created_at" => $data->created_at->format('Y-m-d H:i:s'),
            "updated_at" => $data->updated_at->format('Y-m-d H:i:s'),
            "links" => $links
        ];

        return $arrResponse;
    }

}
