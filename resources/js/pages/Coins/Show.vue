<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

interface Props {
    coinSlug: string;
}

interface CoinMetadata {
    id: number;
    coin_id: number;
    web_slug: string | null;
    asset_platform_id: string | null;
    block_time_in_minutes: number | null;
    hashing_algorithm: string | null;
    categories: string[] | null;
    preview_listing: boolean;
    public_notice: string | null;
    additional_notices: any[] | null;
    genesis_date: string | null;
    sentiment_votes_up_percentage: string | null;
    sentiment_votes_down_percentage: string | null;
    watchlist_portfolio_users: number | null;
    platforms: any | null;
    detail_platforms: any | null;
    localization: any | null;
    description: Record<string, string> | null;
    links: any | null;
    community_data: any | null;
    developer_data: any | null;
    status_updates: any[] | null;
}

interface Coin {
    id: number;
    slug: string;
    symbol: string;
    name: string;
    image: string;
    current_price: string;
    market_cap: number;
    market_cap_rank: number;
    fully_diluted_valuation: number | null;
    total_volume: string;
    high_24h: string;
    low_24h: string;
    price_change_24h: string;
    price_change_percentage_24h: string;
    market_cap_change_24h: number;
    market_cap_change_percentage_24h: string;
    circulating_supply: string;
    total_supply: string;
    max_supply: string | null;
    ath: string;
    ath_change_percentage: string;
    ath_date: string;
    atl: string;
    atl_change_percentage: string;
    atl_date: string;
    last_updated: string;
    next_coin_slug?: string;
    previous_coin_slug?: string;
    metadata?: CoinMetadata;
}

const props = defineProps<Props>();

const coin = ref<Coin | null>(null);
const loading = ref(true);
const error = ref<string | null>(null);

// Extract query parameters from URL
const urlParams = new URLSearchParams(window.location.search);
const searchQuery = urlParams.get('search') || '';
const length = urlParams.get('length') || '10';

const fetchCoin = async () => {
    try {
        loading.value = true;
        error.value = null;
        const params: Record<string, string> = {
            length,
        };
        if (searchQuery) {
            params.search = searchQuery;
        }
        const response = await axios.get(`/api/coins/${props.coinSlug}`, { params });
        coin.value = response.data.data;
    } catch (err) {
        error.value = 'Failed to load coin details';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchCoin();
});

const formatPrice = (price: string) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(parseFloat(price));
};

