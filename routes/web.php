<?php

use App\Http\Controllers\admController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\testeController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\CadastroController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\InscricaoController;
use App\Http\Controllers\PerfilController;
use App\Http\Middleware\AutenticacaoInscricao;
use Illuminate\Http\Request;
use App\Http\Controllers\GeneralController;
use App\Http\Middleware\GarantirUsuarioEhAdmin;
use App\Http\Middleware\GarantirNaoEstaInscrito;

Route::get('/home', [GeneralController::class, 'inicio'])->name('home');
Route::get('/', GeneralController::class . '@inicio')->name('inicio');

Route::get('/inicio', fn () => redirect('/'))->name('inicio.redirect');

Route::get('/cadastro' , fn () => redirect('cadastro/create'));

Route::resource('cadastro', CadastroController::class) ->only(['create', 'store']);

Route::get('/minicursos', GeneralController::class . '@minicursos')->name('minicursos');

Route::get('/visitas', GeneralController::class . '@visitas')->name('visitas');

Route::get('/palestras', GeneralController::class . '@palestras')->name('palestras');

Route::get('/login', fn () => view('login'))->name('login');

Route::post('/login', [LoginController::class, 'authenticate'])->name('login.autenticar');

Route::get('/maisSematron', fn () => view('maisSematron'))->name('maisSematron');
Route::get('/contato', fn () => view('contato'))->name('contato');
Route::get('/login', fn () => view('login'))->name('login');
Route::get('/cadastro', fn () => view('cadastro'))->name('cadastro');
Route::get('/teste', [testeController::class, 'show']);

Route::get('/esqueceu-a-senha', fn () => view('esqueceu-a-senha'))->name('esqueceu-a-senha');

Route::get('/34st3r3gg', fn () => view('easteregg'))->name('easteregg');
Route::get('/pao', fn () => view('pao'))->name('pao');

Route::get('/perfil', [PerfilController::class, 'index'])->middleware('auth')->name('perfil');

Route::get('/adm/list', [App\Http\Controllers\admController::class, 'showInscList'])->name('adm.list');

Route::post('/inscricoes', [InscricaoController::class, 'store']);

Route::get('/teste',[testeController::class,'show']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('/inscricao' , fn () => redirect('inscricao/create'))-> name('inscricao');
    Route::resource('inscricao', InscricaoController::class) ->only(['create', 'store']) ->middleware(GarantirNaoEstaInscrito::class);
    Route::get('/perfil', GeneralController::class . '@perfil')->name('perfil');

    //Módulo de Pagamento (Mercado Pago)
    Route::get('inscricoes/{inscricao:pid}/pagar', [PaymentController::class, 'checkout'])->name('pagar');
    
    //Retornos do Mercado Pago
    Route::get('/pagamento/sucesso', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/pagamento/erro', [PaymentController::class, 'failure'])->name('payment.failure');
    Route::get('/pagamento/pendente', [PaymentController::class, 'pending'])->name('payment.pending');
    Route::get('/pagamento/retomar', [PaymentController::class, 'resume_payment'])->name('payment.resume');
    
    Route::middleware([GarantirUsuarioEhAdmin::class]) -> group(function(){
        Route::get('/adm/list', [admController::class, 'showInscList'])->name('adm.list');
    });

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request.session()->invalidate();
        $request.session()->regenerateToken();
        return redirect('/inicio');
    })->name('logout');

    
    });
    
//rotas de teste, apagar quando entrar em produção
Route::get('/testar-pagamento', function () {
    // Redireciona para o checkout real
    return redirect()->route('pagar');
});

// Carrega configurações extras (se houver)
require __DIR__.'/settings.php';
