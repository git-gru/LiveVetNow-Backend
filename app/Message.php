<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Message extends Authenticatable
{
    use Notifiable;
    // custom mentioned
    protected $table = 'messages';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'token','session_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        
    ];

    public function scopeGetMessages($query,$msgTo){
        $messageDetails = Message::where('msg_from','=',$msgTo)
                                ->orWhere('msg_to','=',$msgTo)
                                ->join('users as user_from','messages.msg_from','=','user_from.id')
                                ->join('users as user_to','messages.msg_to','=','user_to.id')
                                ->select('messages.msg_to','messages.msg_from','messages.message','user_from.name as msg_from_name','user_to.name as msg_to_name','user_from.image_url as from_image_url','user_to.image_url as to_image_url','user_from.host as host')
                                ->orderBy('messages.created_at','desc')
                                ->get();
        return $messageDetails;
    }
    // public function scopeGetMessages($query,$msgFrom,$msgTo){
    //     $messageDetails = Message::where(function($query) use ($msgFrom, $msgTo) {
    //                     $query->where('msg_from','=',$msgFrom)
    //                             ->where('msg_to','=',$msgTo);
    //                 })
    //                 ->orWhere(function($query) use ($msgFrom, $msgTo) {
    //                     $query->where('msg_from','=',$msgTo)
    //                             ->where('msg_to','=',$msgFrom);
    //                 })
    //                 ->orderBy('created_at')
    //                 ->get();
    //     return $messageDetails;
    // }
}

?>