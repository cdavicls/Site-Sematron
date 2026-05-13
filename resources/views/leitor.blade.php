@extends(auth()->check() ? 'layouts.layout-logado' : 'layouts.layout-basico')           <!--IMPORTANDO LAYOUT DA PASTA LAYOUT-->

@section('title', 'Visitas')        <!--AQUI TU BOTA O NEGOCIO QUE APARECE NA ABA LÁ EM CIMA-->

@section('content')                         <!--AQUI COMEÇA O CONTEÚDO ESPECÍFICO DA PÁGINA-->
    <!--SLK NUM COMPENSA FAZER DNV-->
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
            Escaneie o QR Code do participante</h1>
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
            let isScanning = true; // Flag para controlar o cooldown do scanner

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

            // Função para gerar um som de "bip"
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

            // Função para tornar a caixa de scan responsiva
            const qrboxFunction = (viewfinderWidth, viewfinderHeight) => {
                const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
                const qrboxSize = Math.floor(minEdge * 0.8); // Usa 80% da menor dimensão
                return { width: qrboxSize, height: qrboxSize };
            };

            // Tenta usar a câmera traseira (environment) diretamente, que possui foco automático
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
                    // console.debug('leitor.js: erro de scan parcial', errorMessage); // Comentado para evitar flood no console
                }
            ).then(() => {
                console.log('leitor.js: leitura iniciada com sucesso');
            }).catch(err => {
                console.error('leitor.js: erro ao iniciar o leitor', err);
                alert('Não foi possível iniciar a câmera. Verifique permissões (HTTPS ou localhost necessário).');
            });

            function onScanSuccess(decodedText) {
                if (!isScanning) {
                    return; // Ignora scans durante o cooldown
                }
                isScanning = false; // Desativa o scan para evitar leituras múltiplas

                playBeep(); // Feedback sonoro imediato
                html5QrCode.pause(); // Pausa o scanner visualmente

                console.log('leitor.js: onScanSuccess chamado', decodedText);
                if (!eventoSelecionado) {
                    console.warn('leitor.js: sem evento selecionado');
                    alert("Selecione a palestra antes de escanear!");
                    // Reativa o scanner imediatamente em caso de erro do usuário
                    html5QrCode.resume();
                    isScanning = true;
                    return;
                }

                let pid = decodedText;
                console.log('leitor.js: decodedText inicial', pid);

                try {
                    if (decodedText.includes("http")) {
                        let url = new URL(decodedText);
                        pid = url.searchParams.get("pid");
                        console.log('leitor.js: pid extraído da URL', pid);
                    }
                } catch (e) {
                    alert("QR Code com formato de URL inválido.");
                    html5QrCode.resume();
                    isScanning = true;
                    return;
                }

                enviarPresenca(pid);

                // Define um cooldown para reativar o scanner
                setTimeout(() => {
                    html5QrCode.resume();
                    isScanning = true;
                    console.log('leitor.js: Leitor reativado.');
                }, 3000); // Cooldown de 3 segundos
            }

            function enviarPresenca(pid) {
                console.log('leitor.js: enviarPresenca chamado', { pid, eid: eventoSelecionado });
                if (!eventoSelecionado) {
                    console.warn('leitor.js: tentar enviar sem evento selecionado');
                    alert("Selecione uma palestra primeiro!");
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
                        return res.json().then(data => { throw new Error(data.message || 'Erro no servidor'); });
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
                });
            }
        </script>
@endsection