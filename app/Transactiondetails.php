<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Transactiondetails extends Authenticatable
{
    use Notifiable;
    // custom mentioned
    protected $table = 'txn_details';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        
    ];

    
    public function scopeGetTotalEarnings($query, $user_id) {
        $totalEarnings = Transactiondetails::join('appointment','txn_details.apt_id','=','appointment.id')
                    ->where('appointment.doctor_id','=',$user_id)
                    ->groupBy('appointment.doctor_id')
                    ->sum('txn_details.txn_amount');
        return $totalEarnings;
    }
    
    public function scopeGetWeeksTotalEarnings($query, $user_id, $week_sd,$week_ed) {
        $totalEarnings = Transactiondetails::join('appointment','txn_details.apt_id','=','appointment.id')
                    ->where('appointment.doctor_id','=',$user_id)
                    ->where('txn_details.txn_datetime','>=',$week_sd)
                    ->where('txn_details.txn_datetime','<=',$week_ed)
                    ->groupBy('appointment.doctor_id')
                    ->sum('txn_details.txn_amount');
        return $totalEarnings;
    }
}
?>