<?php

use function Livewire\Volt\{state, computed, mount};

state(['cart' => []]);

mount(function () {
    $this->cart = session()->get('cart', []);
});

$removeFromCart = function ($id) {
    $cart = session()->get('cart', []);
    unset($cart[$id]);
    session()->put('cart', $cart);
    $this->cart = $cart;
    $this->dispatch('cart-updated');
};

$total = computed(function () {
    return array_reduce($this->cart, function ($carry, $item) {
        return $carry + ($item['price'] * $item['quantity']);
    }, 0);
});

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-black text-gray-900 mb-8">Mi Carrito</h1>

    @if(count($cart) > 0)
        <div class="lg:flex lg:items-start lg:gap-12">
            <div class="lg:w-2/3 space-y-6">
                @foreach($cart as $id => $item)
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-6">
                        <div class="w-24 h-24 bg-gray-100 rounded-xl flex items-center justify-center text-gray-400 overflow-hidden shrink-0">
                            @if(!empty($item['image']))
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                            @else
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-900">{{ $item['name'] }}</h3>
                            <p class="text-gray-500">${{ number_format($item['price'], 2) }} x {{ $item['quantity'] }}</p>
                        </div>
                        <div class="text-xl font-black text-gray-900">
                            ${{ number_format($item['price'] * $item['quantity'], 2) }}
                        </div>
                        <button wire:click="removeFromCart({{ $id }})" class="text-gray-400 hover:text-red-600 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 lg:mt-0 lg:w-1/3">
                <div class="bg-gray-900 text-white p-8 rounded-3xl shadow-xl">
                    <h2 class="text-2xl font-bold mb-6">Resumen</h2>
                    <div class="flex justify-between mb-4 text-gray-400">
                        <span>Subtotal</span>
                        <span class="text-white">${{ number_format($this->total, 2) }}</span>
                    </div>
                    <div class="flex justify-between mb-8 text-gray-400">
                        <span>Envío</span>
                        <span class="text-green-400 font-bold">Gratis</span>
                    </div>
                    <div class="border-t border-gray-800 pt-6 flex justify-between items-end mb-8">
                        <span class="text-lg">Total</span>
                        <span class="text-4xl font-black text-orange-500">${{ number_format($this->total, 2) }}</span>
                    </div>
                    <a href="/checkout" class="w-full inline-block text-center bg-orange-600 text-white py-4 rounded-2xl font-bold hover:bg-orange-700 transition duration-150 shadow-lg shadow-orange-900/20">
                        Finalizar Compra
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-24">
            <div class="bg-gray-100 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6 text-gray-400">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p class="text-xl text-gray-500 mb-8">Tu carrito está vacío.</p>
            <a href="/" class="inline-block bg-orange-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-orange-700 transition duration-150">
                Ver productos
            </a>
        </div>
    @endif
</div>
