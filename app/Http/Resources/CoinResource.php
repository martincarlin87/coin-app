<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Coin;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CoinResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            ...$this->resource->only((new Coin)->getFillable()),
            'next_coin_slug' => $this->when(isset($this->next_coin_slug), $this->next_coin_slug),
            'previous_coin_slug' => $this->when(isset($this->previous_coin_slug), $this->previous_coin_slug),
            'metadata' => new CoinMetadataResource($this->whenLoaded('metadata')),
        ];
    }
}
