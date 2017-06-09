<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;;

class Directory extends Model
{

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