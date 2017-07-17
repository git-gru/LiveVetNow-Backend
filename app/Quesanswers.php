<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Quesanswers extends Authenticatable
{
    use Notifiable;
    // custom mentioned
    protected $table = 'ques_answer';
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

    public function scopeGetAnswersByAptId($query,$apt_id){
        $answers = Quesanswers::where('apt_id','=',$apt_id)
                    ->select('ques_id','answer')
                    ->get();
        return $answers;
    }
}
?>