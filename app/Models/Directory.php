<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Directory extends Model
{

    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $table = 'directory';

    protected $casts = [
        'upload_id' => 'integer',
        'member_id' => 'integer',
        'parent_id' => 'integer',
    ];

    public function member()
    {
        return $this->belongsTo('App\Models\Member', 'member_id');
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\Directory', 'parent_id');
    }

}