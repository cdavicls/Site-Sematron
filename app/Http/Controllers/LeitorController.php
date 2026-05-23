<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Eventos;
use App\Models\Inscricao;

class LeitorController extends Controller
{
    public function index()
    {
        $eventos = Eventos::where('type', 'palestra')->where('sid', 22)->get();

        return view('leitor', compact('eventos'));
    }

    public function registrar(Request $request)
    {
        $pid = $request->pid;
        $eid = $request->eid;

        $inscricao = Inscricao::where('pid', $pid)->first();

        if(!$inscricao){
            return response()->json(['message' => 'Participante não encontrado....'], 404);
        }

        $presence = json_decode($inscricao->presence ?? '[]', true);

        if(!in_array($eid, $presence)){
            $presence[] = $eid;
        }

        $inscricao->presence = json_encode($presence);
        $inscricao->save();

        return response()->json(['message' => 'Presença registrada!!']);
    }
}
