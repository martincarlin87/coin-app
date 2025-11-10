<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoinMetadata extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'coin_metadata';

    protected $fillable = [
        'coin_id',
        'web_slug',
        'asset_platform_id',
        'block_time_in_minutes',
        'hashing_algorithm',
        'categories',
        'preview_listing',
        'public_notice',
        'additional_notices',
        'genesis_date',
        'sentiment_votes_up_percentage',
        'sentiment_votes_down_percentage',
        'watchlist_portfolio_users',
        'platforms',
        'detail_platforms',
        'localization',
        'description',
        'links',
        'community_data',
        'developer_data',
        'status_updates',
    ];

    protected function casts(): array
    {
        return [
            'coin_id' => 'integer',
            'block_time_in_minutes' => 'integer',
            'categories' => 'array',
            'preview_listing' => 'boolean',
            'additional_notices' => 'array',
            'genesis_date' => 'date',
            'sentiment_votes_up_percentage' => 'decimal:2',
            'sentiment_votes_down_percentage' => 'decimal:2',
            'watchlist_portfolio_users' => 'integer',
            'platforms' => 'array',
            'detail_platforms' => 'array',
            'localization' => 'array',
            'description' => 'array',
            'links' => 'array',
            'community_data' => 'array',
            'developer_data' => 'array',
            'status_updates' => 'array',
        ];
    }

    public function coin(): BelongsTo
    {
        return $this->belongsTo(Coin::class);
    }
}
