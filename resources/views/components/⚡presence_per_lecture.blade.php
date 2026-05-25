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

new #[Layout('layouts.layout-logado')] class extends Component
{
    #[Url]
    public $eventoSelecionado = '';

    public function mount()
    {
        if (!$this->eventoSelecionado) {
            $this->eventoSelecionado = null; // Ou algum valor padrão, se necessário
        }
    }

    #[Computed]
    public function presence_list()
    {
        //all_participants = Inscricao::where('sid', config('general.sematron_atual'));
        // Para obter os presentes basta procurar na coluna 'presence' os que contem o eid do evento selecionado
        $presentes = Inscricao::where('sid', config('general.sematron_atual'))
                        ->whereJsonContains('presence', $this->eventoSelecionado)
                        ->pluck('uid')
                        ->toArray();     
        
        return Userinfo::whereIn('uid', $presentes)->get('uid, name, email,tel');
        

    }
};
?>