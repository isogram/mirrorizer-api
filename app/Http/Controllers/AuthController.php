<?php

namespace App\Http\Controllers;

use Auth;

use DB;

use Illuminate\Http\Request;

use App\Constant;

use App\Tools\Onedrive;

class AuthController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

    }

    public function getMicrosoftAuth(Request $request)
    {
        $onedrive = new Onedrive;
        $creds = $onedrive->generateCredentials();

        return $creds;
    }
}
