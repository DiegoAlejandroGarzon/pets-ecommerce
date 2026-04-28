<?php

use function Livewire\Volt\{state, on, mount};

state(['count' => 0]);

mount(function () {
    $cart = session()->get('cart', []);
    $this->count = array_sum(array_column($cart, 'quantity'));
});

on(['cart-updated' => function () {
    $cart = session()->get('cart', []);
    $this->count = array_sum(array_column($cart, 'quantity'));
}]);

?>

<a href="/cart" class="relative text-gray-600 hover:text-orange-700 transition duration-150 p-2 rounded-full hover:bg-orange-50">
    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
    </svg>
    
    @if($count > 0)
        <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-orange-600 rounded-full">
            {{ $count }}
        </span>
    @endif
</a>
