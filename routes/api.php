<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::middleware('authenticateapi')->post('reg.envister','AuthController@create');

//  Authentication Routes
Route::post('register','AuthController@create');
Route::post('login','AuthController@login');
Route::get('get_states','CommonController@getStates');

// Middleware to check token validation
Route::group(['middleware'=>'jwt.auth'],function() {
    // Common Controller
        Route::post('edit_details','CommonController@editDetails');
        Route::post('change_password','CommonController@changePassword');
        Route::post('update_image','CommonController@uploadImage');
        Route::get('get_speciality','CommonController@getSpecialityList');
        Route::post('invite_doctor','CommonController@inviteDoctor');
    // User Controller
        Route::get('get_doctor','UserController@getDoctorsForUser');
        Route::post('search_doctor','UserController@searchDoctors');
        Route::post('get_doctor_availability','UserController@doctorAvailability');
        Route::get('get_30_min_doctor','UserController@getThirtyMinDoctor');
        Route::get('get_transaction_list','UserController@getTransactions');
        Route::post('review_doctor','UserController@reviewDoctor');
        // Route::get('get_messages/{user_id}','UserController@getMessages');
        Route::get('get_messages','UserController@getMessages');
        Route::post('send_messages','UserController@sendMessages');
        Route::post('upgrade_doctor','UserController@upgradeDoctor');
        Route::post('update_paypal','UserController@updatePaypal');
        Route::get('get_paypal/{doctor_id}','UserController@getPaypal');
        Route::get('my_status/{status}','UserController@doctorStatus');
    // Veterinary Controller
        Route::post('add_pet','VeterinaryController@addPetOfUser');
        Route::get('find_pet/{apt_id?}','VeterinaryController@getPetsOfUser');
        Route::get('pet_type_list','VeterinaryController@getPetType');
        Route::delete('delete_pet/{pet_id}','VeterinaryController@deletePet');
    // Appointment Controller
        Route::post('appointment_status','AppointmentController@appointmentStatus');
        Route::post('book_appointment','AppointmentController@bookAppointment');
        Route::get('get_tokbox_details/{apt_id}','AppointmentController@getTokboxDetails');
        Route::get('appointment_list','AppointmentController@getAppointments');
        Route::post('add_apt_note','AppointmentController@addNoteAppointment');
        Route::get('appointment_history','AppointmentController@getAppointmentHistory');

});
