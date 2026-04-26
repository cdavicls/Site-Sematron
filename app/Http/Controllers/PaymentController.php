<?php

namespace App\Http\Controllers;

use App\Models\Inscricao;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http; // Adicionado para fazer a requisição na API
use Illuminate\Support\Facades\Log;  // Adicionado para registrar erros no log

class PaymentController extends Controller
{
    public function checkout(Inscricao $inscricao)
    {
        $user = Auth::user();
        
        if ($inscricao->alojamento) {
            $preco = $inscricao->pack->preço + 85; // Exemplo de valor adicional para alojamento
            $pack_name = $inscricao->pack->nome . ' com Alojamento';
            $title = 'Inscrição Sematron - ' . $pack_name;
        } else {
            $preco = $inscricao->pack->preço;
            $pack_name = $inscricao->pack->nome;
            $title = 'Inscrição Sematron - ' . $pack_name;
        }
        
        $pack_id = $inscricao->pack_id;
        $code = 'SEM-' . strtoupper(Str::random(10)) . '-' . time();

        $pref = FinanceService::createMercadoPagoPreference($pack_id, $pack_name, $preco, $code, $title);
        
        Sale::create([
            'code' => $code,
            'uid' => $user->uid,
            'pid' => $inscricao->pid,
            'pref_id' => $pref->id,
            'status' => 'waiting',
            'time' => now()
        ]);

        return redirect()->away($pref->init_point);
    }

    public function resume_payment()
    {
        $user = Auth::user();
        $sale = Sale::where('uid', $user->uid)->where('status', 'waiting')->first();

        if (!$sale) {
            return redirect('/inicio')->with('error', 'Nenhuma inscrição pendente encontrada.');
        }

        $prefId = $sale->pref_id;
        $pref = FinanceService::get_preference($prefId);
        $expirationDate = Carbon::parse($pref->expiration_date_to);
        if ($expirationDate->isPast()) {
            self::cancelPayment($sale->code);
            return redirect('/inicio')->with('error', 'A preferência de pagamento expirou. Por favor, inicie o processo de pagamento novamente.');
        }
        return redirect()->away($pref->init_point);
    }

    public function success(Request $request)
    {
        $status = $request->query('collection_status');
        $code = $request->query('external_reference');
        if ($status == 'approved') {
            Sale::where('code', $code)->update([
                'status' => 'confirmed',
            ]);
            $pid = Sale::where('code', $code)->get()->first()->pid;
            Inscricao::where('pid', $pid)->update([
                'status' => 'confirmed',
            ]);

        }
        
        return redirect('/perfil')->with('success', 'Pagamento realizado com sucesso! Agradecemos sua inscrição.');
    }

    public function failure(Request $request)
    {
        $user = Auth::user();
        $sale = Sale::where('uid', $user->uid)->where('status', 'waiting')->first();

        if (!$sale) {
            return redirect('/inicio')->with('error', 'Nenhuma inscrição pendente encontrada.');
        }

        $prefId = $sale->pref_id;
        $pref = FinanceService::get_preference($prefId);
        $expirationDate = Carbon::parse($pref->expiration_date_to);
        if ($expirationDate->isPast()) {
            self::cancelPayment($sale->code);
            return redirect('/inicio')->with('error', 'A preferência de pagamento expirou. Por favor, inicie o processo de pagamento novamente.');
        }
        //ADICIONAR UM POPUP AQUI PARA INFORMAR O USUÁRIO QUE O PAGAMENTO FALHOU, E ORIENTAR A TENTAR NOVAMENTES
        return redirect('/perfil')->with('error', 'O pagamento foi cancelado ou ocorreu um erro. Por favor, tente novamente.');
    }

    public function pending(Request $request) 
    {
        $preferenceId = $request->query('preference_id');
        $code = $request->query('external_reference');
        $sucessful_sale = Sale::where('code', $code)->where('pref_id', $preferenceId)->where('status', 'confirmed')->first();
        if ($sucessful_sale) {
            $this->confirmPayment($code);
            return redirect('/perfil')->with('success', 'Pagamento realizado com sucesso! Agradecemos sua inscrição.');
        }
        Sale::where('code', $code)->update([
            'status' => 'waiting',
            'pref_id' => $preferenceId
        ]);
        $pid = Sale::where('code', $code)->get()->first()->pid;
        Inscricao::where('pid', $pid)->update([
            'status' => 'waiting',
        ]);
        return redirect('/perfil')->with('error', 'O pagamento foi cancelado ou ocorreu um erro. Por favor, tente novamente.');
    }

