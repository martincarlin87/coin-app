<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CoinMetadata>
 */
class CoinMetadataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'coin_id' => \App\Models\Coin::factory(),
            'web_slug' => fake()->slug(2),
            'asset_platform_id' => fake()->boolean(30) ? fake()->word() : null,
            'block_time_in_minutes' => fake()->boolean(70) ? fake()->numberBetween(1, 60) : null,
            'hashing_algorithm' => fake()->boolean(70) ? fake()->randomElement(['SHA-256', 'Scrypt', 'Ethash', 'X11', 'Blake2b']) : null,
            'categories' => fake()->boolean(80) ? fake()->randomElements(
                ['Cryptocurrency', 'Smart Contract Platform', 'DeFi', 'NFT', 'Exchange Token', 'Stablecoin', 'Meme'],
                fake()->numberBetween(1, 4)
            ) : null,
            'preview_listing' => fake()->boolean(10),
            'public_notice' => fake()->boolean(5) ? fake()->sentence() : null,
            'additional_notices' => fake()->boolean(5) ? [fake()->sentence()] : null,
            'genesis_date' => fake()->boolean(60) ? fake()->date() : null,
            'sentiment_votes_up_percentage' => fake()->boolean(80) ? fake()->randomFloat(2, 40, 90) : null,
            'sentiment_votes_down_percentage' => fake()->boolean(80) ? fake()->randomFloat(2, 10, 60) : null,
            'watchlist_portfolio_users' => fake()->boolean(80) ? fake()->numberBetween(1000, 1000000) : null,
            'platforms' => fake()->boolean(50) ? [
                'ethereum' => fake()->sha256(),
                'binance-smart-chain' => fake()->sha256(),
            ] : null,
            'detail_platforms' => fake()->boolean(50) ? [
                'ethereum' => [
                    'decimal_place' => 18,
                    'contract_address' => fake()->sha256(),
                ],
            ] : null,
            'localization' => fake()->boolean(90) ? [
                'en' => fake()->sentence(),
                'es' => fake()->sentence(),
                'fr' => fake()->sentence(),
            ] : null,
            'description' => fake()->boolean(90) ? [
                'en' => fake()->paragraph(3),
            ] : null,
            'links' => fake()->boolean(90) ? [
                'homepage' => [fake()->url()],
                'blockchain_site' => [fake()->url()],
                'official_forum_url' => [fake()->url()],
                'repos_url' => [
                    'github' => [fake()->url()],
                ],
            ] : null,
            'community_data' => fake()->boolean(80) ? [
                'twitter_followers' => fake()->numberBetween(1000, 5000000),
                'reddit_subscribers' => fake()->numberBetween(100, 1000000),
                'telegram_channel_user_count' => fake()->numberBetween(100, 500000),
            ] : null,
            'developer_data' => fake()->boolean(70) ? [
                'forks' => fake()->numberBetween(10, 10000),
                'stars' => fake()->numberBetween(50, 50000),
                'total_issues' => fake()->numberBetween(0, 1000),
                'closed_issues' => fake()->numberBetween(0, 800),
            ] : null,
            'status_updates' => fake()->boolean(20) ? [
                [
                    'description' => fake()->sentence(),
                    'created_at' => fake()->dateTimeThisMonth(),
                ],
            ] : null,
        ];
    }
}
