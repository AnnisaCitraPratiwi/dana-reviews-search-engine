@extends('layouts.app')

@section('content')
<div x-data="dashboardApp()">
    <!-- Scraping Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-2xl font-bold mb-4">Scrape Reviews</h2>
        <form @submit.prevent="scrapeReviews" class="flex gap-4 items-end">
            <div>
                <label class="block text-sm font-medium mb-2">App ID</label>
                <input type="text" x-model="scrapeForm.app_id" 
                       class="border rounded px-3 py-2 w-48" 
                       placeholder="id.dana">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Count</label>
                <input type="number" x-model="scrapeForm.count" 
                       class="border rounded px-3 py-2 w-32" 
                       min="1" max="5000">
            </div>
            <button type="submit" :disabled="scraping"
                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50">
                <span x-show="!scraping">Scrape Reviews</span>
                <span x-show="scraping">Scraping...</span>
            </button>
        </form>
        <div x-show="scrapeMessage" class="mt-4 p-3 rounded" 
             :class="scrapeSuccess ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
            <span x-text="scrapeMessage"></span>
        </div>
    </div>

    <!-- Search Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-2xl font-bold mb-4">Search Reviews</h2>
        <form @submit.prevent="searchReviews" class="flex gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium mb-2">Search Query</label>
                <input type="text" x-model="searchQuery" 
                       class="border rounded px-3 py-2 w-full" 
                       placeholder="Enter search keywords...">
            </div>
            <button type="submit" :disabled="searching"
                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 disabled:opacity-50">
                <span x-show="!searching">Search</span>
                <span x-show="searching">Searching...</span>
            </button>
        </form>
    </div>

    <!-- Search Results -->
    <div x-show="searchResults.length > 0" class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h3 class="text-xl font-bold mb-4">Search Results</h3>
        <div class="space-y-4">
            <template x-for="result in searchResults" :key="result.reviewId">
                <div class="border-l-4 border-blue-500 pl-4 py-2">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <span class="font-medium" x-text="result.userName"></span>
                            <span class="text-yellow-500" x-text="'★'.repeat(result.score)"></span>
                        </div>
                        <span class="text-sm text-gray-500" x-text="new Date(result.at).toLocaleDateString()"></span>
                    </div>
                    <p class="text-gray-700 mb-2" x-text="result.content"></p>
                    <div class="text-sm text-blue-600">
                        Similarity Score: <span x-text="(result.similarity_score * 100).toFixed(2)"></span>%
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-2">Total Reviews</h3>
            <p class="text-3xl font-bold text-blue-600">{{ number_format($totalReviews) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-2">Average Score</h3>
            <p class="text-3xl font-bold text-green-600">{{ number_format($averageScore, 2) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-2">Score Distribution</h3>
            <div class="space-y-1">
                @foreach($scoreDistribution as $score)
                <div class="flex justify-between text-sm">
                    <span>{{ $score->score }} Star</span>
                    <span>{{ $score->count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
function dashboardApp() {
    return {
        // Scraping
        scraping: false,
        scrapeForm: {
            app_id: 'id.dana',
            count: 1000
        },
        scrapeMessage: '',
        scrapeSuccess: false,

        // Searching
        searching: false,
        searchQuery: '',
        searchResults: [],

        async scrapeReviews() {
            this.scraping = true;
            this.scrapeMessage = '';
            
            try {
                const response = await axios.post('/api/scrape', this.scrapeForm);
                this.scrapeMessage = response.data.message + ` (${response.data.count} reviews)`;
                this.scrapeSuccess = true;
                
                // Refresh page after successful scrape
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
                
            } catch (error) {
                this.scrapeMessage = error.response?.data?.message || 'An error occurred';
                this.scrapeSuccess = false;
            } finally {
                this.scraping = false;
            }
        },

        async searchReviews() {
            if (!this.searchQuery.trim()) return;
            
            this.searching = true;
            
            try {
                const response = await axios.post('/api/search', {
                    query: this.searchQuery,
                    limit: 20
                });
                
                this.searchResults = response.data.results;
                
            } catch (error) {
                alert(error.response?.data?.message || 'Search failed');
            } finally {
                this.searching = false;
            }
        }
    }
}
</script>
@endsection