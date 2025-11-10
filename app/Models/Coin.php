<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents a coin from the GeckoCoin api.
 *
 * N.B. Please note that the 'id' attribute from the api response is saved as 'slug'
 * so that we retain the integer primary key.
 *
 * @see https://docs.coingecko.com/reference/coins-markets
 */
class Coin extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'symbol',
        'name',
        'image',
        'current_price',
        'market_cap',
        'market_cap_rank',
        'fully_diluted_valuation',
        'total_volume',
        'high_24h',
        'low_24h',
        'price_change_24h',
        'price_change_percentage_24h',
        'market_cap_change_24h',
        'market_cap_change_percentage_24h',
        'circulating_supply',
        'total_supply',
        'max_supply',
        'ath',
        'ath_change_percentage',
        'ath_date',
        'atl',
        'atl_change_percentage',
        'atl_date',
        'roi',
        'last_updated',
    ];

    protected function casts(): array
    {
        return [
            'current_price' => 'decimal:8',
            'market_cap' => 'integer',
            'market_cap_rank' => 'integer',
            'fully_diluted_valuation' => 'integer',
            'total_volume' => 'decimal:8',
            'high_24h' => 'decimal:8',
            'low_24h' => 'decimal:8',
            'price_change_24h' => 'decimal:8',
            'price_change_percentage_24h' => 'decimal:5',
            'market_cap_change_24h' => 'integer',
            'market_cap_change_percentage_24h' => 'decimal:5',
            'circulating_supply' => 'decimal:8',
            'total_supply' => 'decimal:8',
            'max_supply' => 'decimal:8',
            'ath' => 'decimal:8',
            'ath_change_percentage' => 'decimal:5',
            'ath_date' => 'datetime',
            'atl' => 'decimal:8',
            'atl_change_percentage' => 'decimal:5',
            'atl_date' => 'datetime',
            'roi' => 'array',
            'last_updated' => 'datetime',
        ];
    }

    public function metadata(): HasOne
    {
        return $this->hasOne(CoinMetadata::class);
    }
}
