<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Doctorspeciality extends Authenticatable
{
    use Notifiable;
    // custom mentioned
    protected $table = 'doctor_speciality';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token','session_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        
    ];

    public function scopeGetDocSpecialities($query){
        $specialities = Doctorspeciality::select('id as speciality_id','speciality_name')->get();
        return $specialities;
    }
}
?>