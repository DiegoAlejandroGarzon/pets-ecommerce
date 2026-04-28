<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function success(Request $request, Order $order)
    {
        // Mercado Pago sends collection_id, collection_status, payment_id, status, external_reference, etc.
        $status = $request->query('status');
        
        if ($status === 'approved') {
            $order->update(['status' => 'paid']);
        }

        // Store the order ID in session so the success view can show it
        session()->flash('order_id', $order->id);
        
        return redirect()->route('success');
    }

    public function failure(Request $request, Order $order)
    {
        $order->update(['status' => 'cancelled']);
        
        return redirect()->route('checkout')->with('error', 'El pago fue rechazado o cancelado. Por favor intenta con otro método.');
    }

    public function pending(Request $request, Order $order)
    {
        $order->update(['status' => 'pending']);
        
        session()->flash('order_id', $order->id);
        return redirect()->route('success')->with('message', 'Tu pago está pendiente de confirmación.');
    }

    // Webhook for asynchronous notifications
    public function webhook(Request $request)
    {
        $secret = config('services.mercadopago.webhook_secret');
        
        // Verificación de firma (Firma secreta de Mercado Pago)
        if ($secret) {
            $signatureHeader = $request->header('x-signature');
            $requestId = $request->header('x-request-id');
            
            // La validación real de MP usa ts=... , v1=... y hmac
            // Por simplicidad, si la clave existe, puedes usar el método oficial del SDK
            // o verificar que el ID concuerda. Si prefieres saltarlo en pruebas, configúralo vacío.
        }

        Log::info('MercadoPago Webhook Received', $request->all());

        if ($request->input('type') === 'payment') {
            $paymentId = $request->input('data.id');
            
            // We would verify the payment status with Mercado Pago API here
            // using the $paymentId and update the corresponding order status.
        }

        return response()->json(['status' => 'success'], 200);
    }
}
