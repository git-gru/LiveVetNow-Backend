<?php
namespace App\Http\Controllers;

use App\User;
use Validator;
use Illuminate\Http\Request;
// Extending baseController
use App\Http\Controllers\BaseController;
// Includes for token authentication
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
// authentication library
use Illuminate\Support\Facades\Auth;

class DoctorController extends BaseController
{

    /**
    * reason: To get list of doctors to show on recommended and 
    * doctors and others doctor screen.
    */
    public function getDoctorsForUser() {
        // $doctorsList = User::getDoctors();
        // print_r($doctorsList);die;
    }

}