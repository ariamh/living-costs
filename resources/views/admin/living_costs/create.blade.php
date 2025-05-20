<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Living Cost') }}
        </h2>
    </x-slot>

    {{-- Floating success notification --}}
    @if (session('success'))
        <div class="fixed bottom-4 right-4 z-50 bg-green-500 text-white px-4 py-2 rounded shadow-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 font-medium text-sm text-red-600">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form action="{{ route('admin.living_costs.store') }}" method="POST">
                    @csrf

                    {{-- Select City --}}
                    <div class="mb-6">
                        <label for="city_id" class="block font-medium text-sm text-gray-700">
                            City <span class="text-red-500">*</span>
                        </label>
                        <select name="city_id" id="city_id" class="form-select w-full mt-1 rounded-lg border-gray-300" required>
                            <option value="">-- Select City --</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Cost Inputs --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach ([
                            'housing' => 'Housing',
                            'food' => 'Food',
                            'transportation' => 'Transportation',
                            'utilities' => 'Utilities',
                            'healthcare' => 'Healthcare',
                            'entertainment' => 'Entertainment',
                            'other' => 'Others'
                        ] as $field => $label)
                            <div>
                                <label for="{{ $field }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
                                <input type="number" step="0.01" name="{{ $field }}" id="{{ $field }}"
                                       class="form-input mt-1 block w-full rounded-lg border-gray-300"
                                       placeholder="e.g. 2000000"
                                       value="{{ old($field) }}">
                            </div>
                        @endforeach
                    </div>

                    {{-- Action Buttons --}}
                    <div class="mt-8 flex items-center">
                        <button type="submit"
                                class="px-5 py-2 bg-green-600 text-white font-semibold rounded-md hover:bg-green-700 transition">
                            Save
                        </button>

                        <a href="{{ route('admin.living_costs.index') }}"
                           class="ml-4 px-5 py-2 bg-gray-200 text-gray-800 font-semibold rounded-md hover:bg-gray-300 transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Optional JS push for formatting --}}
    @push('scripts')
    <script>
        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', function (e) {
                let value = e.target.value.replace(/[^\d.]/g, '');
                e.target.value = value;
            });
        });
    </script>
    @endpush
</x-app-layout>
