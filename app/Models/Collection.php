<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
        'image_url',
    ];
}