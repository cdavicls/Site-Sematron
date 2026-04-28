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
@endsection