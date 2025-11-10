<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CoinMetadataResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'coin_id' => $this->coin_id,
            'web_slug' => $this->web_slug,
            'asset_platform_id' => $this->asset_platform_id,
            'block_time_in_minutes' => $this->block_time_in_minutes,
            'hashing_algorithm' => $this->hashing_algorithm,
            'categories' => $this->categories,
            'preview_listing' => $this->preview_listing,
            'public_notice' => $this->public_notice,
            'additional_notices' => $this->additional_notices,
            'genesis_date' => $this->genesis_date,
            'sentiment_votes_up_percentage' => $this->sentiment_votes_up_percentage,
            'sentiment_votes_down_percentage' => $this->sentiment_votes_down_percentage,
            'watchlist_portfolio_users' => $this->watchlist_portfolio_users,
            'platforms' => $this->platforms,
            'detail_platforms' => $this->detail_platforms,
            'localization' => $this->localization,
            'description' => $this->description,
            'links' => $this->links,
            'community_data' => $this->community_data,
            'developer_data' => $this->developer_data,
            'status_updates' => $this->status_updates,
        ];
    }
}
