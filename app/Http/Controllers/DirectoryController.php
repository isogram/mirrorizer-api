<?php

namespace App\Http\Controllers;

use Auth;

use Validator;

use DB;

use Illuminate\Http\Request;

use App\Constant;

use App\Models\Directory;

class DirectoryController extends Controller
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

    public function getDetail(Request $request, $folderID)
    {
        $member = Auth::user();

        // check folderID exsitance
        $dir = Directory::where('id', $folderID)->where('member_id', $member->id)->first();

        if (!$dir) {
            return response($this->responseData([], self::MSG_FOLDER_NOT_FOUND, Constant::FAILED_VALIDATION) , 422);
        }

        $resp = $this->responseData($dir, false, Constant::SUCCESS_TO_FETCH_ITEM);

        return response($resp);
    }

    public function postNew(Request $request)
    {
        $member = Auth::user();

        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'parent_id'     => 'numeric|is_my_folder:' . $member->id,
        ]);

        // validate the rules
        if ($validator->fails()) {

            $errs = [];

            foreach ($validator->errors()->keys() as $key) {
                $errs[] = [
                    'field' => $key,
                    'msg'   => $validator->errors()->first($key)
                ];
            }

            return response( $this->responseData([], $errs, Constant::FAILED_VALIDATION) , 422 );

        }

        try {

            $dir = new Directory;
            $dir->member_id = $member->id;
            $dir->parent_id = $request->get('parent_id');
            $dir->name      = trim($request->get('name'));
            $dir->save();

        } catch (\Exception $e) {

            return response($this->responseData([], $e->getMessage(), Constant::SERVER_ERROR) , 400);
        
        }

        $resp = $this->responseData($dir, false, Constant::SUCCESS_TO_CREATE_ITEM);

        return response($resp);
    }

    public function postEdit(Request $request, $folderID)
    {
        $member = Auth::user();

        // check folderID exsitance
        $dir = Directory::where('id', $folderID)->where('member_id', $member->id)->first();

        if (!$dir) {
            return response($this->responseData([], self::MSG_FOLDER_NOT_FOUND, Constant::FAILED_VALIDATION) , 422);
        }

        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'parent_id'     => 'numeric|is_my_folder:' . $member->id,
        ]);

        // validate the rules
        if ($validator->fails()) {

            $errs = [];

            foreach ($validator->errors()->keys() as $key) {
                $errs[] = [
                    'field' => $key,
                    'msg'   => $validator->errors()->first($key)
                ];
            }

            return response( $this->responseData([], $errs, Constant::FAILED_VALIDATION) , 422 );

        }

        try {

            $reqParentID = $request->get('parent_id');

            $dir->parent_id = ($reqParentID != "" || !is_null($reqParentID)) && $reqParentID != $dir->id ? $reqParentID : $dir->parent_id;
            $dir->name      = trim($request->get('name'));
            $dir->save();

        } catch (\Exception $e) {

            return response($this->responseData([], $e->getMessage(), Constant::SERVER_ERROR) , 400);
        
        }

        $resp = $this->responseData($dir, false, Constant::SUCCESS_TO_UPDATE_ITEM);

        return response($resp);
    }
}
