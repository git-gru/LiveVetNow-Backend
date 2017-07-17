<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Doctortransdetail extends Authenticatable
{
    use Notifiable;
    // custom mentioned
    protected $table = 'doctor_trans_details';
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

    public function scopeGetPetByAppointment($query,$apt_id){
        $petDetails = Appointment::where('appointment.id','=',$apt_id)
                    ->join('vet_details','appointment.vet_det_id','=','vet_details.id')
                    ->join('users','vet_details.user_id','=','users.id')
                    ->select('vet_details.vet_name','users.name')
                    ->first();
        return $petDetails;
    }

}