    public function webhook(Request $request)
    {
        Log::info('Webhook do Mercado Pago recebido', [
            'body' => $request->all()
        ]);
        $action = $request->input('action'); // "payment.created", "payment.updated", etc.

        $xSignature = $request->header('X_SIGNATURE');
        $xRequestId = $request->header('X_REQUEST_ID');
        $dataID = $request->input('data.id'); // ID do pagamento que sofreu a alteração de status
        Log::info('Dados extraídos do Webhook', [
            'action' => $action,
            'data_id' => $dataID,
        ]);                                 
        $parts = explode(',', $xSignature);
        $ts = null;
        $hash = null;

        foreach ($parts as $part) {
            $keyValue = explode('=', $part, 2);
            if (count($keyValue) == 2) {
                $key = trim($keyValue[0]);
                $value = trim($keyValue[1]);
                if ($key === "ts") {
                    $ts = $value;
                } elseif ($key === "v1") {
                    $hash = $value;
                }
            }
        }

        $secret = config('integrations.mercadopago.webhook_key');
        $manifest = "id:$dataID;request-id:$xRequestId;ts:$ts;";
        $sha = hash_hmac('sha256', $manifest, $secret);
        
        // if ($sha === $hash) {
        //     Log::info('Assinatura HMAC válida', [
        //         'action' => $action,
        //         'data_id' => $dataID,
        //     ]);
            // Só fazemos a requisição HTTP se for uma notificação de pagamento mesmo
            if (str_starts_with($action, 'payment')) {
                $response = Http::withToken(config('integrations.mercadopago.access_token'))
                    ->get("https://api.mercadopago.com/v1/payments/{$dataID}");

                log::info('Resposta da API do Mercado Pago para o pagamento', [
                    'payment_id' => $dataID,
                    'status_code' => $response->status(),
                    'body' => $response->body()
                ]);

                if ($response->successful()) {
                    $dadosPagamento = $response->json();
                    $statusPagamento = $dadosPagamento['status'];
                    $referenciaExterna = $dadosPagamento['external_reference'];
                    Log::info('Notificação de pagamento recebida', [
                        'payment_id' => $dataID,
                        'status' => $statusPagamento,
                        'external_reference' => $referenciaExterna,
                        'response' => $dadosPagamento
                    ]);
                    // Correção da sintaxe do Eloquent aqui
                    match ($statusPagamento) {
                        'approved' => $this->confirmPayment($referenciaExterna),
                        'authorized','rejected','pending', 'in_process' => Sale::where('code', $referenciaExterna)->update(['status' => 'waiting']),
                        'cancelled' => $this->cancelPayment($referenciaExterna),
                        default => Log::warning('Status de pagamento desconhecido recebido no Webhook', [
                            'status' => $statusPagamento,
                            'payment_id' => $dataID
                        ])
                    };
                } else {
                    // Grava no log se a API do Mercado Pago retornar erro na consulta do pagamento
                    Log::error('Erro ao consultar API do Mercado Pago no Webhook', [
                        'payment_id' => $dataID,
                        'erro' => $response->body()
                    ]);
                }
            }

            // O RETORNO 200 FICA AQUI! 
            // Independente do que aconteceu acima (sucesso ou falha na consulta), 
            // o Mercado Pago precisa saber que a notificação chegou no seu servidor.
            return response()->json([
                'status' => 'success',
                'message' => 'Notificação processada com sucesso'
            ], 200);
                
        // } else {
        //     Log::error('Assinatura HMAC inválida', [
        //         'action' => $action,
        //         'data_id' => $dataID,
        //     ]);
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Assinatura HMAC inválida'
        //     ], 403);
        // }
    }

    private function cancelPayment($code)
    {
        $pid = Sale::where('code', $code)->get()->first()->pid;
        Inscricao::where('pid', $pid)->delete();
        Sale::where('code', $code)->delete();
    }

    private function confirmPayment($code)
    {
        $sale = Sale::where('code', $code)->get()->first();
        if (!$sale) {
            Log::error('Venda não encontrada para confirmar pagamento', [
                'code' => $code
            ]);
            return; // Ou você pode lançar uma exceção, dependendo de como deseja lidar com isso
        }
        $pid = $sale->pid;

        Sale::where('code', $code)->update(['status' => 'confirmed']);
        Inscricao::where('pid', $pid)->update(['status' => 'confirmed']);
    }
}