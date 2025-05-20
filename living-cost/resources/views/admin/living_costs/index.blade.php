<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Living Cost Data') }}
        </h2>
    </x-slot>

    {{-- Floating success alert --}}
    @if(session('success'))
        <div class="fixed bottom-4 right-4 z-50 bg-green-500 text-white px-4 py-2 rounded shadow-lg animate-fade">
            {{ session('success') }}
        </div>
    @endif

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <a href="{{ route('admin.living_costs.create') }}"
               class="mb-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-md font-semibold hover:bg-blue-700 transition">
               📝 Add New Data
            </a>

            <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg">
                <table class="min-w-full border-collapse table-auto border border-gray-200">
                    <thead class="bg-gray-100 text-gray-700 uppercase text-sm font-semibold">
                        <tr>
                            <th class="border px-4 py-3">City</th>
                            <th class="border px-4 py-3">Housing</th>
                            <th class="border px-4 py-3">Food</th>
                            <th class="border px-4 py-3">Transportation</th>
                            <th class="border px-4 py-3">Total</th>
                            <th class="border px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700">
                        @forelse($costs as $cost)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="border px-4 py-2">{{ $cost->city->name }}</td>
                            <td class="border px-4 py-2">Rp{{ number_format($cost->housing, 0, ',', '.') }}</td>
                            <td class="border px-4 py-2">Rp{{ number_format($cost->food, 0, ',', '.') }}</td>
                            <td class="border px-4 py-2">Rp{{ number_format($cost->transportation, 0, ',', '.') }}</td>
                            <td class="border px-4 py-2 font-semibold text-green-700">Rp{{ number_format($cost->total_estimation, 0, ',', '.') }}</td>
                            <td class="border px-4 py-2 text-center">
                                <a href="{{ route('admin.living_costs.edit', $cost->id) }}"
                                   class="inline-block text-sm text-yellow-600 hover:text-yellow-800 font-medium mr-2">
                                    ✏️ Edit
                                </a>
                                <form action="{{ route('admin.living_costs.destroy', $cost->id) }}" method="POST"
                                      class="inline-block"
                                      onsubmit="return confirm('Are you sure to delete this data?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">
                                        🗑️ Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="border px-4 py-4 text-center text-gray-500 italic">
                                No living cost data available.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $costs->links() }}
            </div>
        </div>
    </div>

    {{-- Optional animation --}}
    @push('styles')
    <style>
        @keyframes fade {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade {
            animation: fade 0.4s ease-out;
        }
    </style>
    @endpush
</x-app-layout>
