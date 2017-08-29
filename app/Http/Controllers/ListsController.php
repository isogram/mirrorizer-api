<?php

namespace App\Http\Controllers;

use Auth;

use Validator;

use DB;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

use App\Constant;

use App\Models\Directory;
use App\Models\Upload;
use App\Models\Link;

class ListsController extends Controller
{
    const PER_PAGE = 100;
    const DEFAULT_PAGE = 1;
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
        // PAGINATION
        $page       = (int)$request->get('page', self::DEFAULT_PAGE) < 1 ? self::DEFAULT_PAGE : (int)$request->get('page', self::DEFAULT_PAGE);
        $perPage    = (int)$request->get('per_page', self::PER_PAGE) < 1 ? self::PER_PAGE : (int)$request->get('per_page', self::PER_PAGE);
        $offset     = ($page  - 1) * $perPage;

        $member = Auth::user();

        $parentID = $request->get('parent_id', 0);

        $dirs = Directory::where('member_id', $member->id)
                ->where('parent_id', $parentID)
                ->select([
                    DB::raw('"dir" as type'),
                    'id as directory_id',
                    DB::raw('NULL as upload_id'),
                    DB::raw('parent_id'),
                    'name',
                    DB::raw('NULL as info'),
                    'created_at',
                    'updated_at',
                ]);

        $files = Upload::where('member_id', $member->id)
                ->where('directory_id', $parentID)
                ->select([
                    DB::raw('"file" as type'),
                    DB::raw('directory_id as directory_id'),
                    'id as upload_id',
                    DB::raw('NULL as parent_id'),
                    'filename as name',
                    DB::raw('info'),
                    'created_at',
                    'updated_at',
                ]);

        // union the queries
        $dirs->unionAll($files);

        $count = DB::table( DB::raw("({$dirs->toSql()}) as sub") )
            ->mergeBindings($dirs->getQuery())
            ->count();

        $data = $dirs->take($perPage)->offset($offset)->get();

        $items = [];
        foreach ($data as $key => $value) {
            $items[$key] = $value->toArray();
            $items[$key]['info'] = !empty($value['info']) ? json_decode($value['info']) : [];
            $items[$key]['links'] = Link::where('upload_id', $value->upload_id)->get();
        }

        if(is_array($items)){
            $items = collect($items);
        }

        $res = new LengthAwarePaginator(
            $items,
            $count,
            $perPage,
            Paginator::resolveCurrentPage(),
            ['path' => Paginator::resolveCurrentPath()]
        );

        $resp = $this->responseData($res, false, Constant::SUCCESS_TO_FETCH_ITEM);

        return response($resp);
    }

}
