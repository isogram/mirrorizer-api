<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{

    protected $table = 'links';
    protected $fillable = ['upload_id', 'url', 'vendor', 'status'];

    protected $casts = [
        'upload_id' => 'integer',
        'url' => 'string',
        'json_response' => 'string',
    ];

    public function upload()
    {
        return $this->belongsTo('App\Models\Upload', 'upload_id');
    }

}