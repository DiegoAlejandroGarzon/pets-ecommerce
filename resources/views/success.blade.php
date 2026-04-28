<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
        <div class="bg-green-100 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-8 text-green-600">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        
        <h1 class="text-4xl font-black text-gray-900 mb-4">¡Pedido Realizado con Éxito!</h1>
        <p class="text-xl text-gray-600 mb-12">Gracias por confiar en Consentidos & Traviesos. Hemos recibido tu pedido y pronto nos pondremos en contacto contigo.</p>
        
        @if(session()->has('order_id'))
            <div class="bg-gray-50 inline-block p-6 rounded-2xl border border-gray-100 mb-12">
                <p class="text-sm text-gray-500 uppercase font-bold tracking-widest mb-1">Número de Pedido</p>
                <p class="text-3xl font-black text-gray-900">#{{ session('order_id') }}</p>
            </div>
        @endif
        
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/" class="bg-orange-600 text-white px-8 py-4 rounded-2xl font-bold hover:bg-orange-700 transition duration-150 w-full sm:w-auto">
                Seguir comprando
            </a>
            <a href="/admin" class="bg-gray-100 text-gray-700 px-8 py-4 rounded-2xl font-bold hover:bg-gray-200 transition duration-150 w-full sm:w-auto">
                Ver mis pedidos
            </a>
        </div>
    </div>
</x-app-layout>
