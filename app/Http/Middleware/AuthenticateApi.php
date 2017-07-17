<?php

namespace App\Http\Middleware;

use Closure;
// Includes for token authentication
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Helpers\ResponseClass;

class AuthenticateApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {

                // return response()->json(['user_not_found'], 404);
                return ResponseClass::Prepare_Response('','User Not Found',true,401);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return ResponseClass::Prepare_Response('','User Token Expired',true,$e->getStatusCode());
            // return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return ResponseClass::Prepare_Response('','Invalid User Token',true,$e->getStatusCode());
            // return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return ResponseClass::Prepare_Response('','User Token Missing',true,$e->getStatusCode());
            // return response()->json(['token_absent'], $e->getStatusCode());

        }

        return $next($request);
    }
}
