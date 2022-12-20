<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Boleto\Render\Pdf;
use Eduardokum\LaravelBoleto\Pessoa;

class BoletoController extends Controller
{
    protected $bancos = [
        'Bancoob' => "\\Eduardokum\\LaravelBoleto\\Boleto\\Banco\\Bancoob",
        'Banrisul' => "\\Eduardokum\\LaravelBoleto\\Boleto\\Banco\\Banrisul",
        'Bb' => "\\Eduardokum\\LaravelBoleto\\Boleto\\Banco\\Bb",
        'Bnb' => "\\Eduardokum\\LaravelBoleto\\Boleto\\Banco\\Bnb",
        'Bradesco' => "\\Eduardokum\\LaravelBoleto\\Boleto\\Banco\\Bradesco",
        'Caixa' => "\\Eduardokum\\LaravelBoleto\\Boleto\\Banco\\Caixa",
        'Hsbc' => "\\Eduardokum\\LaravelBoleto\\Boleto\\Banco\\Hsbc",
        'Itau' => "\\Eduardokum\\LaravelBoleto\\Boleto\\Banco\\Itau",
        'Santander' => "\\Eduardokum\\LaravelBoleto\\Boleto\\Banco\\Santander",
        'Sicredi' => "\\Eduardokum\\LaravelBoleto\\Boleto\\Banco\\Sicredi",
    ];

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pdf(Request $request)
    {
        if (empty($request->banco)) {
            return response()->json([
                'success' => false,
                'message' => 'Banco não informado'
            ]);
        }
        $banco = ucfirst(strtolower($request->banco));
        $class = !empty($this->bancos[$banco]) ? $this->bancos[$banco] : null;
        if (empty($class)) {
            return response()->json([
                'success' => false,
                'message' => "Banco [{$banco}] não atendido pela api."
            ]);
        }
        $doc = preg_replace("/[^0-9]/", "", $request->beneficiario['documento']);
        $logo = null;
        if (!empty($request->logo) && substr($request->logo, 0, 3) !== 'SEM') {
            $logo = $this->checkLogo($request->logo ?? null, $doc);
            if (empty($logo)) {
                if (Storage::exists("$doc.jpg")) {
                    $logo = storage_path() . "/app/$doc.jpg";
                } elseif (Storage::exists("$doc.png")) {
                    $logo = storage_path() . "/app/$doc.png";
                }
            }
        }
        $dados = (object)$request->boleto;
        try {
            $beneficiario = new Pessoa($request->beneficiario);
            $pagador = new Pessoa($request->pagador);
            $payload = $request->boleto;
            $payload['dataVencimento'] = Carbon::parse($dados->dataVencimento);
            $payload['pagador'] = $pagador;
            $payload['beneficiario'] = $beneficiario;;
            $boleto = new $class($payload);
            $boleto->setLogo($logo ?? null);
            $pdf = new Pdf();
            $pdf->addBoleto($boleto);
            $resp = $pdf->gerarBoleto($pdf::OUTPUT_STRING);
            return response()->json([
                'success' => true,
                'pdf' => base64_encode($resp)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * @param $logo
     * @param string $doc
     * @return string|null
     */
    private function checkLogo($logo, string $doc)
    {
        if (empty($logo)) {
            return null;
        }
        $logotipo = gzdecode(base64_decode($logo));
        $logoInfo = getimagesize('data://text/plain;base64,' . base64_encode($logotipo));
        $type = $logoInfo[2];
        if ($type != '2' && $type != '3') {
            return null;
        }
        $ext = '.png';
        if ($type == 2) {
            $ext = '.jpg';
        }
        $path = storage_path() . '/app/' . $doc . $ext;
        Storage::put($doc . $ext, $logotipo);
        return $path;
    }
}
