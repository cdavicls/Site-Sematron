@extends(auth()->check() ? 'layouts.layout-logado' : 'layouts.layout-basico')

@section('title', 'Leitor de QR Code')

@section('content')

<section class="Palavra-Atividades_Traco-Laranja Palavra-Programacao">
    <h1 class="Palavra-Atividades">Leitor</h1>
    <div class="traco-laranja"></div>
</section>

<section class="espaco-no-topo margem-esquerda">

    {{-- ===================== LISTA DE EVENTOS ===================== --}}
    <div id="lista-eventos">
        @forelse($eventos as $evento)
            <div class="borda-visitas" data-eid="{{ $evento->eid }}">
                <div class="texto-na-esquerda">
                    <h1 class="nome-da-visita">{{ $evento->name }}</h1>
                    <h1 class="horarios-visitas">
                        {{ \Carbon\Carbon::parse($evento->start)->format('H:i') }}
                        •
                        {{ \Carbon\Carbon::parse($evento->end)->format('H:i') }}
                    </h1>
                </div>
                <button class="botao-inscrever botao-selecionar">Selecionar</button>
            </div>
        @empty
            <p style="color:#888; text-align:center; margin:20px 0;">
                Nenhum evento disponível no momento.
            </p>
        @endforelse
    </div>

    {{-- ===================== SCANNER ===================== --}}
    <h1 class="Palavra-Atividades" style="margin-top: 40px;">
        Escaneie o QR Code do participante
    </h1>

    {{-- Toast de feedback (substitui alert()) --}}
    <div id="toast" role="alert" aria-live="assertive"></div>

    <div style="display:flex; justify-content:center; margin-top:20px;">
        <div class="scanner-wrapper">
            <div id="reader"></div>
            <div id="scanner-overlay" class="scanner-overlay hidden">
                <div id="overlay-icon"></div>
                <div id="overlay-msg"></div>
            </div>
        </div>
    </div>

</section>

