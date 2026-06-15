<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Inscricao;
use App\Models\Userinfo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Mpdf\Mpdf;

class CertificateController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // CONSTANTES: centralizam os caminhos dos templates.
    // No legado, esses caminhos eram montados com realpath/__DIR__
    // espalhados pelo código. Aqui ficam em um só lugar.
    // ─────────────────────────────────────────────────────────
    private const TEMPLATE_PRESENCA  = 'templates/21/template_presenca.pdf';
    private const TEMPLATE_MINICURSO = 'templates/21/template_minicurso.pdf';
    private const TEMPLATE_VISITA    = 'templates/21/template_visita.pdf';

    private function template_presenca($sid)
    {
        return "templates/{$sid}/template_presence.pdf";
    }

    private function template_minicurso($sid)
    {
        return "templates/{$sid}/template_mini.pdf";
    }

    private function template_visita($sid)
    {
        return "templates/{$sid}/template_visita.pdf";
    }

    /**
     * Equivalente ao getAll() do legado.
     *
     * Monta a lista de certificados disponíveis para uma inscrição (pid).
     * Em vez de retornar hashes manuais, retorna URLs assinadas pelo Laravel.
     *
     * Este método é chamado internamente pelo componente Livewire do perfil,
     * não diretamente por uma rota pública.
     */
    public function listForInscricao(int $pid): array
    {
        $inscricao = Inscricao::where('pid', $pid)->firstOrFail();
        $sid = $inscricao->sid;
        $list = [];

        // ── 1. Certificados estáticos pré-gerados (ex: 123.0.pdf, 123.1.pdf) ──
        // Estes são PDFs que alguém fez upload manualmente, como no método upload() do legado.
        for ($i = 0; $i < 2; $i++) {
            $filename = "{$pid}.{$i}.pdf";
            if (Storage::disk('certificados')->exists($filename)) {
                $list[] = [
                    'type' => $this->typeName($filename, $sid),
                    // URL::signedRoute() substitui o hash_hmac + base64_encode do legado.
                    // O Laravel assina a URL com a APP_KEY, tornando-a impossível de falsificar.
                    'url'  => URL::signedRoute('certificados.download', ['file' => $filename]),
                ];
            }
        }

        // ── 2. Certificado de Presença (gerado dinamicamente via mPDF) ──
        // Equivalente ao bloco "if (file_exists($templatePresence))" do legado.
        if (Storage::disk('certificados')->exists($this->template_presenca($sid))) {
            if ($this->userHasMinPresence($inscricao)) {
                $list[] = [
                    'type' => 'Presença',
                    'url'  => URL::signedRoute('certificados.generate', [
                        'pid'  => $pid,
                        'type' => 'presenca',
                    ]),
                ];
            }
        }

        // ── 3. Certificado de Minicurso ──
        // Só aparece se o pagamento foi confirmado e o participante tem minicurso.
        if (Storage::disk('certificados')->exists($this->template_minicurso($sid))) {
            if ($inscricao->minicurso && $this->isPaymentConfirmed($inscricao)) {
                $label = ($sid == 17) ? 'Minicurso Manhã' : 'Minicurso';
                $list[] = [
                    'type' => $label,
                    'url'  => URL::signedRoute('certificados.generate', [
                        'pid'  => $pid,
                        'type' => 'minicurso',
                    ]),
                ];
            }
        }

        // ── 4. Certificado de Visita ──
        if (Storage::disk('certificados')->exists($this->template_visita($sid))) {
            if ($inscricao->viagem && $this->isPaymentConfirmed($inscricao)) {
                $list[] = [
                    'type' => 'Visita Técnica',
                    'url'  => URL::signedRoute('certificados.generate', [
                        'pid'  => $pid,
                        'type' => 'visita',
                    ]),
                ];
            }
        }

        return $list;
    }

     /**
     * Equivalente ao getTemplated() do legado.
     *
     * Gera o PDF dinamicamente usando mPDF com um template de fundo.
     * Esta rota é protegida pelo middleware 'signed', então o Laravel
     * já verificou a autenticidade da URL antes de chegar aqui.
     *
     * O parâmetro $type pode ser: 'presenca', 'minicurso', ou 'visita'.
     */
    public function generate(Request $request, int $pid, string $type)
    {
        $inscricao = Inscricao::where('pid', $pid)->firstOrFail();

        // Busca o nome do participante na tabela userinfos (como o sistema já faz no perfil)
        $userinfo = Userinfo::where('uid', $inscricao->uid)->firstOrFail();
        $name = $userinfo->name;

        // Decide qual template PDF usar e monta os dados de texto
        switch ($type) {
            case 'presenca':
                if (!$this->userHasMinPresence($inscricao)) {
                    abort(403, 'Presença mínima não atingida.');
                }
                $templatePath = Storage::disk('certificados')->path($this->template_presenca($inscricao->sid));
                $texto = $this->buildTextoPresenca($inscricao);
                break;

            case 'minicurso':
                if (!$inscricao->minicurso || !$this->isPaymentConfirmed($inscricao)) {
                    abort(403, 'Acesso negado ao certificado de minicurso.');
                }
                $templatePath = Storage::disk('certificados')->path($this->template_minicurso($inscricao->sid));
                $event = Event::where('eid', $inscricao->minicurso)->firstOrFail();
                $texto = $this->buildTextoMinicurso($event, $inscricao);
                break;

            case 'visita':
                if (!$inscricao->viagem || !$this->isPaymentConfirmed($inscricao)) {
                    abort(403, 'Acesso negado ao certificado de visita.');
                    }
                    $templatePath = Storage::disk('certificados')->path($this->template_visita($inscricao->sid));
                    $event = Event::where('eid', $inscricao->viagem)->firstOrFail();
                    $texto = $this->buildTextoVisita($event, $inscricao);
                break;

            default:
                abort(404, 'Tipo de certificado desconhecido.');
        }

        if (!file_exists($templatePath)) {
            abort(404, 'Template do certificado não encontrado. Fale com a organização.');
        }

        // ── Geração do PDF com mPDF ──
        // Configuração idêntica ao sistema legado: sem margens, modo paisagem.
        $mpdf = new Mpdf([
            'orientation'    => 'L', // Landscape (paisagem)
            'margin_left'    => 1,
            'margin_right'   => 1,
            'margin_top'     => 0,
            'margin_bottom'  => 0,
            // O diretório temp do mPDF deve ser gravável.
            // storage/app/mpdf-temp/ é um local seguro no Laravel.
            'tempDir'        => storage_path('app/mpdf-temp'),
        ]);

        // Importa o PDF de fundo — exatamente como no código legado, com adaptações para mpdf 8.x.

        $mpdf->setSourceFile($templatePath);
        $tplId = $mpdf->importPage(1);
        $mpdf->SetPageTemplate($tplId);
        $mpdf->AddPage('L');
        $mpdf->SetAutoPageBreak(false);

        // ── Escreve o nome do participante ──
        // As coordenadas (0, 84) e o estilo de fonte são os mesmos do sistema antigo.
        // Ajuste os valores de X, Y, largura e tamanho de fonte conforme necessário.
        $mpdf->SetXY(0, 90);
        $mpdf->SetFont('Arial', '', 38);
        $mpdf->WriteCell(297, 25, $name, 0, 0, 'C'); // 297mm = largura de um A4

        // ── Escreve o texto descritivo ──
        $mpdf->SetXY(18, 110);
        $mpdf->SetFont('Arial', '', 17);
        $mpdf->MultiCell(260, 10, $texto,0,'C');

        // ── Entrega o PDF ao navegador ──
        // 'S' (String) devolve o conteúdo como string em vez de imprimir headers diretamente.
        // Isso é necessário no Laravel porque o framework precisa controlar os headers HTTP.
        // No PHP puro do legado, o Output() imprimia direto, o que quebraria o ciclo do Laravel.
        $pdfContent = $mpdf->Output('', 'S');

        $filename = "Certificado-{$type}-{$name}.pdf";

        return response($pdfContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Equivalente ao getFile() do legado.
     *
     * Faz o download de um certificado estático que foi enviado por upload.
     * A URL já foi validada pelo middleware 'signed' antes de chegar aqui.
     */
    public function download(Request $request, string $file)
    {
        // Garante que o nome do arquivo não tem caminhos maliciosos (ex: ../../etc/passwd)
        $file = basename($file);

        if (!Storage::exists("certificados/{$file}")) {
            abort(404, 'Certificado não encontrado.');
        }

        // Storage::download() cuida de todos os headers HTTP automaticamente.
        // No legado, era preciso setar Content-Type, Content-Disposition, etc. manualmente.
        return Storage::download(
            "certificados/{$file}",
            "Certificado - {$file}",
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Upload de um certificado pré-gerado (equivalente ao upload() do legado).
     * Acessível apenas por administradores (ver rota no web.php).
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file'   => 'required|file|mimes:pdf|max:10240', // máx 10MB
            'prefix' => 'required|string|regex:/^[0-9]+$/',  // apenas números (o pid)
            'suffix' => 'required|string|in:0,1',            // apenas 0 ou 1
        ]);

        $filename = "{$request->prefix}.{$request->suffix}.pdf";
        $request->file('file')->storeAs('certificados', $filename);

        return back()->with('success', "Certificado '{$filename}' enviado com sucesso!");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MÉTODOS PRIVADOS AUXILIARES
    // Equivalentes às lógicas internas espalhadas pelo getAll() e getTemplated()
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Verifica se o participante atingiu a presença mínima.
     *
     * No legado: UserPresence::userHasMinPresence($pid)
     * Aqui a lógica usa os dados que já existem no model Inscricao.
     * O campo 'presence' é um JSON array com os eids das palestras assistidas.
     *
     * Ajuste o valor mínimo (70%) conforme a regra da Sematron.
     */
    private function userHasMinPresence(Inscricao $inscricao): bool
    {
        $presence = is_array($inscricao->presence)
            ? $inscricao->presence
            : json_decode($inscricao->presence ?? '[]', true);

        $totalPresenca = count($presence);

        // Conta o total de palestras daquela edição (sid)
        $totalPalestras = Event::where('type', 'palestra')
            ->where('sid', $inscricao->sid)
            ->count();

        if ($totalPalestras === 0) {
            return false;
        }

        $percentual = ($totalPresenca / $totalPalestras) * 100;

        // Regra: mínimo de 70% de presença para emitir certificado
        return $percentual >= 70;
    }

    /**
     * Verifica se o pagamento da inscrição foi confirmado.
     * Usa o campo 'status' da tabela userdata (Inscricao), que o PaymentController
     * atualiza para 'confirmed' quando o Mercado Pago aprova o pagamento.
     */
    private function isPaymentConfirmed(Inscricao $inscricao): bool
    {
        return $inscricao->status === 'confirmed';
    }

    /**
     * Monta o texto do certificado de presença.
     * Ajuste o número romano e o nome do evento conforme a edição.
     */
    private function buildTextoPresenca(Inscricao $inscricao): string
    {
        // Você pode buscar o nome da edição da tabela de sematrons se existir,
        // ou montar com o sid. Aqui usamos uma abordagem simples:
        return "Participou da {$inscricao->sid}ª Semana de Engenharia Mecatrônica "
             . "da Escola de Engenharia de São Carlos da Universidade de São Paulo, "
             . "cumprindo o mínimo de 70% de presença nas atividades.";
    }

    /**
     * Monta o texto do certificado de minicurso.
     * O campo 'extra' do Event é um JSON; a carga horária fica em extra->m->ch.
     */
    private function buildTextoMinicurso(Event $event, Inscricao $inscricao): string
    {
        $extra = json_decode($event->extra ?? '{}');
        $ch = $extra->m->ch ?? 0;

        return "Participou do minicurso de {$event->name}, ministrado durante a "
             . "{$inscricao->sid}ª Semana de Engenharia Mecatrônica da Escola de Engenharia "
             . "de São Carlos da Universidade de São Paulo, "
             . "com carga horária total de {$ch} horas.";
    }

    /**
     * Monta o texto do certificado de visita técnica.
     */
    private function buildTextoVisita(Event $event, Inscricao $inscricao): string
    {
        return "Participou da visita técnica a {$event->name}, realizada durante a "
             . "{$inscricao->sid}ª Semana de Engenharia Mecatrônica da Escola de Engenharia "
             . "de São Carlos da Universidade de São Paulo.";
    }

    /**
     * Resolve o nome legível do tipo de certificado com base no nome do arquivo.
     * Mantém a mesma lógica de strpos() do código legado.
     */
    private function typeName(string $str, int|string $sid): string
    {
        if (str_contains($str, '.0') || str_contains($str, '-0')) {
            return 'Presença';
        }
        if (str_contains($str, '.1') || str_contains($str, '-1')) {
            return ($sid == 17) ? 'Minicurso Manhã' : 'Minicurso';
        }
        return ($sid == 17) ? 'Minicurso Tarde' : 'Minicurso';
    }
}

