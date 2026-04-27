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

?>

<div>
    <div class="flex flex-col md:flex-row gap-4 mb-8">
        <div class="flex-1">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar productos..." class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div class="w-full md:w-64">
            <select wire:model.live="selectedCategory" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                <div class="aspect-square bg-gray-100 relative">
                    {{-- Espacio para imagen con Spatie Media Library --}}
                    <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
                <div class="p-4">
                    <div class="text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-1">{{ $product->category->name }}</div>
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition duration-150">
                        <a href="/product/{{ $product->slug }}">{{ $product->name }}</a>
                    </h3>
                    <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ strip_tags($product->description) }}</p>
                    
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-xl font-black text-gray-900">
                            ${{ number_format($product->variants->min('price'), 2) }}
                        </span>
                        <button class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-indigo-700 transition duration-150">
                            Ver más
                        </button>
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