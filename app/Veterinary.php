<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Veterinary extends Authenticatable
{
    use Notifiable;
    // custom mentioned
    protected $table = 'veterinary';
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

     public function scopeGetAllPetType($query) {
        $allPetsType = Veterinary::all();
        return $allPetsType;
    }

}
