<?php
namespace App\Http\Controllers;

use App\User;
use App\Doctorspeciality;
use App\Veterinarydetails;
use App\States;
use Image;
use File;
use URL;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
// Extending baseController
use App\Http\Controllers\BaseController;
// Includes for token authentication
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
// authentication library
use Illuminate\Support\Facades\Auth;

class CommonController extends BaseController
{
    /**
    * reason: User can edit his details
    */
    public function editDetails(Request $request) {
        
        
        $user = BaseController::getUserDetailsFromAuth();
        $requestData = $request->only('name','about_me','speciality_id','location_id','is_pet','pet_id');
        $conditionArr = array(
              'name' => 'required',
              'about_me'=>'required'
        );
        if (isset($requestData['is_pet']) && ($requestData['is_pet']=='yes')) {
                $conditionArr['pet_id'] = 'required';
        }
        if ($user['user_type']=='doctor') {
            $conditionArr['speciality_id'] = 'required';
            $conditionArr['location_id'] = 'required';
        }
        $validationRes = $this->ValidatorMethod($requestData,$conditionArr);

        if (!$validationRes['status']) {
          $errorDetails = $validationRes['error_details'];
          return $this->responseMethod($requestData,'Please check your details.',false,'VALIDATOR_FAILS',$errorDetails);  
        } 

        $id = $user['id'];

        $updateArr = array(
            "name"=>$requestData['name'],
            "overview"=>$requestData['about_me']
        );

        if ($user['user_type']=='doctor') {
            $updateArr['speciality_id'] = $requestData['speciality_id'];
            $updateArr['location_id'] = $requestData['location_id'];
        }

        if (isset($requestData['is_pet']) && ($requestData['is_pet']=='yes')) {
            $updateArr['vet_name'] = $requestData['name'];
            $pet = Veterinarydetails::findOrFail($requestData['pet_id']);
            $pet->update($updateArr);
            $message = 'Pet Record Updated Successfully';
        } else {
            $user = User::findOrFail($id);
            $user->update($updateArr);
            $message = 'User Record Updated Successfully';
        }

        return $this->responseMethod($updateArr,$message,true,'OK');
        
    } 
    /**
    * reason: User can change his password
    */
    public function changePassword(Request $request) {
        
        $requestData = $request->only('old_password','new_password');
        $conditionArr = array(
              'old_password' => 'required|min:6',
              'new_password' => 'required|min:6',
        );
        $validationRes = $this->ValidatorMethod($requestData,$conditionArr);

        if (!$validationRes['status']) {
          $errorDetails = $validationRes['error_details'];
          return $this->responseMethod($requestData,'Please check your details.',false,'VALIDATOR_FAILS',$errorDetails);  
        } 

        $user = BaseController::getUserDetailsFromAuth();
        $password = $user['password'];
        $id = $user['id'];

        if ( !Hash::check($requestData['old_password'],$password)) {
            // Return error message
            return $this->responseMethod($requestData,'Invalid Old Password.',false,'VALIDATOR_FAILS');
        }

        $updatedData['password'] = Hash::Make($requestData['new_password']);
        $user = User::findOrFail($id);
        $user->update($updatedData);

        return $this->responseMethod($user,'Password updated Successfully',true,'OK');
        
    } 
    /**
    * reason: Get locations list
    */
    public function getStates() {
        $statesList = States::all();
        return $this->responseMethod($statesList,'States found',true,'OK');
    }
    /**
    * Image Upload working api
    */
    public function uploadImage(Request $request) {
        
        $requestData = $request->only('image','vet_detail_id');
        $requestData['image'] = "data:image/jpeg;base64,".urldecode($requestData['image']);
// print_r($requestData);die;
        // $data = $requestData['image'];

        // list($type, $data) = explode(';', $data);
        // list(, $data)      = explode(',', $data);
        // $data = base64_decode($data);

        // file_put_contents('/imagdddde.png', $data);

        //get the base-64 from data
        // $base64_str = substr($requestData['image'], strpos($requestData['image'], ",")+1);

        //decode base64 string
        // $image = base64_decode($base64_str);

        $user = BaseController::getUserDetailsFromToken();
        $id = $userId = $user['id'];
        $userType = $user['user_type'];
        if (isset($requestData['vet_detail_id'])) {
            $id = $userId = $requestData['vet_detail_id'];
            $userType = 'pet';
        }

        $destinationPath = public_path() . '/images/profile_pics/'.$userType; // upload path

        if(!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, $mode = 0777, true, true);
        }

        $png_url = $userId."-".time().".png";
        $path = $destinationPath.'/'. $png_url;

        Image::make(file_get_contents($requestData['image']))->save($path);


        if ($userType=='pet') {
            $urlToImage = URL::to('/').'/public/images/profile_pics/pet/'.$png_url;
            $updatedURL['vet_image_url'] = 'profile_pics/pet/'.$png_url;
            $petDetails = Veterinarydetails::where("id",'=',$id)->update($updatedURL);
            // $petDetails->save($updatedURL);
        } else {
            $urlToImage = URL::to('/').'/public/images/profile_pics/'.$user['user_type'].'/'.$png_url;
            $updatedURL['image_url'] = 'profile_pics/'.$user['user_type'].'/'.$png_url;
            $userDetails = User::where("id",'=',$id)->update($updatedURL);
            // $userDetails = User::findOrFail($userId);
            // $userDetails->update($updatedURL);
        }

        return $this->responseMethod(array('image_url'=>$urlToImage),'Image uploaded successfully',true,'OK');
    }

    public function getSpecialityList() {
        $specilaityList = Doctorspeciality::getDocSpecialities();
        return $this->responseMethod($specilaityList,'List of doctors speciality',true,'OK');
    }

    public function inviteDoctor(Request $request) {
      $requestData = $request->only('email','name');

        $conditionArr = array(
              'email' => 'required|email',
              'name' => 'required',
        );
        $validationRes = $this->ValidatorMethod($requestData,$conditionArr);

        if (!$validationRes['status']) {
          $errorDetails = $validationRes['error_details'];
          return $this->responseMethod($requestData,'Please check your details.',false,'VALIDATOR_FAILS',$errorDetails);  
        } 

        $user = BaseController::getUserDetailsFromToken();
        $data = array('name'=>$requestData['name'],'inviter_name'=>$user['name']);
        $subject = 'Invitation to LiveVetNow';
        $view = 'mail';

        $isSend = BaseController::sendEmail($view,'',$requestData['email'],$subject,$data);

        if ($isSend) {
            return $this->responseMethod(array(),'Mail Sent Successfully',true,'OK');
        } else {
            return $this->responseMethod(array(),'Mail Sent Fails',false,'OK');
        }

        // Mail::send(['text'=>'mail'], $data, function($message) use ($requestData) {
        //     $message->to($requestData['email'], $requestData['name'])->subject
        //         ('Invitation to LiveVetNow');
        //     $message->from('support@livevetnow.com','LiveVetNow Team');
        // });
    }

}
