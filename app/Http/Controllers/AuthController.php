<?php
namespace App\Http\Controllers;

use App\User;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
// Extending baseController
use App\Http\Controllers\BaseController;
// Includes for token authentication
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
// authentication library
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{

    /**
    * Creator: Bhupinder Garg
    * Date: 13May2017
    * Requirement: Let user register himself
    */
    public function create(Request $request)
    {
      $requestData = $request->all();
      $conditionArr = array(
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6',
            'name' => 'required|min:3',
            'user_type' => 'required'
      );

      if (isset($requestData['user_type']) && $requestData['user_type']=='doctor') {
        $conditionArr['location_id'] = 'required';
        $conditionArr['speciality_id'] = 'required';
        // $conditionArr['available_start_time'] = 'required';
        // $conditionArr['available_end_time'] = 'required';
      }
      $validationRes = $this->ValidatorMethod($requestData,$conditionArr);

      if (!$validationRes['status']) {
        $errorDetails = $validationRes['error_details'];
        return $this->responseMethod($requestData,'Please check your details.',false,'VALIDATOR_FAILS',$errorDetails);  
      } 

      $insertArr = array(
          'email' => $requestData['email'],
          'name' => $requestData['name'],
          'user_type' => $requestData['user_type'],
          'password' => Hash::Make($requestData['password']),
          'broadcasting_id' => ''
      );

      
      if ($requestData['user_type']=='doctor') {
        
        //   $avalableStartTime = explode(':',$requestData['available_start_time']);
        //   $avalableEndTime = explode(':', $requestData['available_end_time']);

        //   $startTime = (($avalableStartTime[0]*60*60)+($avalableStartTime[1]*60)+($avalableStartTime[2]));
        //   $endTime = (($avalableEndTime[0]*60*60)+($avalableEndTime[1]*60)+($avalableEndTime[2]));

          $insertArr['location_id'] = $requestData['location_id'];
          $insertArr['speciality_id'] = $requestData['speciality_id'];
        //   $insertArr['available_start_time'] = $startTime;
        //   $insertArr['available_end_time'] = $endTime;
      } else {
          if (isset($requestData['location_id'])) {
            $insertArr['location_id'] = $requestData['location_id'];
          }
          $insertArr['isConfirmed'] = 1;
      }

      $isUserCreated = User::create($insertArr);

      if ($requestData['user_type']=='doctor') {
       
        $lastInsertId = $isUserCreated['id'];

        $uniqueId = 'broad_id_'.$lastInsertId.'_'.rand(111,999);
        $updateData = array(
            'broadcasting_id' => $uniqueId
        );

            // updating record
            $user = User::findOrFail($lastInsertId);
            $user->update($updateData);
      }

      
        $data = array('name'=>$requestData['name']);
        $subject = 'LiveVetNow: Signup Successfully';
        $view = 'signupmail';

        $isSend = BaseController::sendEmail($view,'',$requestData['email'],$subject,$data);

      return $this->responseMethod($requestData,'User Registered Successfully',true,'OK');
    }

    /**
    * Creator: Bhupinder Garg
    * Date: 13May2017
    * Requirement: Let user login himself
    */
    public function login(Request $request)
    {
      $requestData = $request->all();
      $credentials = $request->only('email', 'password');
      $conditionArr = array(
            'email' => 'required|email',
            'password' => 'required|min:6'
      );
      $validationRes = $this->ValidatorMethod($requestData,$conditionArr);

      if (!$validationRes['status']) {
        $errorDetails = $validationRes['error_details'];
        return $this->responseMethod($requestData,'Please check your details.',false,'VALIDATOR_FAILS',$errorDetails);  
      }

        try {
            // attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return $this->responseMethod($requestData,'Invalid User Details',false,'VALIDATOR_FAILS');
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return $this->responseMethod($requestData,'Unable To Create Token',false,'INTERNAL_SERVER_ERROR');
        }

        $currentUser = Auth::user()->toArray();

        $resposeArray = array(
            "name"=>$currentUser['name'],
            "userid"=>$currentUser['id'],
            "image_url"=>$currentUser['image_url'],
            "host"=>$currentUser['host'],
            "email"=>$currentUser['email'],
            "user_type"=>$currentUser['user_type']
        );

        if ($currentUser['user_type']=='doctor') {
            $doctorDetails = User::getUserDetails($currentUser['id']);
            // $resposeArray['image_url'] = $doctorDetails['image_url'];
            $resposeArray['state'] = $doctorDetails['state'];
            $resposeArray['state_code'] = $doctorDetails['state_code'];
            $resposeArray['speciality_name'] = $doctorDetails['speciality_name'];
            $resposeArray['broadcasting_id'] = $doctorDetails['broadcasting_id'];
            $resposeArray['speciality_id'] = $doctorDetails['speciality_id'];
            $resposeArray['overview'] = $doctorDetails['overview'];
        }

        if (!$currentUser['isConfirmed']) {
            BaseController::invalidToken($token);
            // something went wrong whilst attempting to encode the token
            return $this->responseMethod($requestData,'User is not confirmed yet',false,'VALIDATOR_FAILS');
        }

        $requestData['user_type'] = $currentUser['user_type'];
        $authKey = array('token'=>$token);
        $response = $this->responseMethod($resposeArray,'User LoggedIn Successfully',true,'OK',$authKey);

      return $response;
    }
}
