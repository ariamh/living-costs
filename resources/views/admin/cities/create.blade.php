<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add City') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Show validation errors --}}
            @if ($errors->any())
                <div class="mb-4 font-medium text-sm text-red-600">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Form card --}}
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form action="{{ route('admin.cities.store') }}" method="POST">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- City Name --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                City Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="name"
                                value="{{ old('name') }}"
                                class="form-input w-full mt-1 rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500">
                        </div>

                        {{-- Latitude --}}
                        <div>
                            <label for="latitude" class="block text-sm font-medium text-gray-700">
                                Latitude
                            </label>
                            <input type="number" name="latitude" id="latitude" step="any"
                                value="{{ old('latitude') }}"
                                class="form-input w-full mt-1 rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500">
                        </div>

                        {{-- Province --}}
                        <div>
                            <label for="province" class="block text-sm font-medium text-gray-700">
                                Province
                            </label>
                            <input type="text" name="province" id="province"
                                value="{{ old('province') }}"
                                class="form-input w-full mt-1 rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500">
                        </div>

                        {{-- Longitude --}}
                        <div>
                            <label for="longitude" class="block text-sm font-medium text-gray-700">
                                Longitude
                            </label>
                            <input type="number" name="longitude" id="longitude" step="any"
                                value="{{ old('longitude') }}"
                                class="form-input w-full mt-1 rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500">
                        </div>

                        {{-- Country --}}
                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700">
                                Country
                            </label>
                            <input type="text" name="country" id="country"
                                value="{{ old('country', 'Indonesia') }}"
                                class="form-input w-full mt-1 rounded-lg border-gray-300 focus:ring-green-500 focus:border-green-500">
                        </div>

                    </div>

                    {{-- Action Buttons --}}
                    <div class="mt-6 flex items-center">
                        <button type="submit"
                            class="px-5 py-2 bg-green-600 text-white rounded-md font-semibold hover:bg-green-700 transition">
                            Save
                        </button>

                        <a href="{{ route('admin.cities.index') }}"
                            class="ml-4 px-5 py-2 bg-gray-300 text-gray-800 rounded-md font-semibold hover:bg-gray-400 transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
