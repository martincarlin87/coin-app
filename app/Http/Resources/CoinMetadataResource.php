<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\CoinMetadata;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class CoinMetadataResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            ...$this->resource->only((new CoinMetadata())->getFillable()),
        ];
    }
}
