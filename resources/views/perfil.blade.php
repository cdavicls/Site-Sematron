@extends(auth()->check() ? 'layouts.layout-logado' : 'layouts.layout-basico')

@section('title', 'Perfil')                 <!--AQUI TU BOTA O NEGOCIO QUE APARECE NA ABA LÁ EM CIMA-->

@section('content')
    @livewire('perfil')
@endsection