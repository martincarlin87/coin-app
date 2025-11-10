<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Coin;

/**
 * Get a coin with navigation (next/previous) coin slugs based on filters.
 */
final readonly class GetCoinWithNavigation
{
    /**
     * Execute the action to add navigation data to the coin.
     *
     * @param  Coin  $coin  The coin to add navigation to
     * @param  string|null  $search  Optional search filter to apply
     * @param  int  $length  Maximum number of results in the filtered set
     * @return Coin The coin with next_coin_slug and previous_coin_slug set
     */
    public function execute(Coin $coin, ?string $search = null, int $length = 10): Coin
    {
        // Build the base query with the same filters as the index page
        $baseQuery = Coin::query()
            ->when($search !== null && $search !== '', function ($query) use ($search): void {
                $query->whereAny(['name', 'symbol'], 'like', "%{$search}%");
            })
            ->orderBy('market_cap_rank', 'asc');

        // Get all matching coin slugs to determine position in filtered results
        $filteredCoins = $baseQuery->pluck('slug', 'id')->toArray();
        $currentPosition = array_search($coin->id, array_keys($filteredCoins));

        // Only show next/previous within the filtered result set (limited by length)
        if ($currentPosition !== false) {
            $coinSlugs = array_values($filteredCoins);

            // Previous coin (if not first in list)
            if ($currentPosition > 0) {
                $coin->previous_coin_slug = $coinSlugs[$currentPosition - 1];
            }

            // Next coin (if not last in the length-limited list)
            if ($currentPosition < $length - 1 && isset($coinSlugs[$currentPosition + 1])) {
                $coin->next_coin_slug = $coinSlugs[$currentPosition + 1];
            }
        }

        return $coin;
    }
}
