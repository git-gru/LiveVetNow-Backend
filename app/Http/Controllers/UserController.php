<?php
namespace App\Http\Controllers;

use App\User;
use App\Appointment;
use App\Transactiondetails;
use App\Doctortransdetail;
use App\Doctorsreview;
use App\Message;
use App\Paypal;
use Validator;
use Illuminate\Http\Request;
// Extending baseController
use App\Http\Controllers\BaseController;
// Includes for token authentication
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
// authentication library
use Illuminate\Support\Facades\Auth;

class UserController extends BaseController
{

    /**
    * reason: To get list of doctors to show on recommended and 
    * doctors and others doctor screen.
    */
    public function getDoctorsForUser() {
        $doctorsList = User::getConfirmedDoctors();
        $recommendedDoctorsList = User::getRecomendedConfirmedDoctors();

        foreach($doctorsList as $key=>$row) {
            $res = Doctorsreview::getLatestReview($row['id']);
            if (isset($res->review)) {
                $res = $res->toArray();
                $doctorsList[$key]['review_details'] = $res;
            } else {
                $doctorsList[$key]['review_details'] = array();
            }
        }

        foreach($recommendedDoctorsList as $key=>$row) {
            $res = Doctorsreview::getLatestReview($row['id']);
            if (isset($res->review)) {
                $res = $res->toArray();
                $recommendedDoctorsList[$key]['review_details'] = $res;
            } else {
                $recommendedDoctorsList[$key]['review_details'] = array();
            }
        }

        $returnData['recommended_doctors'] = $recommendedDoctorsList;
        $returnData['regular_doctors'] = $doctorsList;
        return $this->responseMethod($returnData,'List of Doctors',true,'OK');
    }
      /**
    * reason: To search doctors to show on recommended and 
    * doctors and others doctor screen.
    */
    public function searchDoctors(Request $request) {
        
        $searchParams = $request->only('doctor_name');

        $conditionArr = array(
                'doctor_name' => 'required'
        );
        $validationRes = $this->ValidatorMethod($searchParams,$conditionArr);

        if (!$validationRes['status']) {
            $errorDetails = $validationRes['error_details'];
            return $this->responseMethod($searchParams,'Please check your details.',false,'VALIDATOR_FAILS',$errorDetails);  
        } 

        $searchDoctors = User::searchDoctors($searchParams);
        return $this->responseMethod($searchDoctors,'List of Searched Doctors',true,'OK');
        
    }

    /**
    *   reason: get doctor availbility on particular date-time.
    */
    public function doctorAvailability(Request $request) {

        $searchParams = $request->only('doctor_id','appointment_datetime');

        $conditionArr = array(
                'doctor_id' => 'required',
                'appointment_datetime' => 'required'
        );
        $validationRes = $this->ValidatorMethod($searchParams,$conditionArr);

        if (!$validationRes['status']) {
            $errorDetails = $validationRes['error_details'];
            return $this->responseMethod($searchParams,'Please check your details.',false,'VALIDATOR_FAILS',$errorDetails);  
        }

        $doctor_id = $searchParams['doctor_id'];
        $appointment_datetime = $searchParams['appointment_datetime'];
        $appointment_enddatetime = ($appointment_datetime)+(60*30);

        $isDoctorAvailable = Appointment::getDoctorAvailability($doctor_id,$appointment_datetime,$appointment_enddatetime);

        if (count($isDoctorAvailable)  ) {
            $returnFirstApt = $isDoctorAvailable[0];
            $responseArr = array(
                'apt_datetime'=>$returnFirstApt['apt_datetime'],
                'apt_enddatetime'=>$returnFirstApt['apt_enddatetime'],
                'doctor_id'=>$returnFirstApt['doctor_id'],
                'user_id'=>$returnFirstApt['user_id']
            );
            return $this->responseMethod($responseArr,'Doctor is not available',false,'OK');  
        } else {
            return $this->responseMethod($isDoctorAvailable,'Doctor is available',true,'OK');  
        }

    }

    /**
    *   reason: get doctor availbility on particular date-time.
    */
    public function getThirtyMinDoctor() {

        $doctorsList = User::getDoctorsThirtyMin();
        $recommendedDoctorsList = User::getRecommendedDoctorsThirtyMin();
        $returnData['recommended_doctors'] = $recommendedDoctorsList;
        $returnData['regular_doctors'] = $doctorsList;
        return $this->responseMethod($returnData,'List of Doctors free for next 30 mins',true,'OK');
    }

    // List of transactions made
    public function getTransactions() {
        $userArray = $this->getUserDetailsFromAuth()->toArray();
        $transList = Appointment::getTransactionList($userArray['id']);
        $weekDates = BaseController::getWeekDates();
        $transTotal = Transactiondetails::getTotalEarnings($userArray['id']);
        $weekTransTotal = Transactiondetails::getWeeksTotalEarnings($userArray['id'],strtotime($weekDates['week_start_date']),strtotime($weekDates['week_end_date']));

        $responseArr = array(
            "total_earnings"=>$transTotal,
            "this_week_earnings"=>$weekTransTotal,
            'transaction_list'=>$transList
        );
        return $this->responseMethod($responseArr,'Transaction List',true,'OK');  
    }

    // List of messages
    public function getMessages() {
        $userArray = $this->getUserDetailsFromAuth()->toArray();
        $messageList = Message::getMessages($userArray['id']);

        return $this->responseMethod($messageList,'Messages List',true,'OK');  
    }

    // List of messages
    public function getPaypal($doctor_id) {
        $userArray = $this->getUserDetailsFromAuth()->toArray();
        $paypalDetails = Paypal::getDetailsById($doctor_id)->toArray();
        if (!empty($paypalDetails)) {
            return $this->responseMethod($paypalDetails,'Details fetched successfully',true,'OK');  
        } else {
            return $this->responseMethod(array(),'Unable to fetched records',false,'OK');  
        }
    }

