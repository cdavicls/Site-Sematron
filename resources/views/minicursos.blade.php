@extends(auth()->check() ? 'layouts.layout-logado' : 'layouts.layout-basico')          <!--IMPORTANDO LAYOUT DA PASTA LAYOUT-->

@section('title', 'Minicursos')        <!--AQUI TU BOTA O NEGOCIO QUE APARECE NA ABA LÁ EM CIMA-->

@section('content')                         <!--AQUI COMEÇA O CONTEÚDO ESPECÍFICO DA PÁGINA-->
    <!--SLK NUM COMPENSA FAZER DNV-->
    <section class="Palavra-Atividades_Traco-Laranja Palavra-Programacao">
        <h1 class="Palavra-Atividades">Minicursos</h1>
        <div class="traco-laranja"></div>
    </section>





    <section class="espaco-no-topo margem-esquerda">
        <div class="imagem-de-fundo">
                
            @foreach ($mcursos as $mcurso)
            <div class="borda-minicursos">
                <div class="texto-na-esquerda">
                    <h1 class="nome-do-minicurso"> Minicurso - {{ $mcurso->name }} </h1>
                    <h1 class="horarios-minicursos">Dia: {{date('d/m', strtotime($mcurso->start))}} • Hora: {{date('H:i', strtotime($mcurso->start))}} • Vagas Disponíveis: {{ $mcurso->slots}}</h1>
                    <h1 class="horarios-minicursos">Descrição: {{ $mcurso->info }}</h1>
                    </div>
                <a href="/inscricao" class="botao-inscrever">Inscrever</a>
            </div>
            @endforeach


        </div>
    </section>
@endsection                                 <!--AQUI ACABA O CONTEÚDO ESPECÍFICO DA PÁGINA-->