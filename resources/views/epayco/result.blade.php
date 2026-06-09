<x-app-layout>
<div class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
        <div class="p-8 text-center">
            @if($status === 'success')
                <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            @elseif($status === 'pending')
                <div class="w-20 h-20 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            @else
                <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
            @endif

            <h1 class="text-3xl font-black text-gray-900 mb-2">{{ $message }}</h1>
            <p class="text-gray-500 mb-8">Referencia de pago: <span class="font-bold text-gray-700">#{{ $data->x_ref_payco ?? 'N/A' }}</span></p>

            <div class="bg-gray-50 rounded-2xl p-6 mb-8 text-left space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Monto:</span>
                    <span class="font-bold text-gray-900">${{ number_format($data->x_amount ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Fecha:</span>
                    <span class="font-bold text-gray-900">{{ $data->x_transaction_date ?? now()->format('Y-m-d') }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Estado:</span>
                    <span class="font-bold {{ $status === 'success' ? 'text-green-600' : 'text-orange-600' }}">
                        {{ $data->x_response ?? 'Desconocido' }}
                    </span>
                </div>
            </div>

            <a href="/" class="block w-full bg-gray-900 text-white py-4 rounded-xl font-bold hover:bg-black transition duration-150">
                VOLVER A LA TIENDA
            </a>
        </div>
    </div>
</div>
</x-app-layout>
