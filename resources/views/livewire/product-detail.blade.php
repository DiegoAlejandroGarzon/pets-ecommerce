<?php

use function Livewire\Volt\{state, mount};
use App\Models\Product;

state(['product' => null, 'quantity' => 1, 'selectedVariant' => null]);

mount(function (Product $product) {
    $this->product = $product->load(['category', 'variants']);
    $this->selectedVariant = $this->product->variants->first();
});

$addToCart = function () {
    $cart = session()->get('cart', []);
    
    $id = $this->selectedVariant->id;
    
    if(isset($cart[$id])) {
        $cart[$id]['quantity'] += $this->quantity;
    } else {
        $cart[$id] = [
            "name" => $this->product->name,
            "quantity" => $this->quantity,
            "price" => $this->selectedVariant->price,
            "image" => $this->product->getFirstMediaUrl('default')
        ];
    }
    
    session()->put('cart', $cart);
    $this->dispatch('cart-updated');
    session()->flash('success', 'Producto añadido al carrito');
};

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="lg:flex lg:items-start lg:gap-12">
        <div class="lg:w-1/2">
            <div class="aspect-square bg-gray-100 rounded-2xl flex items-center justify-center text-gray-400 overflow-hidden shadow-sm">
                @if($product->hasMedia('default'))
                    <img src="{{ $product->getFirstMediaUrl('default') }}" alt="{{ $product->name }}" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
                @else
                    <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                @endif
            </div>
        </div>

        <div class="mt-8 lg:mt-0 lg:w-1/2">
            <nav class="flex mb-4 text-sm text-gray-500">
                <a href="/" class="hover:text-orange-600">Inicio</a>
                <span class="mx-2">/</span>
                <a href="/categories" class="hover:text-orange-600">{{ $product->category->name }}</a>
            </nav>

            <h1 class="text-4xl font-black text-gray-900 mb-2">{{ $product->name }}</h1>
            <p class="text-lg text-gray-600 mb-6">{{ $product->brand }}</p>

            <div class="text-3xl font-bold text-orange-600 mb-8">
                ${{ number_format($selectedVariant->price, 2) }}
            </div>

            <div class="prose prose-orange mb-8">
                {!! $product->description !!}
            </div>

            @if($product->variants->count() > 1)
                <div class="mb-8">
                    <label class="block text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">Opciones disponibles</label>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($product->variants as $variant)
                            <button 
                                wire:click="$set('selectedVariant', {{ $variant->id }})"
                                class="border-2 p-3 rounded-xl text-left transition duration-150 {{ $selectedVariant->id === $variant->id ? 'border-orange-600 bg-orange-50' : 'border-gray-200 hover:border-orange-300' }}"
                            >
                                <span class="block font-bold">{{ implode(', ', $variant->attributes ?? ['Estándar']) }}</span>
                                <span class="text-sm text-gray-500">${{ number_format($variant->price, 2) }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex items-center gap-4">
                <div class="flex items-center border-2 border-gray-200 rounded-xl">
                    <button wire:click="$set('quantity', {{ max(1, $quantity - 1) }})" class="p-3 hover:text-orange-600">-</button>
                    <span class="px-4 font-bold text-lg">{{ $quantity }}</span>
                    <button wire:click="$set('quantity', {{ $quantity + 1 }})" class="p-3 hover:text-orange-600">+</button>
                </div>
                <button wire:click="addToCart" class="flex-1 bg-orange-600 text-white py-4 rounded-xl font-bold hover:bg-orange-700 transition duration-150 shadow-lg shadow-orange-200">
                    Añadir al carrito
                </button>
            </div>
        </div>
    </div>
</div>
