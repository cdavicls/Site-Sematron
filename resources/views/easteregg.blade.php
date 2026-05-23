@extends(auth()->check() ? 'layouts.layout-logado' : 'layouts.layout-basico')

@section('title', '📂 ACESSO RESTRITO - NÍVEL 5')

@section('content')
<style>
    .easter-egg-container {
        background-color: #0a0a0a;
        color: #00ff41; /* Verde Matrix */
        font-family: 'Courier New', Courier, monospace;
        padding: 40px;
        border-radius: 10px;
        border: 1px solid #00ff41;
        box-shadow: 0 0 20px rgba(0, 255, 65, 0.2);
        margin-top: 50px;
        text-align: center;
    }

    .glitch {
        font-size: 2.5rem;
        font-weight: bold;
        text-transform: uppercase;
        position: relative;
        text-shadow: 0.05em 0 0 #00ff41, -0.025em -0.05em 0 #ff00c1,
                     0.025em 0.05em 0 #fffc00;
        animation: glitch 1s infinite;
    }

    .quote-box {
        margin-top: 30px;
        text-align: left;
        display: inline-block;
        max-width: 600px;
        border-left: 3px solid #00ff41;
        padding-left: 20px;
    }

    .author {
        color: #888;
        font-size: 0.9rem;
        display: block;
        margin-bottom: 15px;
    }

    @keyframes glitch {
        0% { transform: translate(0); }
        20% { transform: translate(-2px, 2px); }
        40% { transform: translate(-2px, -2px); }
        60% { transform: translate(2px, 2px); }
        80% { transform: translate(2px, -2px); }
        100% { transform: translate(0); }
    }
</style>

<div class="container">
    <div class="easter-egg-container">
        <h1 class="glitch">ACESSO CONCEDIDO</h1>
        <p>Parabéns. Você encontrou o que não deveria existir.</p>
        
        <hr style="border-color: #333;">

        <div class="quote-box">
            <p>"Nah, I'd Win."</p>
            <span class="author">— **Adunas 025** (O Arquiteto / Ex-Gerente do TI)</span>

            <p>"Às vezes a vida é sobre fazer escolhas burras"</p>
            <span class="author">— **Nina 025** (A Curiosa / Pseudo-Gerente do Marketing)</span>

            <p>"Se te oferecerem a diretoria é muito importante que vc recuse."</p>
            <span class="author">— **CNH 023** (O Profeta / Ex-Diretor)</span>

            <p>"Em Briga de Saci, todo chute é voadora."</p>  
            <span class="author">— **Luizinha 023** (O Ditador / Ex-Diretor)</span>

            <p>"Se a vida te der limões, chupe-os. Porque é oq temos"</p>  
            <span class="author">— **Sena 025** (O justo / Ex-Diretor)</span>

            <p>"Não sei.. Não Sabia da existência dessa pagina."</p>
            <span class="author">— **Mari 025** (A Tranquila / Ex-Gerente do TI)</span>

            <p>"O sol nasceu para todos, mas a sombra, para os espertos."</p>
            <span class="author">— **Moderado 025** (O Lucido / Ex-Gerente do TI)</span>

            <p>"Pra quem ta afogando, jacaré é tronco"</p>  
            <span class="author">— **Maiami 023** (O Sábio / Membro Financeiro)</span>

            <p>"B.A.M.B.O"</p>  
            <span class="author">— **100 025** (O Enigimatico / Mem#$!%#&!¨ ERROR!)</span>

            <p>"A Diferença de cagar e dar a bunda é apenas vetorial."</p>
            <span class="author">— **Oscar 024** (O Coringa / Ex-Garoto Propaganda)</span>

            <p>"FRASE FODA DO JUJUBA"</p>
            <span class="author">— **Jujuba 024** (O Exilado / Ex-Gerente do RE)</span>

            <p>"Dodongo dislikes smoke"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Adoramos usar batatas para testar as coisas"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Se alguma coisa queimar, é culpa do Caio"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Você achou um easter egg. Parabéns."</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Discord é um ótimo serviço"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"#BatataDosTestes"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"GameNight é a melhor parte da semana"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Sempre tem easter egg nos vídeos da SEMATRON"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Sie sind das Essen und wir sind die Jäger"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"return x && !x;"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Powered by PHP"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Powered by JavaScript"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Node.JS é muito bom"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Nerf Slark"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Surrender aos 20"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Dota > LoL"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Everybody, put your hands up"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Han shot first"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Crocodilos não conseguem colocar a língua para fora"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"zankoku na tenshi no you ni / shounen yo shinwa ni nare"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Rem best girl"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Praise the sun"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Wake up, Mr. Freeman. Wake up and smell the ashes."</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Resonance Cascade"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Dovahkiin, Dovahkiin, naal ok zin los vahriin"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"olar"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Rrrrrrenan"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"SEMATRON is love, SEMATRON is life"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Miniursos"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>":eyes:"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Dragon Maid s2"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Skyrim Loli Edition"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Pão de bataaaaaaata"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Daijoubu mãe, daijoubu"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Caio caio caio caio caio caio caio caio caio"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Grr"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Felix > Rem"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"Kawaii neko-chan"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"ff aos 15?"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"TOP GAP"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"JG DIFF"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"DIFFY IN THE MIFFY"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"git gud."</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"kekw"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"os circuitos de consagração social serão tanto mais eficazes quanto maior a distância do objeto consagrado"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"pronouns: aca/demia"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"vardomiro"</p>
            <span class="author">— **Anônimo** (ERROR)</span>

            <p>"alguns professores ensinam, outros dão aula"</p>
            <span class="author">— **Anônimo** (ERROR)</span>
        </div>

        <div style="margin-top: 40px; font-size: 0.8rem; opacity: 0.5;">
            <p>Sistema monitorado. Limpe seus rastros ao sair.</p>
        </div>
    </div>
</div>
@endsection