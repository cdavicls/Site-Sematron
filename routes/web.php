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
use App\Http\Controllers\LeitorController;
use Illuminate\Http\Request;
use App\Http\Controllers\GeneralController;
use App\Http\Middleware\GarantirUsuarioEhAdmin;
use App\Http\Middleware\GarantirNaoEstaInscrito;

Route::get('/home', [GeneralController::class, 'inicio'])->name('home');
Route::get('/', GeneralController::class . '@inicio')->name('inicio');

Route::get('/inicio', fn () => redirect('/'))->name('inicio.redirect');


Route::get('/inscricao' , fn () => redirect('inscricao/create'));
Route::resource('inscricao', InscricaoController::class) ->only(['create', 'store']) ->middleware(AutenticacaoInscricao::class);

Route::get('/cadastro' , fn () => redirect('cadastro/create'));

Route::resource('cadastro', CadastroController::class) ->only(['create', 'store']);

Route::get('/minicursos', GeneralController::class . '@minicursos')->name('minicursos');

Route::get('/visitas', GeneralController::class . '@visitas')->name('visitas');

Route::get('/palestras', GeneralController::class . '@palestras')->name('palestras');

Route::post('/presenca', [LeitorController::class, 'registrar']) ->name('registrar.presenca')->middleware('auth');

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
               Route::get('/adm/list', [admController::class, 'showInscList'])->name('adm.list');
        Route::get('adm/palestra_list', [admController::class, 'showPalestraList'])->name('adm.palestra_list');
        //Route::get('/adm/leitor', [LeitorController::class, 'index'])->name('adm.leitor_presenca')
        Route::get('/adm/cb185620a1b45a7a496e26bc79a4d0a093ccbc0eaa9a44da9bdcd7e4c470a80d', [LeitorController::class, 'index'])->name('adm.leitor_presenca1');
        Route::get('/adm/5ad5698e79dc1c0fa506f96bbaba99cafba00eacfd430115413b9ddd02f1d938', [LeitorController::class, 'index'])->name('adm.leitor_presenca2');
        Route::get('/adm/037b90bb37900a8b84977458526f823da4de1985628938af54ab6f9cfe96d3c0', [LeitorController::class, 'index'])->name('adm.leitor_presenca3');
        Route::get('/adm/5d2d38e1ec0829df48e6f2cf9b0bb40e581d5f96f87d25ae249a58c24cbb08f8', [LeitorController::class, 'index'])->name('adm.leitor_presenca4');
        
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
