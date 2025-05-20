<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('City List') }}
        </h2>
    </x-slot>

    @if (session('success'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50">
            {{ session('success') }}
        </div>
    @endif

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <a href="{{ route('admin.cities.create') }}"
               class="mb-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-md font-medium hover:bg-blue-700 transition">
               📝 Add City
            </a>

            <div class="bg-white overflow-x-auto shadow-sm sm:rounded-lg">
                <table class="min-w-full table-auto border border-gray-200">
                    <thead class="bg-gray-100 text-gray-700 text-sm font-semibold uppercase">
                        <tr>
                            <th class="px-4 py-3 border">Name</th>
                            <th class="px-4 py-3 border">Province</th>
                            <th class="px-4 py-3 border">Country</th>
                            <th class="px-4 py-3 border">Latitude</th>
                            <th class="px-4 py-3 border">Longitude</th>
                            <th class="px-4 py-3 border text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700">
                        @forelse($cities as $city)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 border">{!! $city->name !!}</td>
                            <td class="px-4 py-2 border">{{ $city->province }}</td>
                            <td class="px-4 py-2 border">{{ $city->country }}</td>
                            <td class="px-4 py-2 border">{{ $city->latitude }}</td>
                            <td class="px-4 py-2 border">{{ $city->longitude }}</td>
                            <td class="px-4 py-2 border text-center">
                                <a href="{{ route('admin.cities.edit', $city->id) }}" class="text-yellow-600 hover:underline">✏️ Edit</a>
                                <form action="{{ route('admin.cities.destroy', $city->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this city?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline ml-2">🗑️ Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-gray-500 italic">No cities found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $cities->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
