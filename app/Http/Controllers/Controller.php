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

}
