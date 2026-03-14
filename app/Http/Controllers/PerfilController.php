<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PerfilController extends Controller
{
    public function index() {

    $user = Auth::user();

    $participante = DB::table('userdata')->where('uid', $user->uid)->orderBy('pid', 'desc')->first();

    return view('perfil', compact('participante'));
    }
}
