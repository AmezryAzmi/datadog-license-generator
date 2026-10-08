<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('app:about-generator', function () {
    $this->info('Datadog License Monitoring Generator');
})->purpose('Display application information');
