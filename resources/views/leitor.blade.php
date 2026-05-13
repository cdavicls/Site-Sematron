@extends(auth()->check() ? 'layouts.layout-logado' : 'layouts.layout-basico')

@section('title', 'Visitas')

@section('content')
    <section class="Palavra-Atividades_Traco-Laranja Palavra-Programacao">
        <h1 class="Palavra-Atividades">Leitor</h1>
        <div class="traco-laranja"></div>
    </section>

    <section class="espaco-no-topo margem-esquerda">

        @foreach($eventos as $evento)
        <div class="borda-visitas">
            <div class="texto-na-esquerda">
                <h1 class="nome-da-visita">{{ $evento->name }}</h1>
                <h1 class="horarios-visitas">
                    {{ \Carbon\Carbon::parse($evento->start)->format('H:i') }}
                    •
                    {{ \Carbon\Carbon::parse($evento->end)->format('H:i') }}
                </h1>
            </div>
            <button class="botao-inscrever" onclick="selecionarEvento({{ $evento->eid }}, this)">Selecionar</button>
        </div>
        @endforeach

        <h1 class="Palavra-Atividades" style="margin-top: 40px;">
            Escaneie o QR Code do participante
        </h1>

        <div style="display: flex; justify-content: center; margin-top: 20px;">
            <div class="scanner-box">
                <div id="reader"></div>
            </div>
        </div>

        <style>
            .scanner-box {
                width: 300px;
                height: 300px;
                background-color: #39ff14;
                border-radius: 30px;
                position: relative;
                overflow: hidden;
            }

            #reader {
                width: 100%;
                height: 100%;
            }

            #reader video {
                width: 100% !important;
                height: 100% !important;
                object-fit: contain;
            }
        </style>
    </section>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        console.log('leitor.js: script carregado');

        let eventoSelecionado = null;
        // FIX 1: Cooldown controlado por timestamp em vez de flag booleana simples
        let lastScanTime = 0;
        const COOLDOWN_MS = 3000;

        function selecionarEvento(eid, elemento) {
            console.log('leitor.js: evento selecionado', eid);
            eventoSelecionado = eid;

            document.querySelectorAll('.borda-visitas').forEach(el => {
                el.style.opacity = "0.6";
            });

            const card = elemento.closest('.borda-visitas');
            card.style.opacity = "1";
        }

        const html5QrCode = new Html5Qrcode("reader");
        console.log('leitor.js: Html5Qrcode instanciado');

        function playBeep() {
            try {
                const context = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = context.createOscillator();
                const gainNode = context.createGain();
                oscillator.connect(gainNode);
                gainNode.connect(context.destination);
                gainNode.gain.value = 0.1;
                oscillator.frequency.value = 880;
                oscillator.type = 'sine';
                oscillator.start();
                setTimeout(() => oscillator.stop(), 150);
            } catch (e) {
                console.warn("Não foi possível reproduzir o som de bip.", e);
            }
        }

        const qrboxFunction = (viewfinderWidth, viewfinderHeight) => {
            const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
            const qrboxSize = Math.floor(minEdge * 0.8);
            return { width: qrboxSize, height: qrboxSize };
        };

        html5QrCode.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: qrboxFunction
            },
            (decodedText) => {
                console.log('leitor.js: QR detectado', decodedText);
                onScanSuccess(decodedText);
            },
            (errorMessage) => {
                // Erros parciais de scan ignorados intencionalmente
            }
        ).then(() => {
            console.log('leitor.js: leitura iniciada com sucesso');
        }).catch(err => {
            console.error('leitor.js: erro ao iniciar o leitor', err);
            alert('Não foi possível iniciar a câmera. Verifique permissões (HTTPS ou localhost necessário).');
        });

        function onScanSuccess(decodedText) {
            // FIX 2: Cooldown via timestamp — nunca trava por flag booleana esquecida
            const now = Date.now();
            if (now - lastScanTime < COOLDOWN_MS) {
                return;
            }
            lastScanTime = now;

            playBeep();

            console.log('leitor.js: onScanSuccess chamado', decodedText);

            if (!eventoSelecionado) {
                console.warn('leitor.js: sem evento selecionado');
                alert("Selecione a palestra antes de escanear!");
                // FIX 3: Resetar o timestamp para permitir novo scan imediatamente
                lastScanTime = 0;
                return;
            }

            let pid = decodedText;
            console.log('leitor.js: decodedText inicial', pid);

            // FIX 4: Extração de pid mais robusta, não trava o scanner em caso de falha
            if (decodedText.includes("http")) {
                try {
                    const url = new URL(decodedText);
                    const extractedPid = url.searchParams.get("pid");
                    if (extractedPid) {
                        pid = extractedPid;
                        console.log('leitor.js: pid extraído da URL', pid);
                    } else {
                        console.warn('leitor.js: URL sem parâmetro pid, usando texto bruto');
                    }
                } catch (e) {
                    console.warn('leitor.js: URL inválida no QR, usando texto bruto', e);
                }
            }

            enviarPresenca(pid);
        }

        function enviarPresenca(pid) {
            console.log('leitor.js: enviarPresenca chamado', { pid, eid: eventoSelecionado });

            if (!eventoSelecionado) {
                console.warn('leitor.js: tentar enviar sem evento selecionado');
                alert("Selecione uma palestra primeiro!");
                lastScanTime = 0; // Libera scanner imediatamente
                return;
            }

            fetch("{{ route('registrar.presenca') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    pid: pid,
                    eid: eventoSelecionado
                })
            })
            .then(res => {
                console.log('leitor.js: resposta fetch recebida', res.status);
                if (!res.ok) {
                    return res.json().then(data => {
                        throw new Error(data.message || 'Erro no servidor');
                    });
                }
                return res.json();
            })
            .then(data => {
                console.log('leitor.js: presença registrada com sucesso', data);
                alert(data.message);
            })
            .catch(err => {
                console.error('leitor.js: erro ao enviar presença', err);
                alert(err.message || 'Erro ao registrar presença.');
                // FIX 5: Em caso de erro de rede, libera o scanner após o cooldown normal
                // (lastScanTime já foi setado, então o cooldown natural se aplica)
            });
        }
    </script>
@endsection