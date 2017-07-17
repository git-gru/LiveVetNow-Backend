<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class States extends Authenticatable
{
    use Notifiable;
    // custom mentioned
    protected $table = 'states';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        ''
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        
    ];


}
