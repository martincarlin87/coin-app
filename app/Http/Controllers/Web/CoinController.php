<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Coin;
use Inertia\Inertia;
use Inertia\Response;

final class CoinController extends Controller
{
    /**
     * Display the coins index page.
     * Data will be fetched client-side from the API.
     */
    public function index(): Response
    {
        return Inertia::render('Coins/Index', []);
    }

    /**
     * Display the coin detail page.
     * Data will be fetched client-side from the API.
     */
    public function show(Coin $coin): Response
    {
        return Inertia::render('Coins/Show', [
            'coinSlug' => $coin->slug,
        ]);
    }
}
