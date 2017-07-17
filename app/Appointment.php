<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Appointment extends Authenticatable
{
    use Notifiable;
    // custom mentioned
    protected $table = 'appointment';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token','session_id','apt_enddatetime'
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

    /**
    * return session to docotor.
    */
    public function scopeGetSessionByApptId($query,$apt_id,$user_id){
        $sessionDetails = Appointment::where('appointment.id','=',$apt_id)
                    ->where(function($query) use ($user_id){
                        $query->where('user_id','=',$user_id)
                                ->orWhere('doctor_id','=',$user_id);
                    })
                    ->select('token','session_id')
                    ->first();
        return $sessionDetails;
    }

    /**
    * update appointment status
    */
    public function scopeUpdateAppointment($query,$apt_id,$updateArr){
        $updatedAppointment = Appointment::where('appointment.id','=',$apt_id)->update($updateArr);
        return $updatedAppointment;
    }

    /**
    * returns appointment list
    */
    public function scopeGetApoointmentDetails($query,$user_id){
        $sessionDetails = Appointment::where(function($query) use ($user_id) {
            $query->where('appointment.doctor_id','=',$user_id)
                    ->orWhere('appointment.user_id','=',$user_id);
        })
                    ->join('users','appointment.user_id','=','users.id')
                    ->join('users as doctor','appointment.doctor_id','=','doctor.id')
                    ->leftjoin('states','doctor.location_id','=','states.id')
                    ->leftjoin('doctor_speciality','doctor.speciality_id','=','doctor_speciality.id')
                    ->join('vet_details','appointment.vet_det_id','=','vet_details.id')
                    ->join('veterinary','vet_details.vet_id','=','veterinary.id')
                    ->select('users.id as user_id','users.name as user_name','users.image_url as user_image_url','doctor.name as doctor_name','doctor.id as doctor_id','doctor.location_id as doctor_location','doctor_speciality.speciality_name as doctor_speciality','states.state as doctor_state','doctor.image_url as doctor_image_url','vet_details.vet_name as pet_name','vet_details.sex as pet_sex','vet_details.age as pet_age','vet_details.vet_image_url as pet_image_url','appointment.app_status as appointment_status','appointment.apt_datetime as appointment_datetime','appointment.id as apt_id','vet_details.id as pet_id','veterinary.veterinary_name as pet_type')
                    ->orderBy('appointment.apt_datetime','desc')
                    ->get();
        return $sessionDetails;
    }
    public function scopeGetAppointmentHistory($query,$user_id){
        $sessionDetails = Appointment::where(function($query) use ($user_id) {
            $query->where('appointment.doctor_id','=',$user_id)
                    ->orWhere('appointment.user_id','=',$user_id);
        })
                    ->where('appointment.app_status','=','confirmed')
                    ->join('users','appointment.user_id','=','users.id')
                    ->join('users as doctor','appointment.doctor_id','=','doctor.id')
                    ->leftjoin('states','doctor.location_id','=','states.id')
                    ->leftjoin('doctor_speciality','doctor.speciality_id','=','doctor_speciality.id')
                    ->leftjoin('notes','appointment.id','=','notes.apt_id')
                    ->join('vet_details','appointment.vet_det_id','=','vet_details.id')
                    ->join('veterinary','vet_details.vet_id','=','veterinary.id')
                    ->select('users.id as user_id','users.name as user_name','users.image_url as user_image_url','doctor.name as doctor_name','doctor.id as doctor_id','doctor.location_id as doctor_location','doctor_speciality.speciality_name as doctor_speciality','states.state as doctor_state','doctor.image_url as doctor_image_url','vet_details.vet_name as pet_name','vet_details.sex as pet_sex','vet_details.age as pet_age','vet_details.vet_image_url as pet_image_url','appointment.app_status as appointment_status','appointment.apt_datetime as appointment_datetime','appointment.id as apt_id','vet_details.id as pet_id','veterinary.veterinary_name as pet_type','notes.description as notes')
                    ->orderBy('appointment.apt_datetime','desc')
                    ->get();
        return $sessionDetails;
    }
    // To find if user have scheduled apt or not.
    public function scopeGetApoointmentByUserId($query,$user_id){
        $appointmentDetails = Appointment::where('doctor_id','=',$user_id)
                        ->orWhere('user_id','=',$user_id)
                        ->first();
        return $appointmentDetails;
    }
    // to check doctor availability
    public function scopeGetDoctorAvailability($query,$doctor_id,$appointment_datetime,$appointment_enddatetime){
        $appointmentDetails = Appointment::where('doctor_id','=',$doctor_id)
                        // ->Where('apt_datetime','<=',$appointment_datetime)
                        // ->Where('apt_datetime','>=',$appointment_enddatetime)
                        ->where(function($query) use($appointment_datetime) {
                            $query->where('apt_datetime','<=',$appointment_datetime)
                                ->where('apt_enddatetime','>=',$appointment_datetime);
                        })
                        ->orWhere(function($query) use( $appointment_enddatetime) {
                            $query->where('apt_datetime','<=',$appointment_enddatetime)
                                ->where('apt_enddatetime','>=',$appointment_enddatetime);
                        })
                        ->orWhere(function($query) use($appointment_datetime, $appointment_enddatetime) {
                            $query->where('apt_datetime','>=',$appointment_datetime)
                                ->where('apt_enddatetime','<=',$appointment_enddatetime);
                        })
                        ->orWhere(function($query) use($appointment_datetime, $appointment_enddatetime) {
                            $query->where('apt_datetime','<=',$appointment_datetime)
                                ->where('apt_enddatetime','>=',$appointment_enddatetime);
                        })
                        ->get();
        return $appointmentDetails;
    }
    // get transaction details
    public function scopeGetTransactionList($query,$user_id){
        $transactionDetails = Appointment::where(function($query) use ($user_id) {
                        $query->where('appointment.doctor_id','=',$user_id)
                                ->orWhere('appointment.user_id','=',$user_id);
                     })
                     ->join('txn_details','appointment.id','=','txn_details.apt_id')
                     ->join('users','appointment.user_id','=','users.id')
                     ->join('users as doctor','appointment.doctor_id','=','doctor.id')
                     ->select('txn_details.txn_id','doctor.name as doctor_name','users.name as user_name','appointment.apt_datetime','txn_details.txn_datetime as txn_date','txn_details.txn_amount','txn_details.currency')
                     ->get();
        return $transactionDetails;
    }

}
