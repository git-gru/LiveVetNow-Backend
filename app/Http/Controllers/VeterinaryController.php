<?php
namespace App\Http\Controllers;

use App\User;
use App\Veterinarydetails;
use App\Veterinary;
use App\Appointment;
use Validator;
use Illuminate\Http\Request;
// Extending baseController
use App\Http\Controllers\BaseController;
// Includes for token authentication
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
// authentication library
use Illuminate\Support\Facades\Auth;

class VeterinaryController extends BaseController
{

    /**
    * reason: To get list of pets of a user
    */
    public function addPetOfUser(Request $request) {
        // Fetching all post data
        $requestData = $request->only('pet_type_id','pet_name','pet_age','pet_sex');

        $conditionArr = array(
            'pet_type_id' => 'required',
            'pet_name' => 'required',
            'pet_age'=>'required',
            'pet_sex'=>'required',
        );
      $validationRes = $this->ValidatorMethod($requestData,$conditionArr);

      if (!$validationRes['status']) {
        $errorDetails = $validationRes['error_details'];
        return $this->responseMethod($requestData,'Incomplete details.',false,'VALIDATOR_FAILS',$errorDetails);  
      }

        $userDetails = BaseController::getUserDetailsFromToken();
        
        $userId = $userDetails->id;
        $vetId = $requestData['pet_type_id'];
        $vetName = $requestData['pet_name'];

        $isPetAdded = Veterinarydetails::forceCreate([
            "vet_id"=>$vetId,
            "user_id"=>$userId,
            "vet_name"=>$vetName,
            "sex"=>$requestData['pet_sex'],
            "age"=>$requestData['pet_age'],
            "created_at"=>date('Y-m-d H:i:s')
        ]);
        
      return $this->responseMethod($isPetAdded,'Pet added Successfully',true,'OK');

    }
    /**
    * reason: To get list of pets of a user
    */
    public function getPetsOfUser($apt_id='') {
        // get pet by appointmentId
        if (!empty($apt_id)) {
            $petDetails = Appointment::getPetByAppointment($apt_id);
        } else {
        // get pet by userId
            $userDetails = BaseController::getUserDetailsFromToken();
            $petDetails = Veterinarydetails::getPetsByUserId($userDetails->id);
        }
        return $this->responseMethod($petDetails,'Pets found ',true,'OK');
    }

    public function getPetType(){
        $petTypes = Veterinary::getAllPetType();
        $petDetails = $petTypes->toArray();
        return $this->responseMethod($petDetails,'List of Pets type ',true,'OK');
    }

    public function deletePet($petId) {
        if (!empty($petId)) {
            $isDeleted = Veterinarydetails::removePet($petId);
            return $this->responseMethod($isDeleted,'Pet deleted Successfully',true,'OK');
        } else{
            return $this->responseMethod("",'Pet id required',false,'OK');
        } 
    }

}