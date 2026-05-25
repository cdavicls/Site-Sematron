<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Sale;
use App\Models\Inscricao;
use Illuminate\Validation\Rules\Exists;

class Userlogin extends Authenticatable
{
    protected $primaryKey = 'uid';
    public $timestamps = false;

    use HasFactory;
    protected $table = 'userlogins';
    protected $fillable = [
        'uid', 'username', 'password', 'salt',
    ];


    public function has_insc(){
        $sid = env('ATUAL_SID');
        return Inscricao::where('uid',$this->uid)->where('sid',$sid)->exists();
    }

    public function temInscricaoCompleta() 
    {
        $sid = env('ATUAL_SID');
        $inscs_pid = Inscricao::where('uid',$this->uid)->where('sid',$sid)->pluck('pid');
        if(!$inscs_pid->isEmpty()){
            $completed = Sale::whereIn('pid',$inscs_pid)->where('status','confirmed')->exists();
            return $completed;
        }
        return False;
    }
    public function isAdmin(){
        $sid = env('ATUAL_SID');
        $gid = Inscricao::where('uid',$this->uid)->where('sid',$sid)->value('gid');
        return $gid == 1;
        }
        
    public function test(){
        $func_logs = [
            'uid' => $this->uid,
            'sid' => env('ATUAL_SID'),
            'has_insc' => $this->has_insc(),
            'temInscricaoCompleta' => $this->temInscricaoCompleta(),
            'isAdmin' => $this->isAdmin()
            ];
        return $func_logs;
    }
}