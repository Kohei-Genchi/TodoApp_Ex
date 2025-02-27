<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

//https://readouble.com/laravel/11.x/ja/scheduling.html
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('todos:cleanup', function () {
    $this->call(TodoCleanup::class);
})->purpose('Clean up completed and pending todos');

// タスク整理のスケジュール設定
Schedule::command('todos:cleanup')->dailyAt('00:00');
