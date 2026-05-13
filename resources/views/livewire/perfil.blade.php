<div>
    <x-slot:title>Perfil</x-slot>

    <section class="separa-em-tres">
        <div class="Parte-da-Esquerda">
            <h1 class="Login-grande">Perfil</h1>
            <h1 class="Sub-Login">Veja aqui suas informações.</h1>
        </div>
        
        @if($this->usuario)
            <div class="borda-perfil">
                <div class="texto-perfil">
                    <h1 class="nome-bendito">{{ $this->usuario->name }}</h1>
                    <h1 class="email-do-ser">{{ $this->usuario->email }}</h1>
                    <h1 class="curso-que-faz">{{ $this->usuario->curso }}</h1>
                        @switch($this->paymentStatus)
                            @case('confirmed')
                                <h1 class="situacao-do-pix" style="background-color: #28a745;">Pagamento Confirmado!</h1>
                                @break

                            @case('waiting')
                                <a class="spc" href="/pagamento/retomar">Aguardando Pagamento...<p>(Clique para continuar Pagando)</p></a>
                                @break

                            @case('n_sub')
                                <h1 class="situacao-do-pix" style=" background-color: #6c757d;">Não inscrito</h1>
                                @break
                            
                            @default
                                <h1 class="situacao-do-pix" style=" background-color: #dc3545;">Erro no processamento...<p>Favor entrar em contato conosco!!</p></h1>
                        @endswitch
                    <!-- <a href="" class="infos-pessoas">Informações Pessoais</a> -->
                    <h1 class="qrcode">Qr code para marcar sua presença:<br></h1>
                    <div style="background-color: #ffffff; padding: 15px; display: inline-block; border-radius: 10px; margin-top: 10px;">
                        <div id="qrcode"></div>
                    </div>
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            let pid = "{{$this->userAtual->pid ?? ''}}";
                            new QRCode(document.getElementById("qrcode"),{
                                text: pid,
                                width: 150,
                                height: 150
                            });
                        });
                    </script>
                </div>

            </div>
        @endif

        <div class="borda-perfil">
            <div class="texto-perfil">
                <h1 class="segue-o-lider">Acompanhe seus dados</h1>
                <h1 class="palavras-bonitas">Selecione a edição:</h1>
                <div class="perfil-bagulho">
                    <div class="input-group">
                        <select id="seletor-sematron" wire:model.live="sidSelecionada" required>
                            <option value="">Selecione...</option>
                            @foreach($this->edicoes as $edicao)
                                <option value="{{ $edicao->sid}}">
                                    {{ $edicao->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($this->userAtual)
        <section class="vaiSumir is-visible">
            <div class="Parte-da-Esquerda">
                {{-- O nome_edicao agora existe no backend e será exibido aqui --}}
                <h1 class="sn-grande">{{ $this->userAtual->nome_edicao }}</h1>
            </div>

            <div class="textos-perfil">
                <div class="div-Perfil">
                    <h1 class="Tit-Perfil">Categoria:</h1>
                    @if($this->userAtual->gid ==3)
                    <h2 class="SubTit-Perfil">Participante</h2>
                    @elseif($this->userAtual->gid ==0 || $this->userAtual->gid ==1 || $this->userAtual->gid ==2)
                    <h2 class="SubTit-Perfil">Membro Organizador</h2>
                    @endif
                </div>

                <div class="div-Perfil">
                    <h1 class="Tit-Perfil">Kit:</h1>
                    <h2 class="SubTit-Perfil">{{ $this->userAtual->pack_id_n }}</h2>
                </div>

                <div class="div-Perfil">
                    <h1 class="Tit-Perfil">Minicursos:</h1>
                    <h2 class="SubTit-Perfil">{{ $this->userAtual->minicurso_n }}</h2>
                </div>

                <div class="div-Perfil">
                    <h1 class="Tit-Perfil">Visita:</h1>
                    <h2 class="SubTit-Perfil">{{ $this->userAtual->viagem_n }}</h2>
                </div>

                <div class="div-Perfil">
                    <h1 class="Tit-Perfil">Camiseta:</h1>
                    <h2 class="SubTit-Perfil">{{ $this->userAtual->camiseta_n }}</h2>
                </div>
            </div>

            <div class="textos-perfil">
                <div class="div-Perfil">
                    <h1 class="Tit-Perfil">Presença em Palestras:</h1>
                    {{-- CORREÇÃO: "presenca_calculada" com 'C' e adicionei o % --}}
                    <h2 class="SubTit-Perfil">{{ $this->userAtual->presenca_calculada }}%</h2>
                </div>

                <div class="borda-certificado">
                    <div class="certificados">
                        <h1 class="palavra-certificado">Certificados</h1>
                    </div>

                    <div class="espacamento-boto">
                        <a href="" class="botoes-perfil">Presença</a>
                        <a href="" class="botoes-perfil">Minicurso</a>
                        <a href="" class="botoes-perfil">Visita</a>
                    </div>
                </div>
            </div>
        </section>
    @else
        <section>
            <!-- <div class="textos-perfil" style="margin-top: 20px;">
                <p>Nenhum dado encontrado ou selecione uma edição acima.</p>
            </div> -->
        </section>
    @endif
</div>
