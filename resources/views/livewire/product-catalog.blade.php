<?php

use function Livewire\Volt\{state, with, computed};
use App\Models\Product;
use App\Models\Category;

state(['search' => '', 'selectedCategory' => '']);

$products = computed(function () {
    return Product::query()
        ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
        ->when($this->selectedCategory, fn($q) => $q->where('category_id', $this->selectedCategory))
        ->with(['category', 'variants'])
        ->latest()
        ->paginate(12);
});

$categories = computed(fn() => Category::all());

$addToCart = function ($productId) {
    $product = Product::with('variants')->find($productId);
    $variant = $product->variants->first();
    
    if (!$variant) return;

    $cart = session()->get('cart', []);
    $id = $variant->id;
    
    if(isset($cart[$id])) {
        $cart[$id]['quantity']++;
    } else {
        $cart[$id] = [
            "name" => $product->name,
            "quantity" => 1,
            "price" => $variant->price,
            "image" => $product->getFirstMediaUrl('default')
        ];
    }
    
    session()->put('cart', $cart);
    $this->dispatch('cart-updated');
};

?>

<div>
    <div class="flex flex-col md:flex-row gap-4 mb-8">
        <div class="flex-1">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar productos..." class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
        </div>
        <div class="w-full md:w-64">
            <select wire:model.live="selectedCategory" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                <option value="">Todas las categorías</option>
                @foreach($this->categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($this->products as $product)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition duration-200 group">
                <div class="aspect-square bg-gray-100 relative overflow-hidden">
                    @if($product->hasMedia('default'))
                        <img src="{{ $product->getFirstMediaUrl('default') }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    @else
                        <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="p-4">
                    <div class="text-xs font-semibold text-orange-600 uppercase tracking-wider mb-1">{{ $product->category->name }}</div>
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-orange-700 transition duration-150">
                        <a href="/products/{{ $product->slug }}">{{ $product->name }}</a>
                    </h3>
                    <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ strip_tags($product->description) }}</p>

                    @php $cheapestVariant = $product->variants->sortBy('price')->first(); @endphp
                    @if($cheapestVariant && ($cheapestVariant->presentation || $cheapestVariant->weight))
                        <div class="mt-1 text-xs text-gray-400 font-medium">
                            {{ $cheapestVariant->presentation }}
                            @if($cheapestVariant->weight)
                                {{ $cheapestVariant->presentation ? '·' : '' }}
                                {{ rtrim(rtrim((string) $cheapestVariant->weight, '0'), '.') }} {{ $cheapestVariant->weight_unit }}
                            @endif
                        </div>
                    @endif

                    <div class="mt-4 flex items-center justify-between gap-2">
                        <span class="text-xl font-black text-gray-900">
                            ${{ number_format($product->variants->min('price'), 2) }}
                        </span>
                        <div class="flex gap-1">
                            <a href="/products/{{ $product->slug }}" class="p-2 text-gray-400 hover:text-orange-600 transition" title="Ver detalles">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <button 
                                wire:click="addToCart({{ $product->id }})"
                                class="bg-orange-600 text-white p-2 rounded-lg hover:bg-orange-700 transition duration-150"
                                title="Añadir al carrito"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center">
                <p class="text-gray-500 text-lg">No se encontraron productos que coincidan con tu búsqueda.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $this->products->links() }}
    </div>
</div>