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
                                        userdata.camiseta as camiseta,
                                        sales.status as sales_status,
                                        userdata.status as status_usuario,
                                        viagem.name as viagem_usuario,
                                        minicurso.name as minicurso,
                                        pack.nome as pack_usuario
                                    FROM userdata
                                    INNER JOIN userinfos ON userdata.uid = userinfos.uid
                                    INNER JOIN sales ON userdata.pid = sales.pid
                                    INNER JOIN pack ON userdata.pack_id = pack.id
                                    LEFT JOIN events AS viagem ON userdata.viagem = viagem.eid
                                    LEFT JOIN events AS minicurso ON userdata.minicurso = minicurso.eid
                                    WHERE userdata.sid = 22');
    
        return view('adm_list_insc',['participantes' => $resultados]);
    }
}
