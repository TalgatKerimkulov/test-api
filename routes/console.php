<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('victoria:test {--query}', function () {
    $testId = (string) str()->uuid();
    $message = "Victoria Logs test message id={$testId}";

    Log::info($message);
    Log::warning("Victoria warning id={$testId}");
    Log::error("Victoria error id={$testId}");

    $this->info("Sent test logs with id: {$testId}");

    if (! $this->option('query')) {
        $this->line('Run with --query to check records in Victoria Logs.');
        return;
    }

    sleep(2);

    $baseUrl = rtrim((string) config('services.victoria.base_uri', 'http://localhost:9428'), '/');
    $response = Http::timeout(10)->get("{$baseUrl}/select/logsql/query", [
        'query' => $testId,
        'limit' => 20,
    ]);

    if (! $response->successful()) {
        $this->error('Victoria query failed: HTTP '.$response->status());
        return;
    }

    $lines = array_values(array_filter(explode("\n", trim($response->body()))));
    $this->info('Found '.count($lines).' records in Victoria Logs');
    foreach (array_slice($lines, 0, 5) as $line) {
        $this->line($line);
    }
})->purpose('Send and optionally verify test logs in Victoria Logs');
