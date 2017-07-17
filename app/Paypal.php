<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Paypal extends Authenticatable
{
    use Notifiable;
    // custom mentioned
    protected $table = 'paypal_details';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [
    //     ''
    // ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        
    ];

    public function scopeUpdateUser($request,$user_id,$updatedArr) {
        $paypalDetail = Paypal::where('doctor_id','=',$user_id)->update($updatedArr);
        return $paypalDetail;
    }

    public function scopeGetDetailsById($request,$userId) {
        $paypalDetail = Paypal::where('doctor_id','=',$userId)->get();
        return $paypalDetail;
    }

}
