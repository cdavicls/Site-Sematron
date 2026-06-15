@extends(auth()->check() ? 'layouts.layout-logado' : 'layouts.layout-basico')

@section('title', 'Presença por Palestra')

@section('content')
    @livewire('presence_per_lecture')
@endsection