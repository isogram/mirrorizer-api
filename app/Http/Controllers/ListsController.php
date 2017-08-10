<?php

namespace App\Http\Controllers;

use Auth;

use Validator;

use DB;

use Illuminate\Http\Request;

use App\Constant;

use App\Models\Directory;

class ListsController extends Controller
{

    const MSG_FOLDER_NOT_FOUND = 'Directory is not belongs to you!';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

    }

    public function getIndex(Request $request)
    {
        $member = Auth::user();

        $parentID = $request->get('parent_id', 0);

        $dirs = $member->directories()->where('parent_id', $parentID)->paginate();

        $resp = $this->responseData($dirs, false, Constant::SUCCESS_TO_FETCH_ITEM);

        return response($resp);
    }

}
