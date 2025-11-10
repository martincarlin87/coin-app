<?php

declare(strict_types=1);

use App\Models\Coin;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

describe('Coin Web Controller', function () {
    it('renders the coins index page', function () {
        $response = get('/coins');

        $response->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Coins/Index')
            );
    });

    it('renders the coin show page with coin id', function () {
        $coin = Coin::factory()->create();

        $response = get("/coins/{$coin->id}");

        $response->assertSuccessful()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Coins/Show')
                ->has('coinId')
                ->where('coinId', $coin->id)
            );
    });

    it('returns 404 for non-existent coin', function () {
        $response = get('/coins/99999');

        $response->assertNotFound();
    });
});
