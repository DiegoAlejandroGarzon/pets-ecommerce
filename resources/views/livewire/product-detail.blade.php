<?php

use function Livewire\Volt\{state, mount, computed};
use App\Models\Product;

state(['product' => null, 'quantity' => 1, 'selectedVariantId' => null]);

mount(function (Product $product) {
    $this->product = $product->load(['category', 'variants']);
    $this->selectedVariantId = $this->product->variants->first()?->id;
});

$selectedVariant = computed(function () {
    return $this->product->variants->firstWhere('id', $this->selectedVariantId);
});

$addToCart = function () {
    $cart = session()->get('cart', []);
    $variant = $this->selectedVariant;
    $id = $variant->id;

    if (isset($cart[$id])) {
        $cart[$id]['quantity'] += $this->quantity;
    } else {
        $cart[$id] = [
            "name"     => $this->product->name,
            "quantity" => $this->quantity,
            "price"    => $variant->price,
            "image"    => $this->product->getFirstMediaUrl('default'),
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
            <p class="text-lg text-gray-600 mb-4">{{ $product->brand }}</p>

            {{-- Precio y peso se actualizan al cambiar la variante --}}
            <div class="mb-6">
                <div class="text-3xl font-bold text-orange-600">
                    ${{ number_format($this->selectedVariant->price, 0, ',', '.') }}
                </div>
                @if($this->selectedVariant->presentation || $this->selectedVariant->weight)
                    <div class="mt-1 text-sm text-gray-500 font-medium">
                        Presentación:
                        <span class="text-gray-700">
                            {{ $this->selectedVariant->presentation }}
                            @if($this->selectedVariant->weight)
                                {{ $this->selectedVariant->presentation ? '·' : '' }}
                                {{ rtrim(rtrim((string) $this->selectedVariant->weight, '0'), '.') }} {{ $this->selectedVariant->weight_unit }}
                            @endif
                        </span>
                    </div>
                @endif
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
                                wire:click="$set('selectedVariantId', {{ $variant->id }})"
                                class="border-2 p-3 rounded-xl text-left transition duration-150 {{ $selectedVariantId === $variant->id ? 'border-orange-600 bg-orange-50' : 'border-gray-200 hover:border-orange-300' }}"
                            >
                                <span class="block font-bold text-sm">{{ implode(', ', $variant->attributes ?? ['Estándar']) }}</span>
                                @if($variant->presentation || $variant->weight)
                                    <span class="block text-xs text-gray-500">
                                        {{ $variant->presentation }}
                                        @if($variant->weight)
                                            {{ $variant->presentation ? '·' : '' }}
                                            {{ rtrim(rtrim((string) $variant->weight, '0'), '.') }} {{ $variant->weight_unit }}
                                        @endif
                                    </span>
                                @endif
                                <span class="block text-sm font-semibold text-orange-600 mt-1">${{ number_format($variant->price, 0, ',', '.') }}</span>
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
                <a href="{{ route('cart') }}" class="flex items-center gap-2 border-2 border-gray-900 text-gray-900 px-5 py-4 rounded-xl font-bold hover:bg-gray-900 hover:text-white transition duration-150">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Ver carrito
                </a>
            </div>
        </div>
    </div>
</div>
