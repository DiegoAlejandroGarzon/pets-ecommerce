<?php

use function Livewire\Volt\{computed};
use App\Models\Category;

$categories = computed(fn() => Category::withCount('products')->get());

?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-4xl font-black text-gray-900 mb-8">Categorías</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach($this->categories as $category)
            <a href="/?selectedCategory={{ $category->id }}" class="group relative bg-white rounded-3xl p-8 shadow-sm border border-gray-100 hover:shadow-xl transition duration-300 overflow-hidden">
                <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition duration-300">
                    <svg class="w-32 h-32 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                    </svg>
                </div>
                
                <h2 class="text-2xl font-bold text-gray-900 mb-2 group-hover:text-orange-600 transition duration-300">{{ $category->name }}</h2>
                <p class="text-gray-500">{{ $category->products_count }} productos disponibles</p>
                
                <div class="mt-6 inline-flex items-center text-orange-600 font-bold">
                    Explorar
                    <svg class="w-5 h-5 ml-2 transition-transform duration-300 group-hover:translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </div>
            </a>
        @endforeach
    </div>
</div>
