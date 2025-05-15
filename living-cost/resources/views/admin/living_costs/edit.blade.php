<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Living Cost Data') }}
        </h2>
    </x-slot>

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
                <form action="{{ route('admin.living_costs.update', $livingCost->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="city_id" class="block font-medium text-sm text-gray-700">
                            City <span class="text-red-500">*</span>
                        </label>
                        <select name="city_id" id="city_id" class="form-select w-full mt-1 rounded-lg border-gray-300" required>
                            <option value="">-- Select City --</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ $livingCost->city_id == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ([
                            'housing' => 'Housing',
                            'food' => 'Food',
                            'transportation' => 'Transportation',
                            'utilities' => 'Utilities',
                            'healthcare' => 'Healthcare',
                            'entertainment' => 'Entertainment',
                            'other' => 'Others'
                        ] as $field => $label)
                            <div class="mb-4">
                                <label for="{{ $field }}">{{ $label }}</label>
                                <input type="number" name="{{ $field }}" id="{{ $field }}"
                                       class="form-input w-full rounded-lg border-gray-300"
                                       value="{{ old($field, (int) $livingCost->$field) }}">
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex items-center">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Update
                        </button>

                        <a href="{{ route('admin.living_costs.index') }}"
                           class="ml-3 inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-sm text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition ease-in-out duration-150">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
