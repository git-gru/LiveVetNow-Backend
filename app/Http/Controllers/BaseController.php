<?php

namespace App\Http\Controllers;

use Validator;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseClass;
// Includes for token authentication
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Mail;
// authentication library
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{

      static function getUserDetailsFromToken()  {
        $user = JWTAuth::parseToken()->toUser();
        return $user; // returning user object
      }

      static function getWeekDates()  {
        $monday = strtotime("last monday");
        $monday = date('w', $monday)==date('w') ? $monday+7*86400 : $monday;
        
        $sunday = strtotime(date("Y-m-d",$monday)." +6 days");
        
        $this_week_sd = date("Y-m-d",$monday);
        $this_week_ed = date("Y-m-d",$sunday);
        return array('week_start_date'=>$this_week_sd,'week_end_date'=>$this_week_ed);
      }

      static function getUserDetailsFromAuth()  {
        $user = Auth::User();
        return $user; // returning user array
      }

      static function invalidToken($token)  {
        JWTAuth::invalidate($token);
      }

      Public function ValidatorMethod($requestParam,$conditionArr) {
        $validator = Validator::make($requestParam, $conditionArr);

          $status = true;
          $errorsData = array();
          if ($validator->fails()) {
              $status = false;
              $errorsData = $validator->errors()->all();
          }
          $responseArray = array('status'=>$status,'error_details'=>$errorsData);
          return $responseArray;
      }

      public function responseMethod($data,$message,$status,$statusMsg,$extras=array()) {

        $defaultMessage = '';
        switch($statusMsg) {
            case 'OK':  $defaultMessage = "Request Completed Successfully";
                        $statusCode = 200;
                        break;

            case 'UNAUTHORIZED':    $defaultMessage = "Unauthorized Request";
                                    $statusCode = 401;
                                    break;

            case 'VALIDATOR_FAILS':    $defaultMessage = "Unprocessable Entity";
                                 $statusCode = 422; //Unprocessable entity
                                 break;

            case 'INTERNAL_SERVER_ERROR':    $defaultMessage = "Server Not Responding";
                                 $statusCode = 500; //Internal Server Error
                                 break;

            case 'BAD_REQUEST':    $defaultMessage = "Bad request";
                                 $statusCode = 400; //Bad Request
                                 break;
                                    
            default:    $defaultMessage = "Request Type Not Found";
                        $statusCode = 200;
                        break; 
        }

        if (empty($message)) {
            $message = $defaultMessage;
        }

        return ResponseClass::Prepare_Response($data,$message,$status,$statusCode,$extras);
      }

      
      static function sendEmail($view,$messageFrom,$messageTo,$subject,$viewData,$extraParams = array())  {

        $messageFrom = (!empty($messageFrom)) ? $messageFrom : "support@livevetnow.com";

        Mail::send(['text'=>$view], $viewData, function($message) use ($messageTo,$messageFrom,$subject) {
            $message->to($messageTo)->subject($subject);
            $message->from($messageFrom);
        });

        if (Mail::failures()) {
            return false;
        } else {
          return true;
        }
      }

}
