@extends(auth()->check() ? 'layouts.layout-logado' : 'layouts.layout-basico')

@section('title', 'Perfil')                 <!--AQUI TU BOTA O NEGOCIO QUE APARECE NA ABA LÁ EM CIMA-->

@section('content')                         <!--AQUI COMEÇA O CONTEÚDO ESPECÍFICO DA PÁGINA-->
    <section class="separa-em-tres">
        <div class="Parte-da-Esquerda">
            <h1 class="Login-grande">Perfil</h1>
            <h1 class="Sub-Login">Veja aqui suas informações.</h1>
        </div>



        <div class="borda-perfil">
            <div class="texto-perfil">
                <h1 class="nome-bendito">Enzo Marina Lombada</h1>
                <h1 class="email-do-ser">amigosdogil10@gmail.com</h1>
                <h1 class="curso-que-faz">EESC-USP | Eng. Mecatrônica</h1>
                <h1 class="situacao-do-pix">Pagamento Confirmado!</h1>
                <a href="" class="infos-pessoas">Informações Pessoais</a>
                <h1 class="qrcode">Qr code para marcar sua presença:<br></h1>
                <div id="qrcode"></div>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        let pid = "{{$participante->pid}}";
                        new QRCode(document.getElementById("qrcode"),{
                            text: pid,
                            width: 150,
                            height: 150
                        });
                    });
                </script>
            </div>
        </div>



        <div class="borda-perfil">
            <div class="texto-perfil">
                <h1 class="segue-o-lider">Acompanhe seus dados</h1>
                <h1 class="palavras-bonitas">Selecione qual das Sematrons que você deseja ver seus dados:</h1>
                <div class="perfil-bagulho">
                    <div class="input-group">
                        <select id="semattt" name="pacote-dos-guri" required>
                            <option value="" disabled selected>Selecione...</option>
                            <option value="Sematron-XXII">SEMATRON XXII</option>
                            <option value="Sematron-XXI">SEMATRON XXI</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="vaiSumir" data-show="Sematron-XXII">
        <div class="Parte-da-Esquerda">
            <h1 class="Login-grande">SEMATRON XXII</h1>
        </div>



        <div class="textos-perfil">
            <div class="div-Perfil">
                <h1 class="Tit-Perfil">Categoria:</h1>
                <h2 class="SubTit-Perfil">Participante</h2>
            </div>

            <div class="div-Perfil">
                <h1 class="Tit-Perfil">Kit:</h1>
                <h2 class="SubTit-Perfil">Completo</h2>
            </div>

            <div class="div-Perfil">
                <h1 class="Tit-Perfil">Minicursos:</h1>
                <h2 class="SubTit-Perfil">Jogar Truco</h2>
            </div>

            <div class="div-Perfil">
                <h1 class="Tit-Perfil">Visita:</h1>
                <h2 class="SubTit-Perfil">Ubiratã</h2>
            </div>

            <div class="div-Perfil">
                <h1 class="Tit-Perfil">Camiseta:</h1>
                <h2 class="SubTit-Perfil">G</h2>
            </div>
        </div>



        <div class="textos-perfil">
            <div class="div-Perfil">
                <h1 class="Tit-Perfil">Presença em Palestras:</h1>
                <h2 class="SubTit-Perfil">X%</h2>
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
@endsection                                 <!--AQUI ACABA O CONTEÚDO ESPECÍFICO DA PÁGINA-->