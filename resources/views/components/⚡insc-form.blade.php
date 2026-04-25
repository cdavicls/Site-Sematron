<?php

use Livewire\Volt\Component;
use App\Models\Pack;
use App\Models\Event;
use App\Models\Inscricao;
use Livewire\Attributes\Computed;

new class extends Component
{
    public $selectedPackId = null;
    public $requiresAccommodation = false;
    public $shirtSize = null;
    public $selectedVisitId = null;
    public $selectedMinicourseId = null;

    #[Computed]
    public function packs()
    {
        return Pack::where('sid', env('ATUAL_SID'))->get();
    }

    #[Computed]
    public function activePack()
    {
        return $this->selectedPackId ? Pack::find($this->selectedPackId) : null;
    }

    #[Computed]
    public function visits()
    {


        $all_visits =  Event::where('type', 'viagem')->where('sid', env('ATUAL_SID'))->get();
        $available_visits = [];
        foreach ($all_visits as $event) {
            $subscribed_count = Inscricao::where('viagem', $event->eid)->count();
            if ($subscribed_count < $event->slots) {
                $available_visits[] = $event;
            } 
        }

        return $available_visits;
 
    }

    #[Computed]
    public function minicourses()
    {
        $all_minicourses = Event::where('type', 'minicurso')->where('sid', env('ATUAL_SID'))->get();
        $available_minicourses = [];
        foreach ($all_minicourses as $event) {
            $subscribed_count = Inscricao::where('minicurso', $event->eid)->count();
            if ($subscribed_count < $event->slots) {
                $available_minicourses[] = $event;
            }
        }

        return $available_minicourses;
    }

    #[Computed]
    public function totalPrice()
    {
        // Uso de () pois estamos chamando internamente na classe PHP
        $pack = $this->activePack(); 
        if (!$pack) return 0.00;
        
        $base = (float) str_replace(',', '.', $pack->preço);
        $addon = $this->requiresAccommodation ? 80.00 : 0.00;
        
        return $base + $addon;
    }

    public function updatedSelectedPackId()
    {
        // Uso de () pois estamos chamando internamente na classe PHP
        $pack = $this->activePack();
        if ($pack) {
            if (!$pack->visita) $this->selectedVisitId = null;
            if (!$pack->minicurso) $this->selectedMinicourseId = null;
            if (!$pack->kit) $this->shirtSize = null;
        }
    }

    public function submit()
    {
        $pack = $this->activePack();
        
        $this->validate([
            'selectedPackId' => 'required',
            'shirtSize' => ($pack && $pack->kit) ? 'required' : 'nullable',
        ], [
            'selectedPackId.required' => 'Por favor, selecione um pacote.',
            'shirtSize.required' => 'Escolha o tamanho da sua camiseta.',
        ]);

        $this->dispatch('post-registration');
    }
};
?>