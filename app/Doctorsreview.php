<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Doctorsreview extends Authenticatable
{
    use Notifiable;
    // custom mentioned
    protected $table = 'doctors_review';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'doctor_id','user_id','apt_id','review'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        
    ];

    public function scopeGetLatestReview($query,$doctor_id){
        $docDetails = Doctorsreview::where('doctors_review.doctor_id','=',$doctor_id)
                    ->join('users','doctors_review.user_id','=','users.id')
                    ->select('doctors_review.review','users.name','users.image_url','users.host')
                    ->first();
        return $docDetails;
    }

}
