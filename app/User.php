<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    // custom mentioned
    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','user_type','location_id','broadcasting_id','is_premier','overview','image_url','available_start_time','available_end_time'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
    * reason: To get list of doctors
    */
    public function scopeGetConfirmedDoctors($query)
    {
        $doctorsList = User::where('user_type','=','doctor')
                        ->where('isConfirmed','=','1')
                        ->join('states','users.location_id','=','states.id')
                        ->join('doctor_speciality','users.speciality_id','=','doctor_speciality.id')
                        ->select('users.*','states.state','states.state_code','doctor_speciality.speciality_name')
                        ->get();
        return $doctorsList;
    }

    /**
    * reason: To get list of doctors
    */
    public function scopeGetRecomendedConfirmedDoctors($query)
    {
        $doctorsList = User::where('user_type','=','doctor')
                        ->where('isConfirmed','=','1')
                        ->where('is_premier','=','1')
                        ->join('states','users.location_id','=','states.id')
                        ->join('doctor_speciality','users.speciality_id','=','doctor_speciality.id')
                        ->select('users.*','states.state','states.state_code','doctor_speciality.speciality_name')
                        ->get();
        return $doctorsList;
    }

    /**
    * reason: To get list of doctors
    */
    public function scopeGetDoctorsThirtyMin($query)
    {
        $currentDateTime = strtotime(date('Y-m-d H:i:s'));
        $startDateTime = $currentDateTime+(60*15);
        $endDateTime = $startDateTime+(60*30);
        $doctorsList = User::where('user_type','=','doctor')
                        ->where('isConfirmed','=','1')
                        ->join('states','users.location_id','=','states.id')
                        ->join('doctor_speciality','users.speciality_id','=','doctor_speciality.id')
                        ->leftJoin('appointment',function($join) use ($startDateTime) {
                            $join->on('appointment.doctor_id','=','users.id');
                            $join->where('appointment.apt_enddatetime','>=',$startDateTime);
                        })
                        ->whereNull('appointment.doctor_id')
                        ->select('users.*','states.state','states.state_code','doctor_speciality.speciality_name')
                        ->get();
        return $doctorsList;
    }
    public function scopeGetRecommendedDoctorsThirtyMin($query)
    {
        $currentDateTime = strtotime(date('Y-m-d H:i:s'));
        $startDateTime = $currentDateTime+(60*15);
        $endDateTime = $startDateTime+(60*30);
        $doctorsList = User::where('user_type','=','doctor')
                        ->where('isConfirmed','=','1')
                        ->where('is_premier','=','1')
                        ->join('states','users.location_id','=','states.id')
                        ->join('doctor_speciality','users.speciality_id','=','doctor_speciality.id')
                        ->leftJoin('appointment',function($join) use ($startDateTime) {
                            $join->on('appointment.doctor_id','=','users.id');
                            $join->where('appointment.apt_enddatetime','>=',$startDateTime);
                        })
                        ->whereNull('appointment.doctor_id')
                        ->select('users.*','states.state','states.state_code','doctor_speciality.speciality_name')
                        ->get();
        return $doctorsList;
    }

    public function scopeSearchDoctors($query,$paramters)
    {
        $docName = $paramters['doctor_name'];
        $searchDoctors =  User::where('user_type','=','doctor')
                        ->where('isConfirmed','=','1')
                        ->where('name','like','%'.$docName.'%')
                        ->join('states','users.location_id','=','states.id')
                        ->select('users.*','states.*')
                        ->get();
        return $searchDoctors;
    }

    public function scopeGetUserDetails($query, $user_id) {
        $userDetails = User::where('users.id','=',$user_id)
                ->leftjoin('states','users.location_id','=','states.id')
                ->leftjoin('doctor_speciality','users.speciality_id','=','doctor_speciality.id')
                ->select('users.*','states.state','states.state_code','doctor_speciality.speciality_name')
                ->first();
        return $userDetails;
    }

    public function scopeGetUserBasicDetails($query, $user_id) {
        $userDetails = User::where('users.id','=',$user_id)
                        ->first();
        return $userDetails;
    }

    public function scopeUpdateAvailableStatus($query, $user_id,$updateArr) {
        $userDetails = User::where('users.id','=',$user_id)->update($updateArr);
        return $userDetails;
    }
}
