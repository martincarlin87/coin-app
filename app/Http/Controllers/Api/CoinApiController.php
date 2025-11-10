<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\GetCoinWithNavigation;
use App\Http\Controllers\Controller;
use App\Http\Resources\CoinResource;
use App\Models\Coin;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CoinApiController extends Controller
{
    /**
     * Display the top 10 coins by market cap rank.
     * Results are cached for 5 minutes to reduce database load.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'sort' => 'in:asc,desc',
            'start' => 'integer',
            'length' => 'integer',
            'search' => 'string|max:255',
        ]);

        // Create a unique cache key based on query parameters
        $cacheKey = 'coins:index:'.md5(json_encode([
            'sort' => $request->input('sort', 'asc'),
            'start' => $request->input('start', 0),
            'length' => $request->input('length', 10),
            'search' => $request->input('search', ''),
        ]));

        // Cache for 5 minutes (300 seconds)
        $coins = Cache::remember($cacheKey, 300, function () use ($request): Collection {
            $length = $request->input('length', 10);

            $query = Coin::query()
                // Limit to top coins by market cap rank (based on length parameter)
                ->where('market_cap_rank', '<=', $length)
                // Filter results that match the search term, if specified
                ->when($request->filled('search'), function ($query) use ($request): void {
                    $search = $request->input('search');
                    $query->whereAny(['name', 'symbol'], 'like', "%{$search}%");
                })
                // Default to ascending order if not specified
                ->orderBy('market_cap_rank', $request->input('sort', 'asc'));

            // Apply pagination if start parameter is provided
            if ($request->filled('start')) {
                $query->skip($request->input('start'))->take($length);
            }

            return $query->get();
        });

        return CoinResource::collection($coins);
    }

    /**
     * Display the specified coin.
     * Results are cached for 5 minutes to reduce database load.
     */
    public function show(Request $request, Coin $coin, GetCoinWithNavigation $getCoinWithNavigation): CoinResource
    {
        $request->validate([
            'search' => 'string|max:255',
            'length' => 'integer',
        ]);

        // Create a unique cache key based on coin ID and query parameters
        $cacheKey = 'coins:show:'.$coin->id.':'.md5(json_encode([
            'search' => $request->input('search', ''),
            'length' => $request->input('length', 10),
        ]));

        // Cache for 5 minutes (300 seconds)
        $coinData = Cache::remember($cacheKey, 300, function () use ($coin, $request, $getCoinWithNavigation): Coin {
            // Eager load metadata relationship
            $coin->load('metadata');

            // Add navigation (next/previous coin IDs) based on filters
            return $getCoinWithNavigation->execute(
                $coin,
                $request->input('search'),
                $request->integer('length', 10)
            );
        });

        return new CoinResource($coinData);
    }
}
