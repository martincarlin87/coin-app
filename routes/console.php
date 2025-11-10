<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:import-coin-data')
    ->everyFiveMinutes()
    ->withoutOverlapping()  // Prevent concurrent runs
    ->onOneServer()         // Only run on one server if using multiple
    ->runInBackground();    // Run in background
