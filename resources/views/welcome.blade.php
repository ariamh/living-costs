<x-app-layout>
    <div x-data="livingCostSearch()" class="min-h-screen flex items-center justify-center bg-cover bg-center" style="background-image: url('/img/bg-mountain.jpg');">
        <div class="text-center p-8 bg-white bg-opacity-80 rounded-2xl shadow-xl max-w-xl w-full">
            <h2 class="text-2xl md:text-3xl font-semibold mb-6 text-gray-800">Find Living Cost by City</h2>

            <form @submit.prevent="searchCity" class="flex flex-col sm:flex-row items-center gap-3">
                <input
                    x-model="city"
                    type="text"
                    placeholder="Enter city name..."
                    class="flex-1 px-4 py-3 rounded-full border border-gray-300 focus:outline-none focus:ring-2"
                    required
                >
                <button
                    type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-6 py-3 rounded-full transition-all"
                >
                    Search
                </button>
            </form>

            <!-- ✅ Loading state ditampilkan saat pencarian -->
            <div x-show="isLoading" class="mt-4 text-sm text-blue-600 flex items-center justify-center gap-2 animate-pulse">
                <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8h4z">
                    </path>
                </svg>
                Loading City Data...
            </div>

            <!-- Modal -->
            <div x-show="showModal" @click.away="showModal = false" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-90"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md relative ring-1 ring-gray-200">
                    <button @click="showModal = false"
                        class="absolute top-3 right-3 text-gray-400 hover:text-red-600 text-2xl font-bold leading-none">&times;</button>

                    <div class="flex items-center gap-3 mb-4">
                        <div class="bg-green-100 text-green-700 p-2 rounded-full">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 10h11m0 0h8m-8 0a6 6 0 11-12 0 6 6 0 0112 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">
                            Cost of Living in <span x-text="result.city.name" class="text-green-600"></span>
                        </h3>
                    </div>

                    <div class="text-sm text-gray-700 space-y-2">
                        <template x-for="[key, label] in Object.entries({
                        housing: 'Housing',
                        food: 'Food',
                        transportation: 'Transportation',
                        utilities: 'Utilities',
                        healthcare: 'Healthcare',
                        entertainment: 'Entertainment',
                        other: 'Other'
                    })" :key="key">
                            <div class="flex justify-between border-b-2 pb-1">
                                <span x-text="label"></span>
                                <span x-text="'Rp' + formatRupiah(result[key])" class="font-medium text-gray-800"></span>
                            </div>
                        </template>

                        <div class="flex justify-between pt-3 text-lg font-bold text-green-700 mt-4">
                            <span>Total</span>
                            <span x-text="'Rp' + formatRupiah(result.total_estimation)"></span>
                        </div>
                    </div>
                    <div class="mt-4 text-left">
                        <h4 class="font-semibold text-gray-800 mb-2">AI Analysis</h4>
                        <p class="text-sm text-gray-700 whitespace-pre-line text-justify leading-relaxed" x-text="result.analysis || 'AI analysis not available.'"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine.js -->
    <script>
        function livingCostSearch() {
            return {
                city: '',
                result: null,
                showModal: false,
                isLoading: false,
                async searchCity() {
                    this.isLoading = true;
                    try {
                        const response = await fetch(`/api/search?city=${encodeURIComponent(this.city)}`);
                        const data = await response.json();

                        if (data.success) {
                            this.result = data.data;
                            this.result.analysis = data.analysis;
                            this.showModal = true;
                            setTimeout(() => {
                                this.city = '';
                            }, 100);
                        } else {
                            alert("City not found. Please try another.");
                            setTimeout(() => {
                                this.city = '';
                            }, 100);
                        }
                    } catch (error) {
                        alert("An error occurred while retrieving the data.");
                        console.error(error);
                    } finally{
                        this.isLoading = false;
                    }
                },
                formatRupiah(value) {
                    return new Intl.NumberFormat('id-ID').format(value);
                }
            };
        }
    </script>
</x-app-layout>
