@extends(auth()->check() ? 'layouts.layout-logado' : 'layouts.layout-basico')

@section('title', 'Lista de Participantes - SEMATRON')

@section('content')


<div class="page-header">
    <h1 class="page-title">Inscritos</h1>
    <div class="page-subtitle">Visualização de dados do banco</div>
</div>
<section class="table-responsive">
    <table class="table table-striped cyber-table">
<thead>
    <tr>
        {{-- AGORA TEMOS 8 CABEÇALHOS PARA BATER COM OS 8 DADOS --}}
        <th>Nome Completo</th>
        <th>Status Conta</th>
        <th>Viagem</th>
        <th>RG</th>
        <th>CPF</th>
        <th>Instuição</th>
        <th>Nº USP</th>
        <th>Telefone</th>
        <th>Email</th>
        <th>UID</th>
        <th>PID</th>
        <th>Cód. Pagamento</th>
        <th>Status Venda</th>

    </tr>
</thead>
<tbody>
    @if(count($participantes) > 0)
        @foreach($participantes as $p)
        <tr>
            <td>{{ $p->name }}</td> 
            <td><span class="badge-status">{{ $p->status_usuario }}</span></td>
            <td>{{ $p->viagem_usuario ?? 'Nenhuma' }}</td>
            <td>{{ $p->rg }}</td>
            <td>{{ $p->cpf }}</td>
            <td>{{ $p->inst }}</td>
            <td>{{ $p->nusp }}</td>
            <td>{{ $p->tel }}</td>
            <td>{{ $p->email }}</td>                  
            <td><strong>{{ $p->uid }}</strong></td>
            <td><code>{{ $p->pid }}</code></td>
            <td><code>{{ $p->code }}</code></td>
            <td><span class="badge-status">{{ $p->sales_status }}</span></td>
        </tr>
        @endforeach
    @else
        <tr>
            <td colspan="8" class="text-center empty-state" style="padding: 2rem;">
                Nenhum registro encontrado no banco de dados.
            </td>
        </tr>
    @endif
</tbody>
</table>
</section>

{{-- Adicionado um espaçamento extra acima do botão para ele não colar na tabela --}}
<div style="margin-top: 2rem;">
    <a href="/" class="btn-back">VOLTAR PARA HOME</a>
</div>

@endsection