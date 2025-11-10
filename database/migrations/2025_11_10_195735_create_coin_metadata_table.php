<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coin_metadata', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coin_id')->constrained()->onDelete('cascade');
            $table->string('web_slug')->nullable();
            $table->string('asset_platform_id')->nullable();
            $table->integer('block_time_in_minutes')->nullable();
            $table->string('hashing_algorithm')->nullable();
            $table->json('categories')->nullable();
            $table->boolean('preview_listing')->default(false);
            $table->text('public_notice')->nullable();
            $table->json('additional_notices')->nullable();
            $table->date('genesis_date')->nullable();
            $table->decimal('sentiment_votes_up_percentage', 5, 2)->nullable();
            $table->decimal('sentiment_votes_down_percentage', 5, 2)->nullable();
            $table->bigInteger('watchlist_portfolio_users')->nullable();
            $table->json('platforms')->nullable();
            $table->json('detail_platforms')->nullable();
            $table->json('localization')->nullable();
            $table->json('description')->nullable();
            $table->json('links')->nullable();
            $table->json('community_data')->nullable();
            $table->json('developer_data')->nullable();
            $table->json('status_updates')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coin_metadata');
    }
};
