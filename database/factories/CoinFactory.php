<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coin>
 */
class CoinFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currentPrice = fake()->randomFloat(8, 0.00001, 5000);
        $high24h = $currentPrice * fake()->randomFloat(4, 1.01, 1.15);
        $low24h = $currentPrice * fake()->randomFloat(4, 0.85, 0.99);
        $priceChange24h = $currentPrice - $low24h;
        $priceChangePercentage24h = ($priceChange24h / $low24h) * 100;

        $circulatingSupply = fake()->randomFloat(8, 1000000, 100000000);
        $marketCap = (int) ($currentPrice * $circulatingSupply);
        // Ensure total_volume stays within decimal(20,8) limit: 999,999,999,999.99999999
        $totalVolume = min($currentPrice * $circulatingSupply * fake()->randomFloat(4, 0.01, 0.2), 999999999999);

        $ath = $currentPrice * fake()->randomFloat(4, 1.5, 10);
        $atl = $currentPrice * fake()->randomFloat(4, 0.1, 0.5);

        return [
            'slug' => fake()->unique()->slug(2),
            'symbol' => strtoupper(fake()->lexify('???')),
            'name' => fake()->words(2, true).' Coin',
            'image' => fake()->imageUrl(200, 200, 'crypto', true),
            'current_price' => $currentPrice,
            'market_cap' => $marketCap,
            'market_cap_rank' => fake()->numberBetween(1, 10000),
            'fully_diluted_valuation' => $marketCap * fake()->randomFloat(4, 1, 1.5),
            'total_volume' => $totalVolume,
            'high_24h' => $high24h,
            'low_24h' => $low24h,
            'price_change_24h' => $priceChange24h,
            'price_change_percentage_24h' => $priceChangePercentage24h,
            'market_cap_change_24h' => (int) ($marketCap * fake()->randomFloat(4, -0.1, 0.1)),
            'market_cap_change_percentage_24h' => fake()->randomFloat(5, -10, 10),
            'circulating_supply' => $circulatingSupply,
            'total_supply' => $circulatingSupply * fake()->randomFloat(4, 1, 1.2),
            'max_supply' => fake()->boolean(70) ? $circulatingSupply * fake()->randomFloat(4, 1.5, 3) : null,
            'ath' => $ath,
            'ath_change_percentage' => (($currentPrice - $ath) / $ath) * 100,
            'ath_date' => fake()->dateTimeBetween('-5 years', '-1 day'),
            'atl' => $atl,
            'atl_change_percentage' => (($currentPrice - $atl) / $atl) * 100,
            'atl_date' => fake()->dateTimeBetween('-5 years', '-1 day'),
            'roi' => fake()->boolean(30) ? [
                'times' => fake()->randomFloat(4, 0.5, 100),
                'currency' => 'usd',
                'percentage' => fake()->randomFloat(2, 50, 10000),
            ] : null,
            'last_updated' => fake()->dateTimeBetween('-1 hour', 'now'),
        ];
    }
}
