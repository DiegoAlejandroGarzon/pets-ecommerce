<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EpaycoController extends Controller
{
    public function response(Request $request)
    {
        $ref_payco = $request->query('ref_payco');

        if (!$ref_payco) {
            return redirect('/')->with('error', 'No se recibió referencia de pago');
        }

        $url = "https://secure.epayco.co/validation/v1/reference/" . $ref_payco;
        $response = json_decode(file_get_contents($url));

        if (!$response || !$response->success) {
            return redirect('/')->with('error', 'Error validando el pago');
        }

        $data = $response->data;
        // x_id_invoice viene como "ORD-{id}"
        $orderId = str_replace('ORD-', '', $data->x_id_invoice ?? '');
        $order = Order::find($orderId);

        // Estados ePayco: 1=Aceptada, 2=Rechazada, 3=Pendiente, 4=Fallida
        $status = $data->x_cod_response;

        if ($status == 1) {
            session()->forget('cart');
            if ($order) {
                $order->update(['status' => 'paid', 'payment_ref' => $ref_payco]);
            }
            return view('epayco.result', ['status' => 'success', 'message' => '¡Pago exitoso!', 'data' => $data]);
        }

        if ($status == 3) {
            if ($order) {
                $order->update(['status' => 'pending', 'payment_ref' => $ref_payco]);
            }
            return view('epayco.result', ['status' => 'pending', 'message' => 'Tu pago está siendo procesado.', 'data' => $data]);
        }

        if ($order) {
            $order->update(['status' => 'failed', 'payment_ref' => $ref_payco]);
        }
        return view('epayco.result', ['status' => 'error', 'message' => 'El pago no pudo ser procesado.', 'data' => $data]);
    }

    public function confirmation(Request $request)
    {
        $data = $request->all();
        Log::info('ePayco Confirmation Received', $data);

        // Firma: sha256(p_cust_id_client + p_key + x_ref_payco + x_transaction_id + x_amount + x_currency_code)
        // ePayco envía x_amount con 2 decimales, ej: "10000.00"
        $amount = number_format((float) ($data['x_amount'] ?? 0), 2, '.', '');

        $signature_local = hash('sha256',
            env('EPAYCO_P_CUST_ID_CLIENT') .
            env('EPAYCO_P_KEY') .
            ($data['x_ref_payco'] ?? '') .
            ($data['x_transaction_id'] ?? '') .
            $amount .
            ($data['x_currency_code'] ?? '')
        );

        Log::info('ePayco firma debug', [
            'cust_id'        => env('EPAYCO_P_CUST_ID_CLIENT'),
            'p_key'          => substr(env('EPAYCO_P_KEY'), 0, 6) . '...',
            'x_ref_payco'    => $data['x_ref_payco'] ?? '',
            'x_transaction_id' => $data['x_transaction_id'] ?? '',
            'x_amount_raw'   => $data['x_amount'] ?? '',
            'x_amount_used'  => $amount,
            'x_currency_code' => $data['x_currency_code'] ?? '',
            'firma_calculada' => $signature_local,
            'firma_recibida'  => $data['x_signature'] ?? '',
        ]);

        if ($signature_local !== ($data['x_signature'] ?? '')) {
            Log::warning('ePayco: firma inválida');
            return response('Invalid Signature', 400);
        }

        $status = $data['x_cod_response'] ?? null;
        $rawInvoice = $data['x_id_invoice'] ?? '';
        $orderId = str_replace('ORD-', '', $rawInvoice);
        $order = Order::find($orderId);

        if (!$order) {
            Log::warning("ePayco: orden no encontrada para invoice {$rawInvoice}");
            return response('Order not found', 404);
        }

        $statusMap = [
            '1' => 'paid',
            '2' => 'failed',
            '3' => 'pending',
            '4' => 'failed',
        ];

        $newStatus = $statusMap[(string) $status] ?? 'failed';
        $order->update(['status' => $newStatus, 'payment_ref' => $data['x_ref_payco'] ?? null]);

        Log::info("ePayco: orden {$order->id} actualizada a '{$newStatus}'");

        return response('OK', 200);
    }
}