const formatNumber = (num: string | number) => {
    return new Intl.NumberFormat('en-US', {
        maximumFractionDigits: 2,
    }).format(typeof num === 'string' ? parseFloat(num) : num);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const navigateToCoin = (coinSlug: string) => {
    const params = new URLSearchParams();
    if (searchQuery) {
        params.set('search', searchQuery);
    }

    const queryString = params.toString();
    router.visit(`/coins/${coinSlug}${queryString ? '?' + queryString : ''}`);
};
</script>

<template>
    <Head :title="coin?.name ?? 'Loading...'" />

    <div class="min-h-screen bg-gray-50 p-6 dark:bg-gray-900">
        <div class="mx-auto max-w-5xl">
            <!-- Back Link -->
            <Link
                href="/coins"
                class="mb-6 inline-flex items-center text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to list
            </Link>

            <!-- Navigation Buttons -->
            <div v-if="coin && (coin.previous_coin_slug || coin.next_coin_slug)" class="mb-6 flex justify-between">
                <button
                    v-if="coin.previous_coin_slug"
                    @click="navigateToCoin(coin.previous_coin_slug)"
                    class="inline-flex cursor-pointer items-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Previous Coin
                </button>
                <button
                    v-if="coin.next_coin_slug"
                    @click="navigateToCoin(coin.next_coin_slug)"
                    class="inline-flex cursor-pointer items-center rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                    :class="{ 'ml-auto': !coin.previous_coin_slug }"
                >
                    Next Coin
                    <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center py-12">
                <div class="h-8 w-8 animate-spin rounded-full border-4 border-gray-300 border-t-blue-600"></div>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-red-800 dark:bg-red-900 dark:text-red-200">
                {{ error }}
            </div>

            <!-- Coin Details -->
            <div v-else-if="coin" class="space-y-6">
                <!-- Header -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <div class="flex items-center gap-4">
                        <img :src="coin.image" :alt="coin.name" class="h-16 w-16 rounded-full" />
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                                {{ coin.name }}
                            </h1>
                            <p class="text-lg uppercase text-gray-500 dark:text-gray-400">
                                {{ coin.symbol }}
                            </p>
                        </div>
                        <div class="ml-auto text-right">
                            <div class="text-3xl font-bold text-gray-900 dark:text-white">
                                {{ formatPrice(coin.current_price) }}
                            </div>
                            <div
                                :class="{
                                    'text-green-600 dark:text-green-400': parseFloat(coin.price_change_percentage_24h) > 0,
                                    'text-red-600 dark:text-red-400': parseFloat(coin.price_change_percentage_24h) < 0,
                                }"
                            >
                                {{ parseFloat(coin.price_change_percentage_24h).toFixed(2) }}% (24h)
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Market Stats -->
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Market Cap Rank</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">#{{ coin.market_cap_rank }}</div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                        <div class="text-sm text-gray-500 dark:text-gray-400">Market Cap</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            ${{ formatNumber(coin.market_cap) }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                        <div class="text-sm text-gray-500 dark:text-gray-400">24h Volume</div>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ formatPrice(coin.total_volume) }}
                        </div>
                    </div>
                </div>

                <!-- Price Stats -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">24h Price Range</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Low</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ formatPrice(coin.low_24h) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">High</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ formatPrice(coin.high_24h) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Supply Info -->
                <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                    <h2 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Supply</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Circulating Supply</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ formatNumber(coin.circulating_supply) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Total Supply</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ formatNumber(coin.total_supply) }}
                            </span>
                        </div>
                        <div v-if="coin.max_supply" class="flex justify-between">
                            <span class="text-gray-600 dark:text-gray-400">Max Supply</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ formatNumber(coin.max_supply) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- All-Time High/Low -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-2 text-lg font-bold text-gray-900 dark:text-white">All-Time High</h3>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ formatPrice(coin.ath) }}
                        </div>
                        <div class="mt-1 text-sm text-red-600 dark:text-red-400">
                            {{ parseFloat(coin.ath_change_percentage).toFixed(2) }}%
                        </div>
                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ formatDate(coin.ath_date) }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-2 text-lg font-bold text-gray-900 dark:text-white">All-Time Low</h3>
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ formatPrice(coin.atl) }}
                        </div>
                        <div class="mt-1 text-sm text-green-600 dark:text-green-400">
                            +{{ parseFloat(coin.atl_change_percentage).toFixed(2) }}%
                        </div>
                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ formatDate(coin.atl_date) }}
                        </div>
                    </div>
                </div>

                <!-- Metadata Section -->
                <div v-if="coin.metadata" class="space-y-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Additional Information</h2>

                    <!-- Description -->
                    <div v-if="coin.metadata.description?.en" class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-3 text-lg font-bold text-gray-900 dark:text-white">About</h3>
                        <div class="text-gray-700 dark:text-gray-300" v-html="coin.metadata.description.en"></div>
                    </div>

                    <!-- Technical Details & Sentiment Grid -->
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div v-if="coin.metadata.genesis_date" class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Genesis Date</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ formatDate(coin.metadata.genesis_date) }}
                            </div>
                        </div>
                        <div v-if="coin.metadata.hashing_algorithm" class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Hashing Algorithm</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ coin.metadata.hashing_algorithm }}
                            </div>
                        </div>
                        <div v-if="coin.metadata.block_time_in_minutes" class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Block Time</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ coin.metadata.block_time_in_minutes }} min
                            </div>
                        </div>
                        <div v-if="coin.metadata.sentiment_votes_up_percentage" class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Sentiment Up</div>
                            <div class="text-lg font-semibold text-green-600 dark:text-green-400">
                                {{ parseFloat(coin.metadata.sentiment_votes_up_percentage).toFixed(1) }}%
                            </div>
                        </div>
                        <div v-if="coin.metadata.sentiment_votes_down_percentage" class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Sentiment Down</div>
                            <div class="text-lg font-semibold text-red-600 dark:text-red-400">
                                {{ parseFloat(coin.metadata.sentiment_votes_down_percentage).toFixed(1) }}%
                            </div>
                        </div>
                        <div v-if="coin.metadata.watchlist_portfolio_users" class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                            <div class="text-sm text-gray-500 dark:text-gray-400">Watchlist Users</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ formatNumber(coin.metadata.watchlist_portfolio_users) }}
                            </div>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div v-if="coin.metadata.categories && coin.metadata.categories.length > 0" class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-3 text-lg font-bold text-gray-900 dark:text-white">Categories</h3>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="category in coin.metadata.categories"
                                :key="category"
                                class="rounded-full bg-blue-100 px-3 py-1 text-sm font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                            >
                                {{ category }}
                            </span>
                        </div>
                    </div>

                    <!-- Links -->
                    <div v-if="coin.metadata.links" class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
                        <h3 class="mb-3 text-lg font-bold text-gray-900 dark:text-white">Links</h3>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <a
                                v-if="coin.metadata.links.homepage && coin.metadata.links.homepage[0]"
                                :href="coin.metadata.links.homepage[0]"
                                target="_blank"
                                class="inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Official Website
                            </a>
                            <a
                                v-if="coin.metadata.links.blockchain_site && coin.metadata.links.blockchain_site[0]"
                                :href="coin.metadata.links.blockchain_site[0]"
                                target="_blank"
                                class="inline-flex items-center text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Blockchain Explorer
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
