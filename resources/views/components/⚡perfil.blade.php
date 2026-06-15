<?php

use App\Models\Userinfo;
use App\Models\Inscricao;
use App\Models\Event;
use App\Models\Pack;
use App\Models\Sale;
use App\Models\Sematron;
use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CertificateController;

new #[Layout('layouts.layout-logado')] class extends Component
{
    #[Url]
    public $sidSelecionada = '';

    public function mount()
    {
        if (!$this->sidSelecionada) {
            $this->sidSelecionada = env('ATUAL_SID');
        }
    }

    #[Computed]
    public function usuario()
    {
        return Userinfo::where('uid', Auth::user()->uid)->first();
    }

    #[Computed]
    public function paymentStatus()
    {
        $user = Auth::user();
        if($user->has_insc()){
            if($user->temInscricaoCompleta()){
                return 'confirmed';
            }
            $user_pids = Inscricao::where('uid',$user->uid)->where('sid', config('general.sematron_atual'))->pluck('pid');
            $first_sale = Sale::whereIn('pid',$user_pids)->first();
            if($first_sale==null){return 'failed';}
            return $first_sale->status;
        }
        return 'n_sub';
    }

    #[Computed]
    public function edicoes()
    {   
        $participating_ids = Inscricao::where('uid', Auth::user()->uid)->pluck('sid');
        
        return Sematron::whereIn('sid', $participating_ids)
                        ->select('sid', 'name') 
                        ->get();
    }

    #[Computed]
    public function userAtual()
    {
        if (!$this->sidSelecionada) {
            return null;
        }

        $inscrito = Inscricao::where('uid', Auth::user()->uid)
                        ->where('sid', $this->sidSelecionada)
                        ->first();

        if (!$inscrito) {
            return null;
        }

        // Lógica de Presença
        $presence = is_string($inscrito->presence) 
            ? json_decode($inscrito->presence, true) 
            : $inscrito->presence;

        if (is_array($presence)){
            $totalPresenca = count($presence);
        } else {
            $totalPresenca = 0;
        }

        $n_palestras = Event::where('type', 'palestra')
                            ->where('sid', $inscrito->sid)
                            ->count();

        if($n_palestras > 0){
            $inscrito->presenca_calculada = ceil(($totalPresenca / $n_palestras) * 100);
        } else {
            $inscrito->presenca_calculada = 0;
        }

        // Busca Nomes
        $inscrito->minicurso_n = Event::where('eid', $inscrito->minicurso)->value('name') ?? 'Não selecionado';
        $inscrito->viagem_n    = Event::where('eid', $inscrito->viagem)->value('name') ?? 'Não selecionado';
        $inscrito->camiseta_n  = $inscrito->camiseta ? strtoupper($inscrito->camiseta) : 'N/A';
        $inscrito->pack_id_n   = Pack::where('id', $inscrito->pack_id)->value('nome') ?? 'Não disponível';

        // CORREÇÃO: Adicionando o nome_edicao para usar no título da view!
        $inscrito->nome_edicao = Sematron::where('sid', $this->sidSelecionada)->value('name');

        return $inscrito;
    }

    #[Computed]
    public function certificados()
    {
        if (!$this->userAtual) return [];

        // Instanciamos o controller para reutilizar a lógica centralizada.
        // Em projetos maiores, isso seria movido para um Service class.
        $certController = new CertificateController();
        return $certController->listForInscricao($this->userAtual->pid);
    }
};
?>