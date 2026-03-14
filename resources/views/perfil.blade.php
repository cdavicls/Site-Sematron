@extends(auth()->check() ? 'layouts.layout-logado' : 'layouts.layout-basico')

@section('title', 'Perfil')

@section('content')
    @livewire('perfil')
@endsection
