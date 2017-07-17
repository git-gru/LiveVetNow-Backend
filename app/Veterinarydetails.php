<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Veterinarydetails extends Authenticatable
{
    use Notifiable;
    // custom mentioned
    protected $table = 'vet_details';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vet_id','user_id','vet_name','vet_image_url','vet_host','sex','age','overview'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        
    ];

    public function scopeGetPetsByUserId($query, $userId='') {
        $allPets = Veterinarydetails::where('user_id','=',$userId)
                    ->join('veterinary','vet_id','=','veterinary.id')
                    ->select('vet_name as pet_name','vet_image_url as pet_image_url','vet_host as pet_host','sex as pet_sex','age as pet_age','veterinary.veterinary_name as pet_type','vet_details.id as pet_id')
                    ->get();
        return $allPets;
    }

    public function scopeRemovePet($query, $petId) {
        $isDeleted = Veterinarydetails::where('id','=',$petId)->delete();
        return $isDeleted;
    }

}
