<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

interface Coin {
    id: number;
    slug: string;
    symbol: string;
    name: string;
    image: string;
    current_price: string;
    market_cap: number;
    market_cap_rank: number;
    price_change_percentage_24h: string;
}

const coins = ref<Coin[]>([]);
const loading = ref(true);
const error = ref<string | null>(null);
const searchQuery = ref('');
const hasReceivedResponse = ref(false);

const fetchCoins = async () => {
    try {
        loading.value = true;
        error.value = null;
        const params: Record<string, string> = {};
        if (searchQuery.value) {
            params.search = searchQuery.value;
        }
        const response = await axios.get('/api/coins', { params });
        coins.value = response.data.data;
        hasReceivedResponse.value = true;
    } catch (err) {
        error.value = 'Failed to load coins';
        console.error(err);
        hasReceivedResponse.value = true;
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchCoins();
});

const formatPrice = (price: string) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(parseFloat(price));
};

const formatMarketCap = (marketCap: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        notation: 'compact',
        maximumFractionDigits: 2,
    }).format(marketCap);
};

const viewCoin = (coinId: number) => {
    const params = new URLSearchParams();
    if (searchQuery.value) {
        params.set('search', searchQuery.value);
    }

    const queryString = params.toString();
    router.visit(`/coins/${coinId}${queryString ? '?' + queryString : ''}`);
};

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const handleSearch = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        fetchCoins();
    }, 300);
};

const clearSearch = () => {
    loading.value = true;
    searchQuery.value = '';
    fetchCoins();
};
</script>

<template>
    <Head title="Top Cryptocurrencies" />

    <div class="min-h-screen bg-gray-50 p-6 dark:bg-gray-900">
        <div class="mx-auto max-w-7xl">
            <h1 class="mb-8 text-3xl font-bold text-gray-900 dark:text-white">
                Top 10 Cryptocurrencies
            </h1>

            <!-- Search Input -->
            <div class="mb-6">
                <div class="relative">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by name or symbol..."
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 pl-10 text-gray-900 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                        @input="handleSearch"
                    />
                    <svg
                        class="absolute left-3 top-2.5 h-5 w-5 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                    </svg>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center py-12">
                <div class="h-8 w-8 animate-spin rounded-full border-4 border-gray-300 border-t-blue-600"></div>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-red-800 dark:bg-red-900 dark:text-red-200">
                {{ error }}
            </div>

            <!-- Empty State -->
            <div v-else-if="hasReceivedResponse && coins.length === 0" class="rounded-lg bg-white p-12 text-center shadow dark:bg-gray-800">
                <svg
                    class="mx-auto h-16 w-16 text-gray-400"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                    {{ searchQuery ? 'No coins found' : 'No cryptocurrency data available' }}
                </h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ searchQuery
                        ? 'Try adjusting your search query or clearing the search to see all coins.'
                        : 'Get started by importing the latest cryptocurrency data from CoinGecko.' }}
                </p>
                <div v-if="!searchQuery" class="mt-6">
                    <code class="inline-block rounded-lg bg-gray-100 px-4 py-2 text-sm text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                        sail artisan app:import-coin-data
                    </code>
                </div>
                <button
                    v-if="searchQuery"
                    @click="clearSearch"
                    class="mt-4 cursor-pointer rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:bg-blue-500 dark:hover:bg-blue-600"
                >
                    Clear search
                </button>
            </div>

            <!-- Coins List -->
            <div v-else class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                Rank
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                Coin
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                Price
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                24h %
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">
                                Market Cap
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        <tr
                            v-for="coin in coins"
                            :key="coin.id"
                            class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
                            @click="viewCoin(coin.id)"
                        >
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                {{ coin.market_cap_rank }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img :src="coin.image" :alt="coin.name" class="h-8 w-8 rounded-full" />
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ coin.name }}
                                        </div>
                                        <div class="text-sm uppercase text-gray-500 dark:text-gray-400">
                                            {{ coin.symbol }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900 dark:text-gray-100">
                                {{ formatPrice(coin.current_price) }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <span
                                    :class="{
                                        'text-green-600 dark:text-green-400': parseFloat(coin.price_change_percentage_24h) > 0,
                                        'text-red-600 dark:text-red-400': parseFloat(coin.price_change_percentage_24h) < 0,
                                    }"
                                >
                                    {{ parseFloat(coin.price_change_percentage_24h).toFixed(2) }}%
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-gray-900 dark:text-gray-100">
                                {{ formatMarketCap(coin.market_cap) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
