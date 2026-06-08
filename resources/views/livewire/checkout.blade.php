<?php

use function Livewire\Volt\{state, computed, mount};
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

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

$prepareOrder = function () {
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

        $this->dispatch('open-epayco', [
            'orderId'   => $order->id,
            'total'     => $this->total,
            'name'      => $this->name,
            'address'   => $this->address,
            'phone'     => $this->phone,
            'email'     => $this->email,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        $this->processing = false;
        session()->flash('error', 'Ocurrió un error al procesar tu pedido. Intenta de nuevo.');
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
                        <p class="font-bold text-gray-900">ePayco (Davivienda)</p>
                        <p class="text-sm text-gray-600">Paga de forma segura con Tarjeta, PSE o Efecty.</p>
                    </div>
                    <div class="text-orange-600">
                        <img src="https://multimedia.epayco.co/epayco-landing/btns/epayco-logo-fondo-oscuro.png" alt="ePayco" class="h-8">
                    </div>
                </div>
                <p class="mt-4 text-xs text-gray-500 italic">* Tu pago será procesado por la plataforma segura de ePayco Colombia.</p>
            </div>
        </div>

        <div class="mt-12 lg:mt-0 lg:w-1/3">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 sticky top-24">
                <h2 class="text-2xl font-bold mb-6 text-gray-900">Tu Pedido</h2>
                <div class="space-y-4 mb-8">
                    @foreach ($cart as $item)
                        <div class="flex items-center justify-between text-sm gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 overflow-hidden shrink-0">
                                    @if(!empty($item['image']))
                                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    @endif
                                </div>
                                <span class="text-gray-600 font-medium">{{ $item['name'] }} <span class="text-gray-400">x{{ $item['quantity'] }}</span></span>
                            </div>
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

                <button wire:click="prepareOrder" wire:loading.attr="disabled"
                    class="w-full bg-gray-900 text-white py-5 rounded-2xl font-black text-lg hover:bg-black transition duration-150 shadow-xl disabled:opacity-60">
                    <span wire:loading.remove wire:target="prepareOrder">REALIZAR PAGO CON EPAYCO</span>
                    <span wire:loading wire:target="prepareOrder">Procesando...</span>
                </button>
                <p class="text-center text-xs text-gray-400 mt-4 uppercase tracking-tighter font-bold">Compra 100%
                    Segura</p>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://checkout.epayco.co/checkout.js"></script>
<script>
    document.addEventListener('livewire:init', function () {
        Livewire.on('open-epayco', function (payload) {
            var p = Array.isArray(payload) ? payload[0] : payload;

            var handler = ePayco.checkout.configure({
                key: "{{ env('EPAYCO_PUBLIC_KEY') }}",
                test: {{ env('EPAYCO_TESTING', 'true') === 'true' ? 'true' : 'false' }}
            });

            handler.open({
                name: "Compra en {{ config('app.name') }}",
                description: "Compra de productos para mascotas",
                invoice: "ORD-" + p.orderId,
                currency: "cop",
                amount: p.total,
                tax_base: "0",
                tax: "0",
                country: "co",
                lang: "es",
                external: "false",
                name_billing: p.name,
                address_billing: p.address,
                mobile_phone_billing: p.phone,
                email_billing: p.email,
                number_doc_billing: "",
                response: "{{ route('epayco.response') }}",
                confirmation: "{{ route('epayco.confirmation') }}",
            });
        });
    });
</script>
