<?php

namespace App\Http\Controllers;

use Auth;

use Validator;

use DB;

use Illuminate\Http\Request;

use App\Constant;

use App\Models\Upload;
use App\Models\Link;
use App\Jobs\MirrorizerJob;

class UploadController extends Controller
{

    const MSG_FILE_NOT_FOUND = 'File is not belongs to you!';
    const MSG_NO_MIRROR_PROVIDER = 'No mirror provider available!';

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

        $directoryID = $request->get('directory_id', 0);

        $uploaded = $member->uploads()->where('directory_id', $directoryID)->paginate();

        $resp = $this->responseData($uploaded, false, Constant::SUCCESS_TO_FETCH_ITEM);

        return response($resp);
    }

    public function getDetail(Request $request, $fileID)
    {
        $member = Auth::user();

        // check fileID exsitance
        $file = Upload::where('id', $fileID)->where('member_id', $member->id)->first();

        if (!$file) {
            return response($this->responseData([], self::MSG_FILE_NOT_FOUND, Constant::FAILED_VALIDATION) , 422);
        }

        $resp = $this->responseData($file, false, Constant::SUCCESS_TO_FETCH_ITEM);

        return response($resp);
    }

    public function postNew(Request $request)
    {
        // get used mirrorizer provider
        $providers = config('mirrorizer.mirror_provider');

        if (!$providers)
            return response($this->responseData([], self::MSG_NO_MIRROR_PROVIDER, Constant::SERVER_ERROR) , 400);

        $member = Auth::user();


        $validator = Validator::make($request->all(), [
            'file'          => 'required|file',
            'directory_id'  => 'numeric|is_my_folder:' . $member->id,
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

        if ($request->hasFile('file')) {

            $file = $request->file('file');

            $strID = str_random(8);

            // set destination path
            $storage = config('mirrorizer.full_upload_path');
            $uploadPath = config('mirrorizer.upload_directory');
            $userDestinationPath = $member->username . '/' . date('Y/m/d');
            $destinationPath = $storage . '/' . $userDestinationPath;

            try {

                // check or create storage for current user
                is_dir($destinationPath) ?: mkdir($destinationPath, 0775, true);

            } catch (\Exception $e) {

                return response($this->responseData([], $e->getMessage(), Constant::SERVER_ERROR) , 400);

            }

            // set filename
            $fileName = $file->getClientOriginalName();
            
            $info = [
                'str_id'            => $strID,
                'size'              => $file->getClientSize(),
                'mime_type'         => $file->getMimeType(),
                'client_name'       => $file->getClientOriginalName(),
                'client_extension'  => $file->getClientOriginalExtension(),
                'client_mime_type'  => $file->getClientMimeType(),
            ];

            try {            

                // move the file
                $theFile = time() . '_' . $strID . '_' . $fileName;
                $file->move($destinationPath, $theFile);

            } catch (\Exception $e) {

                return response($this->responseData([], $e->getMessage(), Constant::SERVER_ERROR) , 400);

            }

            try {

                // save to db            
                $upload = new Upload;
                $upload->filename   = $fileName;
                $upload->directory_id = $request->get('directory_id', 0);
                $upload->str_id     = $strID;
                $upload->path       = $userDestinationPath . '/' . $strID;
                $upload->info       = json_encode($info);

                $member->uploads()->save($upload);

                $links = [];
                foreach ($providers as $provider) {
                    $link = new Link(['vendor' => $provider]);
                    array_push($links, $link);
                }

                if ($links) {
                    $upload->links()->saveMany($links);
                }

                // mirrorize here
                // put jobs into queue to mirroring files
                dispatch(new MirrorizerJob($upload));

            } catch (\Exception $e) {

                return response($this->responseData([], $e->getMessage(), Constant::FAILED_TO_PROCESS) , 400);

            }

            $json = $this->resultItem($upload, 'file');

            return response( $this->responseData($json, false, Constant::SUCCESS_TO_CREATE_ITEM) );

        }
    }

    public function postEdit(Request $request, $fileID)
    {
        $member = Auth::user();

        // check fileID exsitance
        $file = Upload::where('id', $fileID)->where('member_id', $member->id)->first();

        if (!$file) {
            return response($this->responseData([], self::MSG_FILE_NOT_FOUND, Constant::FAILED_VALIDATION) , 422);
        }

        $validator = Validator::make($request->all(), [
            'filename'      => 'required',
            'directory_id'  => 'numeric|is_my_folder:' . $member->id,
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

            $reqDirID = $request->get('directory_id');

            $file->directory_id = ($reqDirID != "" || !is_null($reqDirID)) && $reqDirID != $file->id ? $reqDirID : $file->directory_id;
            $file->filename      = trim($request->get('filename'));
            $file->save();

        } catch (\Exception $e) {

            return response($this->responseData([], $e->getMessage(), Constant::SERVER_ERROR) , 400);
        
        }

        $resp = $this->responseData($file, false, Constant::SUCCESS_TO_UPDATE_ITEM);

        return response($resp);
    }
}