{{-- ===================== ESTILOS ===================== --}}
<style>
    /* ---- Seleção de evento ---- */
    .borda-visitas {
        transition: opacity .2s, box-shadow .2s;
    }
    .borda-visitas.inativo {
        opacity: .45;
    }
    .borda-visitas.ativo {
        opacity: 1;
        box-shadow: 0 0 0 2px #f97316;
    }
    .botao-selecionar.selecionado {
        background-color: #f97316;
        color: #fff;
        cursor: default;
    }

    /* ---- Wrapper do scanner ---- */
    .scanner-wrapper {
        width: 300px;
        height: 300px;
        border-radius: 30px;
        overflow: hidden;
        position: relative;
        background: #111;
    }

    #reader {
        width: 100%;
        height: 100%;
    }

    /* Força o vídeo a preencher o box sem distorcer */
    #reader video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }

    /* Esconde elementos desnecessários que a lib injeta */
    #reader img,
    #reader select,
    #reader button,
    #reader span,
    #reader br,
    #reader__dashboard,
    #reader__dashboard_section,
    #reader__filescan_input {
        display: none !important;
    }

    /* ---- Overlay de resultado sobre o scanner ---- */
    .scanner-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border-radius: 30px;
        font-weight: 700;
        font-size: 1rem;
        gap: 8px;
        transition: opacity .25s;
    }
    .scanner-overlay.hidden  { display: none; }
    .scanner-overlay.sucesso { background: rgba(34, 197, 94, .88); color: #fff; }
    .scanner-overlay.erro    { background: rgba(239, 68,  68, .88); color: #fff; }
    .scanner-overlay.aviso   { background: rgba(234,179,   8, .88); color: #1a1a1a; }

    #overlay-icon { font-size: 2.5rem; line-height: 1; }

    /* ---- Toast global ---- */
    #toast {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(80px);
        background: #1e1e1e;
        color: #fff;
        padding: 12px 24px;
        border-radius: 999px;
        font-size: .95rem;
        font-weight: 600;
        white-space: nowrap;
        box-shadow: 0 4px 20px rgba(0,0,0,.35);
        opacity: 0;
        transition: opacity .3s, transform .3s;
        z-index: 9999;
        pointer-events: none;
    }
    #toast.visivel        { opacity: 1; transform: translateX(-50%) translateY(0); }
    #toast.toast-sucesso  { border-left: 4px solid #22c55e; }
    #toast.toast-erro     { border-left: 4px solid #ef4444; }
    #toast.toast-aviso    { border-left: 4px solid #eab308; }
</style>

{{-- ===================== SCRIPT ===================== --}}
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
(function () {
    'use strict';

    /* ------------------------------------------------------------------ */
    /*  Estado global                                                       */
    /* ------------------------------------------------------------------ */
    let eventoSelecionadoEid = null;
    let ultimoScan           = 0;       // timestamp ms do último scan aceito
    const COOLDOWN_MS        = 3000;    // intervalo mínimo entre leituras
    let toastTimer           = null;
    let overlayTimer         = null;

    /* ------------------------------------------------------------------ */
    /*  Seleção de evento via delegação — sem inline onclick               */
    /* ------------------------------------------------------------------ */
    document.querySelectorAll('.botao-selecionar').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var card = btn.closest('.borda-visitas');
            eventoSelecionadoEid = card.dataset.eid;

            document.querySelectorAll('.borda-visitas').forEach(function (c) {
                c.classList.remove('ativo');
                c.classList.add('inativo');
                c.querySelector('.botao-selecionar').textContent = 'Selecionar';
                c.querySelector('.botao-selecionar').classList.remove('selecionado');
            });

            card.classList.add('ativo');
            card.classList.remove('inativo');
            btn.textContent = '✓ Selecionado';
            btn.classList.add('selecionado');

            console.log('[leitor] evento selecionado:', eventoSelecionadoEid);
            mostrarToast('Evento selecionado!', 'aviso');
        });
    });

    /* ------------------------------------------------------------------ */
    /*  Toast                                                               */
    /* ------------------------------------------------------------------ */
    function mostrarToast(mensagem, tipo) {
        tipo = tipo || 'sucesso';
        var toast = document.getElementById('toast');
        clearTimeout(toastTimer);
        toast.className   = 'visivel toast-' + tipo;
        toast.textContent = mensagem;
        toastTimer = setTimeout(function () { toast.className = ''; }, 3500);
    }

    /* ------------------------------------------------------------------ */
    /*  Overlay sobre o scanner                                             */
    /* ------------------------------------------------------------------ */
    function mostrarOverlay(tipo, mensagem) {
        var overlay = document.getElementById('scanner-overlay');
        var icon    = document.getElementById('overlay-icon');
        var msg     = document.getElementById('overlay-msg');
        clearTimeout(overlayTimer);

        var icones = { sucesso: '✓', erro: '✗', aviso: '⚠' };
        overlay.className = 'scanner-overlay ' + tipo;
        icon.textContent  = icones[tipo] || '';
        msg.textContent   = mensagem;

        overlayTimer = setTimeout(function () {
            overlay.className = 'scanner-overlay hidden';
        }, COOLDOWN_MS);
    }

    /* ------------------------------------------------------------------ */
    /*  Bip sonoro                                                          */
    /* ------------------------------------------------------------------ */
    function playBeep(freq, durMs) {
        freq  = freq  || 880;
        durMs = durMs || 150;
        try {
            var ctx  = new (window.AudioContext || window.webkitAudioContext)();
            var osc  = ctx.createOscillator();
            var gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            gain.gain.value     = 0.1;
            osc.frequency.value = freq;
            osc.type            = 'sine';
            osc.start();
            setTimeout(function () { osc.stop(); ctx.close(); }, durMs);
        } catch (e) {
            console.warn('[leitor] bip indisponível:', e);
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Extração de PID                                                     */
    /* ------------------------------------------------------------------ */
    function extrairPid(texto) {
        if (texto.indexOf('http') === 0) {
            try {
                var url = new URL(texto);
                var pid = url.searchParams.get('pid');
                if (pid) return pid;
            } catch (_) { /* segue para fallback */ }
        }
        return texto.trim(); // texto bruto como pid
    }

    /* ------------------------------------------------------------------ */
    /*  Envio de presença                                                   */
    /* ------------------------------------------------------------------ */
    function enviarPresenca(pid) {
        fetch("{{ route('registrar.presenca') }}", {
            method:  'POST',
            headers: {
                'Content-Type':  'application/json',
                'X-CSRF-TOKEN':  '{{ csrf_token() }}',
                'Accept':        'application/json',
            },
            body: JSON.stringify({ pid: pid, eid: eventoSelecionadoEid }),
        })
        .then(function (res) {
            return res.json().then(function (data) {
                return { ok: res.ok, data: data };
            });
        })
        .then(function (result) {
            if (result.ok) {
                playBeep(880, 150);
                var msg = (result.data && result.data.message) ? result.data.message : 'Presença registrada!';
                mostrarOverlay('sucesso', msg);
                mostrarToast(msg, 'sucesso');
            } else {
                playBeep(330, 300);
                var msg = (result.data && result.data.message) ? result.data.message : 'Erro ao registrar.';
                mostrarOverlay('erro', msg);
                mostrarToast(msg, 'erro');
            }
        })
        .catch(function (err) {
            console.error('[leitor] fetch falhou:', err);
            playBeep(330, 300);
            mostrarOverlay('erro', 'Sem conexão com o servidor.');
            mostrarToast('Sem conexão com o servidor.', 'erro');
        });
    }

    /* ------------------------------------------------------------------ */
    /*  Callback do scanner                                                 */
    /* ------------------------------------------------------------------ */
    function aoDetectar(textoQr) {
        var agora = Date.now();

        // Cooldown: ignora leituras repetidas dentro do intervalo
        if (agora - ultimoScan < COOLDOWN_MS) return;
        ultimoScan = agora;

        console.log('[leitor] QR detectado:', textoQr);

        if (!eventoSelecionadoEid) {
            mostrarOverlay('aviso', 'Selecione uma palestra!');
            mostrarToast('Selecione uma palestra antes de escanear.', 'aviso');
            ultimoScan = 0; // libera imediatamente para nova tentativa
            return;
        }

        var pid = extrairPid(textoQr);
        if (!pid) {
            mostrarOverlay('erro', 'QR Code inválido');
            mostrarToast('QR Code inválido.', 'erro');
            return;
        }

        console.log('[leitor] PID extraído:', pid);
        enviarPresenca(pid);
    }

    /* ------------------------------------------------------------------ */
    /*  Inicialização do scanner                                            */
    /* ------------------------------------------------------------------ */
    var scanner = new Html5Qrcode('reader');

    var config = {
        fps: 10,
        qrbox: function (w, h) {
            var lado = Math.floor(Math.min(w, h) * 0.8);
            return { width: lado, height: lado };
        },
        aspectRatio: 1.0,
    };

    // Câmera traseira: melhor foco automático em dispositivos móveis
    scanner.start(
        { facingMode: 'environment' },
        config,
        aoDetectar,
        function () { /* erros de frame ignorados */ }
    ).then(function () {
        console.log('[leitor] câmera iniciada com sucesso.');
    }).catch(function (err) {
        console.error('[leitor] falha ao iniciar câmera:', err);
        mostrarToast('Não foi possível acessar a câmera. Verifique as permissões.', 'erro');
    });

})();
</script>

@endsection