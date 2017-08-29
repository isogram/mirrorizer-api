<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Upload extends Model
{

    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $table = 'uploads';

    protected $casts = [
        'member_id'     => 'integer',
        'directory_id'  => 'integer',
    ];

    protected $perPage = 100;

    protected $appends = ['name'];
    protected $hidden = ['filename'];

    public function getNameAttribute($value)
    {
        return $this->attributes['name'] = $this->attributes['filename'];
    }

    public function getInfoAttribute($value)
    {
        $json = json_decode($value);
        return $this->attributes['info'] = !is_null($json) ? $json : [];
    }

    public function member()
    {
        return $this->belongsTo('App\Models\Member', 'member_id');
    }

    public function directory()
    {
        return $this->belongsTo('App\Models\Directory', 'directory_id');
    }

    public function links()
    {
        return $this->hasMany('App\Models\Link', 'upload_id');
    }

}