
        <section class="Palavra-Atividades_Traco-Laranja Palavra-Programacao">
            <h1 class="Palavra-Atividades">Palestras</h1>
            <div class="traco-laranja"></div>
        </section>

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
        </section>


