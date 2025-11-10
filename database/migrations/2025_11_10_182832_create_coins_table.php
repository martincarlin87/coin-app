<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coins', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('symbol')->index();
            $table->string('name')->index();
            $table->string('image');
            $table->decimal('current_price', 20, 8)->nullable();
            $table->unsignedBigInteger('market_cap')->nullable();
            $table->unsignedInteger('market_cap_rank')->nullable();
            $table->unsignedBigInteger('fully_diluted_valuation')->nullable();
            $table->decimal('total_volume', 20, 8)->nullable();
            $table->decimal('high_24h', 20, 8)->nullable();
            $table->decimal('low_24h', 20, 8)->nullable();
            $table->decimal('price_change_24h', 20, 8)->nullable();
            $table->decimal('price_change_percentage_24h', 15, 5)->nullable();
            $table->bigInteger('market_cap_change_24h')->nullable();
            $table->decimal('market_cap_change_percentage_24h', 15, 5)->nullable();
            $table->decimal('circulating_supply', 30, 8)->nullable();
            $table->decimal('total_supply', 30, 8)->nullable();
            $table->decimal('max_supply', 30, 8)->nullable();
            $table->decimal('ath', 20, 8)->nullable();
            $table->decimal('ath_change_percentage', 15, 5)->nullable();
            $table->timestamp('ath_date')->nullable();
            $table->decimal('atl', 20, 8)->nullable();
            $table->decimal('atl_change_percentage', 15, 5)->nullable();
            $table->timestamp('atl_date')->nullable();
            $table->json('roi')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coins');
    }
};
