<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\Event;
use \App\Models\Userinfo;
use \App\Models\Pack;
use \App\Models\Inscricao;
use Illuminate\Support\Facades\Auth;

class GeneralController extends Controller
{
    public function inicio()
    {
        $n_visitas = Event::where([
            ['type', 'viagem'],
            ['sid', config('general.sematron_atual')]
        ])->get()->count();
        $n_mcursos = Event::where([
            ['type', 'minicurso'],
            ['sid', config('general.sematron_atual')]
        ])->get()->count();
        $n_palestras = Event::where([
            ['type', 'palestra'],
            ['sid', config('general.sematron_atual')]
        ])->where('slots', '>', 0)->get()->count();
        return view('inicio', ['n_visitas' => $n_visitas, 'n_mcursos' => $n_mcursos, 'n_palestras' => $n_palestras]);
    }
    public function minicursos()
    {
    $all_minicursos =  Event::where('type', 'minicurso')->where('sid', env('ATUAL_SID'))->get();
    $available_minicursos = [];
    foreach ($all_minicursos as $event) {
        $subscribed_count = Inscricao::where('minicurso', $event->eid)->count();
        if ($subscribed_count < $event->slots) {
            $event->slots = $event->slots - $subscribed_count; // Atualiza os slots disponíveis
            $available_minicursos[] = $event;
        }
    }

    return view('minicursos', ['mcursos' => $available_minicursos]);
    }
    public function visitas()
    {
    $all_visits =  Event::where('type', 'viagem')->where('sid', env('ATUAL_SID'))->get();
    $available_visits = [];
    foreach ($all_visits as $event) {
        $subscribed_count = Inscricao::where('viagem', $event->eid)->count();
        if ($subscribed_count < $event->slots) {
            $event->slots = $event->slots - $subscribed_count; // Atualiza os slots disponíveis
            $available_visits[] = $event;
        }
    }

    return view('visitas', ['visitas' => $available_visits]);
    }

    public function palestras()
    {
        $palestras = Event::where([
            ['type', 'palestra'],
            ['sid', config('general.sematron_atual')]
        ])->get();
        return view('palestras', ['palestras' => $palestras]);
}

    public function perfil(Request $request)
    {
        function number2roman($num,$isUpper=true) {
            $n = intval($num);
            $res = '';

            /*** roman_numerals array ***/
            $roman_numerals = array(
                'M' => 1000,
                'CM' => 900,
                'D' => 500,
                'CD' => 400,
                'C' => 100,
                'XC' => 90,
                'L' => 50,
                'XL' => 40,
                'X' => 10,
                'IX' => 9,
                'V' => 5,
                'IV' => 4,
                'I' => 1
            );

            foreach ($roman_numerals as $roman => $number)
            {
                /*** divide to get matches ***/
                $matches = intval($n / $number);

                /*** assign the roman char * $matches ***/
                $res .= str_repeat($roman, $matches);

                /*** substract from the number ***/
                $n = $n % $number;
            }

            /*** return the res ***/
            if($isUpper) return $res;
            else return strtolower($res);
        }




        // Informaçoes do usuário logado
        $auth = Auth::user();
        if (!$auth) {
            return redirect('/login')->with('error', 'Por favor, faça login para acessar seu perfil.');
        }
        $usuario = Userinfo::where('uid', $auth->uid)
                            ->first();
        // Todas as sematorns que o usuario já participou
        $users = Inscricao::where('uid', $auth->uid)
                            ->get();
        //pega as informações de cada sematron individualmente
        foreach ($users as $user) {
            if ($user) {

                $presence = is_string($user->presence) 
                    ? json_decode($user->presence, true) 
                    : $user->presence;

                $totalPresenca = is_array($presence) ? count($presence) : 0;

                $n_palestras = Event::where([
                    ['type', 'palestra'],
                    ['sid', $user->sid]
                ])->get()->count();

                $user->presenca = ($n_palestras > 0) ? ceil($totalPresenca / $n_palestras * 100) : 0;

                $user->minicurso_n = Event::where('eid', $user->minicurso)->first()->name ?? 'Não disponível';
                $user->viagem_n = Event::where('eid', $user->viagem)->first()->name ?? 'Não disponível';
                $user->camiseta_n = strtoupper($user->camiseta) ?? 'Não disponível';
                $user->pack_id_n = Pack::where('id', $user->pack_id)->first()->nome ?? 'Não disponível';
                $user->romano = number2roman($user->sid);
                

            } else {
                // Se usuário não existe, define como 0
                $user->presenca = 0;
                $user->minicurso_n = null;
                $user->viagem_n = null;
                $user->camiseta_n = null;
                $user->pack_id_n = null;
            }
        }

            //Seleciona as informações da sematron selecionada
            $sidSelecionada = $request->input('sid');
            
            if (!$sidSelecionada && $users->isNotEmpty()) {
                $sidSelecionada = $users->first()->sid;
            }

            $user_atual = $users->where('sid', $sidSelecionada)->first();

        
        return view('perfil', ['users' => $users, 'usuario' => $usuario, 'sidSelecionada' => $sidSelecionada, 'user_atual' => $user_atual]);
}
}
