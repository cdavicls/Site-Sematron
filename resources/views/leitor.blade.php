@extends(auth()->check() ? 'layouts.layout-logado' : 'layouts.layout-basico')           <!--IMPORTANDO LAYOUT DA PASTA LAYOUT-->

@section('title', 'Visitas')        <!--AQUI TU BOTA O NEGOCIO QUE APARECE NA ABA LÁ EM CIMA-->

@section('content')                         <!--AQUI COMEÇA O CONTEÚDO ESPECÍFICO DA PÁGINA-->
    <!--SLK NUM COMPENSA FAZER DNV-->
        <section class="Palavra-Atividades_Traco-Laranja Palavra-Programacao">
            <h1 class="Palavra-Atividades">Leitor</h1>
            <div class="traco-laranja"></div>
        </section>


        <section class="espaco-no-topo margem-esquerda">
            <div class="imagem-de-fundo">
                
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

            </div>
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
                object-fit: cover;
            }
            </style>
        </section>

        <script src="https://unpkg.com/html5-qrcode"></script>
        <script>
            let eventoSelecionado = null;

            function selecionarEvento(eid, elemento) {
                eventoSelecionado = eid;

                document.querySelectorAll('.borda-visitas').forEach(el => {
                    el.style.opacity = "0.6";
                });

                const card = elemento.closest('.borda-visitas');
                card.style.opacity = "1";
            }

            const html5QrCode = new Html5Qrcode("reader");

            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    html5QrCode.start(
                        devices[devices.length - 1].id,
                            {
                                fps: 10,
                                qrbox: { width: 250, height: 250 }
                            },
                        (decodedText) => {
                        onScanSuccess(decodedText);
                       }
                    );
                }
            });

            function onScanSuccess(decodedText) {

                if (!eventoSelecionado) {
                    alert("Selecione a palestra antes de escanear!");
                    return;
                }

                let pid = decodedText;

                if (decodedText.includes("http")) {
                    let url = new URL(decodedText);
                    pid = url.searchParams.get("pid");
                }
                enviarPresenca(pid);
            }

            function enviarPresenca(pid) {

                if (!eventoSelecionado) {
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
                .then(res => res.json())
                .then(data => alert(data.message));
            }
        </script>
@endsection@extends(auth()->check() ? 'layouts.layout-logado' : 'layouts.layout-basico')

@section('title', 'Leitor de QR Code')

@section('content')
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

<section class="Palavra-Atividades_Traco-Laranja Palavra-Programacao">
    <h1 class="Palavra-Atividades">Leitor</h1>
    <div class="traco-laranja"></div>
</section>

<section class="espaco-no-topo margem-esquerda">

    {{-- ===================== LISTA DE EVENTOS ===================== --}}
    <div id="lista-eventos">
        @if(count($eventos) > 0)
            <select id="seletor-evento" onchange="selecionarEvento(this.value)">
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

    {{-- ===================== SCANNER ===================== --}}
    <h1 class="Palavra-Atividades" style="margin-top: 40px;">
        Escaneie o QR Code do participante
    </h1>

    {{-- Toast de feedback (substitui alert()) --}}
    <div id="toast" role="alert" aria-live="assertive"></div>

    <div style="display:flex; justify-content:center; margin-top:20px; margin-bottom: 50px;">
        <div class="scanner-wrapper">
            <div id="reader"></div>
            <div id="scanner-overlay" class="scanner-overlay hidden">
                <div id="overlay-icon"></div>
                <div id="overlay-msg"></div>
            </div>
        </div>
    </div>

</section>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    console.log('leitor.js: script carregado');

    let eventoSelecionado = null;
    let isScanning = true; // Controla o tempo de recarga de uma leitura para outra

    // Função de notificação elegante
    function showToast(message, type = 'info') {
        const toast = document.getElementById("toast");
        toast.className = `show ${type}`;
        toast.innerText = message;
        setTimeout(function(){ 
            toast.className = toast.className.replace(`show ${type}`, ""); 
        }, 3000);
    }

    window.selecionarEvento = function(eid) {
        console.log('leitor.js: evento selecionado', eid);
        eventoSelecionado = eid;

        if (eid) {
            showToast("Palestra selecionada com sucesso!", "success");
        }
    }

    const html5QrCode = new Html5Qrcode("reader");
    console.log('leitor.js: Html5Qrcode instanciado');

    // Gera um sinal sonoro leve
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

    // Tenta usar a câmera traseira primeiro (environment)
    html5QrCode.start(
        { facingMode: "environment" },
        {
            fps: 10,
            qrbox: { width: 250, height: 250 }
        },
        (decodedText) => {
            onScanSuccess(decodedText);
        },
        (errorMessage) => {
            // Ignoramos erros contínuos de foco de tela
        }
    ).then(() => {
        console.log('leitor.js: leitura iniciada com sucesso');
    }).catch(err => {
        console.error('leitor.js: erro ao iniciar o leitor', err);
        showToast('Não foi possível iniciar a câmera. Verifique as permissões do navegador.', 'error');
    });

    function onScanSuccess(decodedText) {
        if (!isScanning) return; // Retorna caso ainda não tenha passado o cooldown da leitura anterior
        isScanning = false;

        playBeep();
        html5QrCode.pause(); // Pausa a câmera visualmente enquanto processa no banco

        if (!eventoSelecionado) {
            showToast("Selecione a palestra antes de escanear!", "warning");
            setTimeout(() => {
                html5QrCode.resume();
                isScanning = true;
            }, 2000);
            return;
        }

        let pid = decodedText;

        // Caso o QR code seja um link, extrai o PID se possível
        try {
            if (decodedText.includes("http")) {
                let url = new URL(decodedText);
                pid = url.searchParams.get("pid") || decodedText;
            }
        } catch (e) {
            showToast("QR Code com formato de URL inválido.", "error");
            setTimeout(() => {
                html5QrCode.resume();
                isScanning = true;
            }, 2000);
            return;
        }

        enviarPresenca(pid);
    }

    function enviarPresenca(pid) {
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
        .then(res => res.json().then(data => ({ status: res.status, ok: res.ok, body: data })))
        .then(res => {
            if (!res.ok) {
                throw new Error(res.body.message || 'Erro no servidor');
            }
            showToast(res.body.message || "Presença registrada!", "success");
        })
        .catch(err => {
            console.error('leitor.js: erro ao enviar presença', err);
            showToast(err.message || 'Erro ao registrar presença.', "error");
        })
        .finally(() => {
            // Dá um intervalo de 2.5 segundos para liberar o próximo escaneamento
            setTimeout(() => {
                html5QrCode.resume();
                isScanning = true;
            }, 2500);
        });
    }
</script>

@endsection