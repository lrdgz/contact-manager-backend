<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Contacts extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'firstname',
        'lastname',
        'email',
        'phonenumber',
        'image_file',
    ];
}
