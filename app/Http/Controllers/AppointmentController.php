<?php
namespace App\Http\Controllers;

use App\User;
use App\Appointment;
use App\Quesanswers;
use App\Notes;
use App\Transactiondetails;
use Validator;
use Illuminate\Http\Request;
// Extending baseController
use App\Http\Controllers\BaseController;
// Includes for token authentication
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
// authentication library
use Illuminate\Support\Facades\Auth;
// video Calling library
use OpenTok\OpenTok;

class AppointmentController extends BaseController
{

    /**
    * reason: To get list of doctors to show on recommended and 
    * doctors and others doctor screen.
    */
    public function bookAppointment(Request $request) {
        $requestedParams = $request->only('doctor_id','vet_detail_id','request_type','apt_datetime','ques1','ques2','ques3');
        $questions = $request->only('ques1','ques2','ques3');

         $conditionArr = array(
                'doctor_id' => 'required',
                'vet_detail_id' => 'required',
                // 'appointment_datetime' => 'required',
                'request_type' => 'required',
                'ques1' => 'required',
                'ques2' => 'required',
                'ques3' => 'required'
        );
        $validationRes = $this->ValidatorMethod($requestedParams,$conditionArr);

        if (!$validationRes['status']) {
            $errorDetails = $validationRes['error_details'];
            return $this->responseMethod($requestedParams,'Please check your details.',false,'VALIDATOR_FAILS',$errorDetails);  
        }

        $userArray = $this->getUserDetailsFromAuth()->toArray();
        $doctorDetails = User::getUserBasicDetails($requestedParams['doctor_id']);

        $appointmentDateTime = (isset($requestedParams['apt_datetime'])) ? $requestedParams['apt_datetime'] : strtotime(date('Y-m-d H:i:s')) ;

        $appointmentEndDateTime = ($appointmentDateTime+(30*60));

        // $doctorStartTime = $doctorDetails->available_start_time+strtotime(date('Y-m-d'));
        // $doctorEndTime = $doctorDetails->available_end_time+strtotime(date('Y-m-d'));

        // Check if appointment time lies between doctor availabliity time.
        // if (!($doctorStartTime<=$appointmentDateTime  && $appointmentEndDateTime<=$doctorEndTime)) {
            
        //     return $this->responseMethod($requestedParams,'Doctor is not available',true,'VALIDATOR_FAILS');  
        // }

        // Check if doctor is available.
        if (!(isset($doctorDetails->currently_available)  && $doctorDetails->currently_available)) {
            
            return $this->responseMethod($requestedParams,'Doctor is not available',true,'VALIDATOR_FAILS');  
        }
        $createArr = array(
            "user_id"=>$userArray['id'],
            "doctor_id"=>$requestedParams['doctor_id'],
            "vet_det_id"=>$requestedParams['vet_detail_id'],
            "apt_datetime"=>$appointmentDateTime,
            'apt_enddatetime'=>$appointmentEndDateTime,
            "request_type"=>$requestedParams['request_type'],
            "created_at"=>date('Y-m-d H:i:s')
        );
        $appointmentRes = Appointment::forceCreate($createArr);
        $aptId = $appointmentRes['id'];
        // $allowWindow = $appointmentRes['allowed_window'];
        // $updateArr = array(
        //     'apt_enddatetime'=>($appointmentDateTime+(30*60))
        // );

        // $updatedResponse = Appointment::updateAppointment($aptId,$updateArr);
        $answersArr = array();
        foreach($questions as $key=>$row) {
            $quesIs = str_replace("ques","",$key);
            $temp = array(
                "ques_id"=>$quesIs,
                "answer"=>$row,
                "apt_id"=>$appointmentRes['id'],
                "created_at"=>date('Y-m-d H:i:s')
            );
            array_push($answersArr, $temp);
            Quesanswers::forceCreate($temp);
        }

        //  for mobile developer ease.
        $appointmentRes['appointment_id'] = $appointmentRes['id'];
        return $this->responseMethod($appointmentRes,'Appointment Request processed successfully',true,'OK');

    }

    
    /**
    * reason: Get Tokbox details.
    */
    public function getTokboxDetails($apt_id) {
        $userDetails = BaseController::getUserDetailsFromAuth()->toArray();
        $tokBoxKey = env('TOKBOX_KEY');

        if ($userDetails['user_type']=='user') {
            $tokBoxSecret = env('TOKBOX_SECRET');
            // checking if session already exist
            $session_details = Appointment::getSessionByApptId($apt_id,$userDetails['id']);
            if ( isset($session_details->token) ) {
                $session_details = $session_details->toArray();
                    $token = $session_details['token'];
                    $sessionId = $session_details['session_id'];

                $returnData= array('session_id'=>$sessionId,'token'=>$token, 'api_key'=>$tokBoxKey );
                $message = 'Session Created';
                $status = true;
                $statusCode = 'OK';
            } else {
                
                // Creating opentok object
                $opentok = new OpenTok($tokBoxKey, $tokBoxSecret);
                // creating session from object
                $session = $opentok->createSession();
                // Fetching token from session
                $token = $session->generateToken();
                // Fetching session id
                $sessionId = $session->getSessionId();
                
                $updateDetails = array(
                    'token'=>$token,
                    'session_id'=>$sessionId
                );
                $aptObj = Appointment::findOrFail($apt_id);
                $aptObj->update($updateDetails);

                
                $returnData= array('session_id'=>$sessionId,'token'=>$token, 'api_key'=>$tokBoxKey );
                $message = 'Session Created';
                $status = true;
                $statusCode = 'OK';
            }

        } else if ($userDetails['user_type']=='doctor') {

            $session_details = Appointment::getSessionByApptId($apt_id,$userDetails['id']);

                if ( isset($session_details->token) ) {
                    $session_details = $session_details->toArray();
                    $message = 'Session details found';
                    $returnData= array('session_id'=>$session_details['session_id'],'token'=>$session_details['token'], 'api_key'=>$tokBoxKey );
                    $status = true;
                    $statusCode = 'OK';
                } else {
                    $returnData = array();
                    $message = 'Unable to fetch any session';
                    $status = false;
                    $statusCode = 'OK';
                }
        }
        return $this->responseMethod($returnData,$message,$status,$statusCode);
    }

