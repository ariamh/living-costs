<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('City Map') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div id="map" class="w-full h-[500px] rounded-md"></div>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="http://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
    <script src="http://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const map = L.map('map').setView([-6.2, 106.8], 6);

            L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            @foreach ($cities as $city)
                L.marker([{{ $city->latitude }}, {{ $city->longitude }}])
                    .addTo(map)
                    .bindPopup("<b>{{ $city->name }}</b><br>{{ $city->province }}");
            @endforeach
        });
    </script>
    @endpush
</x-app-layout>
