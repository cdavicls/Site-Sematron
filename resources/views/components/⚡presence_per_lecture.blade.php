<?php

use App\Models\Userinfo;
use App\Models\Inscricao;
use App\Models\Eventos;
use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

new #[Layout('layouts.layout-logado')] class extends Component
{
    #[Url]
    public $eventoSelecionado = '';
    public $eventos;
    public $presentes = [];

    public function mount()
    {
        $this->eventoSelecionado = $this->eventoSelecionado ?: null;

        $this->eventos = Eventos::where('type', 'palestra')
                                ->where('sid', config('general.sematron_atual'))
                                ->get();

        if ($this->eventoSelecionado) {
            $this->carregarPresentes();
        }
    }

    public function updatedEventoSelecionado()
    {
        $this->carregarPresentes();
    }

    public function carregarPresentes()
    {
        if (!$this->eventoSelecionado) {
            $this->presentes = [];
            return;
        }

        $uids = [];
        $inscricoes = Inscricao::where('sid', config('general.sematron_atual'))
                               ->get(['uid', 'presence']);

        foreach ($inscricoes as $inscricao) {
            $presence_data = json_decode($inscricao->presence, true) ?? [];
            if (in_array($this->eventoSelecionado, $presence_data)) {
                $uids[] = $inscricao->uid;
            }
        }

        $this->presentes = Userinfo::whereIn('uid', $uids)
                                   ->get(['uid', 'name', 'email', 'tel'])
                                   ->toArray();
    }
};
?>