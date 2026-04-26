<?php

namespace App\Http\Controllers;

use App\Models\Inscricao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class admController extends Controller
{
     public function showInscList()
    {  
        $resultados = DB::select('SELECT 
                                        userdata.uid, 
                                        userdata.pid,
                                        userinfos.name, 
                                        userinfos.email, 
                                        userinfos.cpf,
                                        userinfos.rg,
                                        userinfos.inst,
                                        userinfos.nusp,
                                        userinfos.tel,
                                        sales.code,
                                        sales.status as sales_status,
                                        userdata.status as status_usuario,
                                        events.name as viagem_usuario,
                                        pack.nome as pack_usuario
                                    FROM userdata
                                    INNER JOIN userinfos ON userdata.uid = userinfos.uid
                                    INNER JOIN sales ON userdata.pid = sales.pid
                                    INNER JOIN pack ON userdata.pack_id = pack.id
                                    LEFT JOIN events ON userdata.viagem = events.eid
                                    WHERE userdata.sid = 22');
    
        return view('adm_list_insc',['participantes' => $resultados]);
    }
}
