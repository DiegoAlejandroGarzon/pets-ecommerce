<?php

use function Livewire\Volt\{state, computed, mount};
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Http;

state([
    'cart' => [],
    'name' => '',
    'email' => '',
    'address' => '',
    'city' => '',
    'phone' => '',
    'processing' => false,
]);

mount(function () {
    $this->cart = session()->get('cart', []);
    if (count($this->cart) === 0) {
        return redirect()->to('/');
    }

    if (auth()->check()) {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }
});

$total = computed(function () {
    return array_reduce(
        $this->cart,
        function ($carry, $item) {
            return $carry + $item['price'] * $item['quantity'];
        },
        0,
    );
});

$submitOrder = function () {
    $this->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'address' => 'required|string|max:500',
        'city' => 'required|string|max:100',
        'phone' => 'required|string|max:20',
    ]);

    $this->processing = true;

    try {
        DB::beginTransaction();

        $order = Order::create([
            'user_id' => auth()->id(),
            'total' => $this->total,
            'status' => 'pending',
            'shipping_data' => [
                'name' => $this->name,
                'email' => $this->email,
                'address' => $this->address,
                'city' => $this->city,
                'phone' => $this->phone,
            ],
        ]);

        foreach ($this->cart as $variantId => $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_variant_id' => $variantId,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }

        DB::commit();

        // Mercado Pago Integration
        $mpToken = config('services.mercadopago.access_token');

        if (empty($mpToken)) {
            session()->flash('error', 'Mercado Pago no está configurado. Revisa tu archivo .env');
            $this->processing = false;
            return;
        }

        $items = [];
        foreach ($this->cart as $item) {
            $items[] = [
                'title' => $item['name'],
                'quantity' => (int) $item['quantity'],
                'unit_price' => (float) $item['price'],
                'currency_id' => 'COP',
            ];
        }

        $paymentData = [
            'items' => $items,
            'back_urls' => [
                'success' => route('payment.success', ['order' => $order->id]),
                'failure' => route('payment.failure', ['order' => $order->id]),
                'pending' => route('payment.pending', ['order' => $order->id]),
            ],
            'auto_return' => 'approved',
            'external_reference' => (string) $order->id,
        ];

        \Illuminate\Support\Facades\Log::info('MercadoPago Request Data', [
            'url' => config('app.url'),
            'data' => $paymentData
        ]);

        $response = Http::withToken($mpToken)->post('https://api.mercadopago.com/checkout/preferences', $paymentData);

        if ($response->successful()) {
            $preference = $response->json();

            session()->forget('cart');
            session()->flash('order_id', $order->id);

            // Redirect to Mercado Pago checkout
            return redirect()->away($preference['init_point']);
        } else {
            session()->flash('error', 'Error de Mercado Pago: ' . $response->body());
            $this->processing = false;
            return;
        }
    } catch (\Exception $e) {
        DB::rollBack();
        $this->processing = false;
        session()->flash('error', 'Ocurrió un error: ' . $e->getMessage());
    }
};

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-black text-gray-900 mb-8 text-center lg:text-left">Finalizar Compra</h1>

    @if (session()->has('error'))
        <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-xl">
            {{ session('error') }}
        </div>
    @endif

    <div class="lg:flex lg:items-start lg:gap-12">
        <div class="lg:w-2/3">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 mb-8">
                <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                    <span
                        class="bg-orange-100 text-orange-600 w-8 h-8 rounded-full flex items-center justify-center text-sm">1</span>
                    Datos de Envío
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-full">
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wider">Nombre
                            Completo</label>
                        <input wire:model="name" type="text"
                            class="w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500 p-3">
                        @error('name')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wider">Correo
                            Electrónico</label>
                        <input wire:model="email" type="email"
                            class="w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500 p-3">
                        @error('email')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label
                            class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wider">Teléfono</label>
                        <input wire:model="phone" type="text"
                            class="w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500 p-3">
                        @error('phone')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-span-full">
                        <label class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wider">Dirección de
                            Entrega</label>
                        <input wire:model="address" type="text" placeholder="Ej: Calle 123 #45-67"
                            class="w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500 p-3">
                        @error('address')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label
                            class="block text-sm font-bold text-gray-700 mb-2 uppercase tracking-wider">Ciudad</label>
                        <input wire:model="city" type="text"
                            class="w-full rounded-xl border-gray-200 focus:border-orange-500 focus:ring-orange-500 p-3">
                        @error('city')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
                <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                    <span
                        class="bg-orange-100 text-orange-600 w-8 h-8 rounded-full flex items-center justify-center text-sm">2</span>
                    Método de Pago
                </h2>
                <div class="p-4 border-2 border-orange-600 bg-orange-50 rounded-2xl flex items-center justify-between">
                    <div>
                        <p class="font-bold text-gray-900">Mercado Pago</p>
                        <p class="text-sm text-gray-600">Paga de forma segura con Tarjeta, PSE o Efectivo.</p>
                    </div>
                    <div class="text-orange-600">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <p class="mt-4 text-xs text-gray-500 italic">* Tu pago será procesado por la plataforma segura de Mercado Pago.</p>
            </div>
        </div>

        <div class="mt-12 lg:mt-0 lg:w-1/3">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 sticky top-24">
                <h2 class="text-2xl font-bold mb-6 text-gray-900">Tu Pedido</h2>
                <div class="space-y-4 mb-8">
                    @foreach ($cart as $item)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">{{ $item['name'] }} x{{ $item['quantity'] }}</span>
                            <span
                                class="font-bold text-gray-900">${{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t border-gray-100 pt-6 space-y-4 mb-8">
                    <div class="flex justify-between text-gray-500">
                        <span>Subtotal</span>
                        <span>${{ number_format($this->total, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-500">
                        <span>Envío</span>
                        <span class="text-green-600 font-bold">Gratis</span>
                    </div>
                    <div class="flex justify-between text-2xl font-black text-gray-900 pt-2">
                        <span>Total</span>
                        <span class="text-orange-600">${{ number_format($this->total, 2) }}</span>
                    </div>
                </div>

                <button wire:click="submitOrder" wire:loading.attr="disabled"
                    class="w-full bg-gray-900 text-white py-5 rounded-2xl font-black text-lg hover:bg-black transition duration-150 shadow-xl disabled:opacity-50">
                    <span wire:loading.remove>REALIZAR PEDIDO</span>
                    <span wire:loading>PROCESANDO...</span>
                </button>
                <p class="text-center text-xs text-gray-400 mt-4 uppercase tracking-tighter font-bold">Compra 100%
                    Segura</p>
            </div>
        </div>
    </div>
</div>
