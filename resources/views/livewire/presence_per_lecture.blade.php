<div>
    <section class="Palavra-Atividades_Traco-Laranja Palavra-Programacao">
        <h1 class="Palavra-Atividades">Palestras</h1>
        <div class="traco-laranja"></div>
    </section>

    <style>
        /* Estilos do Scanner e UI */
        .scanner-wrapper {
            position: relative;
            width: 100%;
            max-width: 500px;
            border-radius: 12px;
            overflow: hidden;
            background: #000;
            border: 2px solid var(--laranja);
        }

        #reader {
            width: 100%;
        }

        /* Toast Notifications */
        #toast {
            visibility: hidden;
            min-width: 250px;
            margin-left: -125px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 16px;
            position: fixed;
            z-index: 1000;
            left: 50%;
            bottom: 30px;
            font-size: 17px;
            transition: visibility 0s, opacity 0.5s linear;
            opacity: 0;
        }

        #toast.show {
            visibility: visible;
            opacity: 1;
        }

        #toast.success { background-color: #28a745; }
        #toast.error { background-color: #dc3545; }
        #toast.warning { background-color: #ffc107; color: #000; }

        /* Select de Eventos */
        #seletor-evento {
            width: 100%;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 16px;
            background-color: #111;
            color: white;
            border: 2px solid var(--laranja);
            cursor: pointer;
        }
    </style>


    <section class="espaco-no-topo margem-esquerda">
        <div id="lista-eventos">
            @if(count($eventos) > 0)
                <select id="seletor-evento" wire:model.live="eventoSelecionado" required>
                    <option value="">Selecione a palestra...</option>
                    @foreach($eventos as $evento)
                        <option value="{{ $evento->eid }}">
                            {{ $evento->name }} ({{ \Carbon\Carbon::parse($evento->start)->format('H:i') }} às {{ \Carbon\Carbon::parse($evento->end)->format('H:i') }})
                        </option>
                    @endforeach
                </select>
            @else
                <p style="color:#888; text-align:center; margin:20px 0;">
                    Nenhum evento disponível no momento.
                </p>
            @endif
        </div>

         @if($eventoSelecionado)
        <section class="table-responsive" style="margin-top: 2rem;">
            <table class="table table-striped cyber-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Telefone</th>
                        <th>UID</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($presentes) > 0)
                        @foreach($presentes as $p)
                            <tr>
                                <td>{{ $p['name'] }}</td>
                                <td>{{ $p['email'] }}</td>
                                <td>{{ $p['tel'] }}</td>
                                <td><strong>{{ $p['uid'] }}</strong></td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="text-center empty-state" style="padding: 2rem;">
                                Nenhum participante registrado para esta palestra.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </section>
    @endif
    </section>
</div>