    public function updatePaypal(Request $request) {
        $requestedData = $request->only('doctor_id','paypal_id');

        $conditionArr = array(
                'doctor_id' => 'required',
                'paypal_id' => 'required'
        );
        $validationRes = $this->ValidatorMethod($requestedData,$conditionArr);

        if (!$validationRes['status']) {
            $errorDetails = $validationRes['error_details'];
            return $this->responseMethod($requestedData,'Please check your details.',false,'VALIDATOR_FAILS',$errorDetails);  
        }

        $createArr = array(
            'paypal_id'=>$requestedData['paypal_id']
        );

        $userArray = $this->getUserDetailsFromAuth()->toArray();
        $isUserAlreadyExist = Paypal::getDetailsById($requestedData['doctor_id'])->toArray();
        if (!empty($isUserAlreadyExist)) {
        // print_r($isUserAlreadyExist);die;
            $paypalRes = Paypal::updateUser($requestedData['doctor_id'],$createArr);
        } else {
            $createArr['doctor_id'] = $requestedData['doctor_id'];
            $paypalRes = Paypal::forceCreate($createArr);
        }

        return $this->responseMethod($paypalRes,'Operation Performed successfully',true,'OK');  
    }
    // public function getMessages($message_from) {
    //     $userArray = $this->getUserDetailsFromAuth()->toArray();
    //     $messageList = Message::getMessages($message_from,$userArray['id']);

    //     return $this->responseMethod($messageList,'Messages List',true,'OK');  
    // }

    // List of messages
    public function sendMessages(Request $request) {
        
        $searchParams = $request->only('msg_to','message');

        $conditionArr = array(
                'msg_to' => 'required',
                'message' => 'required'
        );
        $validationRes = $this->ValidatorMethod($searchParams,$conditionArr);

        if (!$validationRes['status']) {
            $errorDetails = $validationRes['error_details'];
            return $this->responseMethod($searchParams,'Please check your details.',false,'VALIDATOR_FAILS',$errorDetails);  
        }
        $userArray = $this->getUserDetailsFromAuth()->toArray();
        $insertArr = array(
            'msg_from'=>$userArray['id'],
            'msg_to'=>$searchParams['msg_to'],
            'message'=>$searchParams['message']
        );
        $messageCreated = Message::forceCreate($insertArr);

        return $this->responseMethod($messageCreated,'Messages Sent',true,'OK');  
    }

    public function upgradeDoctor(Request $request) {
        
        $searchParams = $request->only('txn_id','txn_amount','txn_datetime','currency');

        $conditionArr = array(
                'txn_id' => 'required',
                'txn_amount' => 'required',
                'currency' => 'required',
                'txn_datetime' => 'required'
        );
        $validationRes = $this->ValidatorMethod($searchParams,$conditionArr);

        if (!$validationRes['status']) {
            $errorDetails = $validationRes['error_details'];
            return $this->responseMethod($searchParams,'Please check your details.',false,'VALIDATOR_FAILS',$errorDetails);  
        } 

        $userArray = $this->getUserDetailsFromAuth()->toArray();

        $transArr = array(
            'doctor_id'=>$userArray['id'],
            'txn_id'=>$searchParams['txn_id'],
            'txn_amount'=>$searchParams['txn_amount'],
            'txn_datetime'=>$searchParams['txn_datetime'],
            'currency'=>$searchParams['currency']
        );

        Doctortransdetail::forceCreate($transArr);

        $updatedArr = array(
            "is_premier"=>1
        );

        $userObj = User::findOrFail($userArray['id']);
        $userObj->update($updatedArr);

        return $this->responseMethod($userObj,'Doctor Upgraded Successfully',true,'OK');  
    }

    public function reviewDoctor(Request $request) {
        $requestedParams = $request->only('apt_id','doctor_id','review');

        $conditionArr = array(
                'apt_id' => 'required',
                'doctor_id' => 'required',
                'review'=>'required'
        );
        $validationRes = $this->ValidatorMethod($requestedParams,$conditionArr);

        if (!$validationRes['status']) {
            $errorDetails = $validationRes['error_details'];
            return $this->responseMethod($requestedParams,'Please check your details.',false,'VALIDATOR_FAILS',$errorDetails);  
        } 

        $userArray = $this->getUserDetailsFromAuth()->toArray();

        $insertArr = array(
            'user_id'=>$userArray['id'],
            'doctor_id'=>$requestedParams['doctor_id'],
            'apt_id'=>$requestedParams['apt_id'],
            'review'=>$requestedParams['review']
        );

        $reviewResponse = Doctorsreview::forceCreate($insertArr);

        return $this->responseMethod($reviewResponse,'Review added Successfully',true,'OK');  
    }

    public function doctorStatus($status) {
        $requestedParams = array('my_status'=>$status);

        $conditionArr = array(
                'my_status' => 'required'
        );
        $validationRes = $this->ValidatorMethod($requestedParams,$conditionArr);

        if (!$validationRes['status']) {
            $errorDetails = $validationRes['error_details'];
            return $this->responseMethod($requestedParams,'Please check your details.',false,'VALIDATOR_FAILS',$errorDetails);  
        }
        
        $userArray = $this->getUserDetailsFromAuth()->toArray();
        $updatedArr = array(
            'currently_available'=>($requestedParams['my_status']=='active') ? 1 : 0 
        );
        $isUpdated = User::updateAvailableStatus($userArray['id'],$updatedArr);
        return $this->responseMethod($isUpdated,'Availability Status Updated',true,'OK');  
    }

}