    /** 
    *   
    */
    public function getAppointments() {
        $userDetails = BaseController::getUserDetailsFromAuth()->toArray();
        $appointmentDetails = Appointment::getApoointmentDetails($userDetails['id'])->toArray();

        // get answers of questions
        foreach($appointmentDetails as $key=>$row) {
            $apt_id = $row['apt_id'];
            $answers = Quesanswers::getAnswersByAptId($apt_id)->toArray();
            $appointmentDetails[$key]['ques_answers'] = $answers;
        }

        return $this->responseMethod($appointmentDetails,'Appointment List',true,'OK');
    }

    /**
    * Creator: Bhupinder Garg
    * Reason: To update apointment status.
    * 
    */
    public function appointmentStatus(Request $request) {
        $requestedParams = $request->all();

         $conditionArr = array(
                'apt_id' => 'required',
                'apt_status' => 'required'
        );

        if (isset($requestedParams['apt_status']) && ($requestedParams['apt_status']=='confirmed')) {
            $conditionArr['txn_id']='required';
        }

        $validationRes = $this->ValidatorMethod($requestedParams,$conditionArr);

        if (!$validationRes['status']) {
            $errorDetails = $validationRes['error_details'];
            return $this->responseMethod($requestedParams,'Please check your details.',false,'VALIDATOR_FAILS',$errorDetails);  
        }
        // $userDetails = $this->getUserDetailsFromAuth()->toArray();
        // print_r($userDetails);die;
        // $updatedResponse = Appointment::getApoointmentByUserId($userDetails['id']);
        // if (!count($updatedResponse)) {
        //     return $this->responseMethod('','This user do not have any appointment scheduled',false,'VALIDATOR_FAILS');  
        // }

        $aptId = $requestedParams['apt_id'];
        if (($requestedParams['apt_status']=='confirmed')) {
            $txn_id = $requestedParams['txn_id'];
            $txn_amount = $requestedParams['txn_amount'];
            $txn_datetime = $requestedParams['txn_datetime'];
            $currency = $requestedParams['currency'];
            $transactionArr = array(
                'apt_id'=>$aptId,
                'txn_id'=>$txn_id,
                'txn_amount'=>$txn_amount,
                'txn_datetime'=>$txn_datetime,
                'currency'=>$currency
            );
            Transactiondetails::forceCreate($transactionArr);
        }
        $updateArr = array(
            'app_status'=>$requestedParams['apt_status']
        );
        $updatedResponse = Appointment::updateAppointment($aptId,$updateArr);
        return $this->responseMethod($updatedResponse,'Appointment Status updated',true,'OK');
    }

    public function addNoteAppointment(Request  $request) {
        $requestedParams = $request->only('apt_id','note');
        
         $conditionArr = array(
                'apt_id' => 'required',
                'note' => 'required'
        );
        $validationRes = $this->ValidatorMethod($requestedParams,$conditionArr);

        if (!$validationRes['status']) {
            $errorDetails = $validationRes['error_details'];
            return $this->responseMethod($requestedParams,'Please check your details.',false,'VALIDATOR_FAILS',$errorDetails);  
        }

        $createArr = array(
            'apt_id'=>$requestedParams['apt_id'],
            'description'=>$requestedParams['note']
        );
        $noteCreated = Notes::forceCreate($createArr);
        return $this->responseMethod($noteCreated,'Note Added successfully',true,'OK');
    }

    public function getAppointmentHistory() {
        $userDetails = BaseController::getUserDetailsFromAuth()->toArray();
        $appointmentDetails = Appointment::getAppointmentHistory($userDetails['id']);

        return $this->responseMethod($appointmentDetails,'Appointment History',true,'OK');
    }
}