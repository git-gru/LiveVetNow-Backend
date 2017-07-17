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
// video Calling library
use OpenTok\OpenTok;

class TokboxController extends BaseController
{

    /**
    * reason: Get Tokbox details.
    */
    public function getTokboxDetails() {
        $tokBoxKey = env('TOKBOX_KEY');
        $tokBoxSecret = env('TOKBOX_SECRET');
        // Creating opentok object
        $opentok = new OpenTok($tokBoxKey, $tokBoxSecret);
        // creating session from object
        $session = $opentok->createSession();
        // Fetching token from session
        $token = $session->generateToken();
        // Fetching session id
        $sessionId = $session->getSessionId();

        $returnData= array('session_id'=>$sessionId,'token'=>$token, 'api_key'=>$tokBoxKey );

        return $this->responseMethod($returnData,'',true,'OK');
    }

}