<?php

namespace Sajjadhossainshohagbd\Extractor;

use Illuminate\Support\ServiceProvider;

class ExtractorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Merge extractor config to laravel config
        $this->mergeConfigFrom(__DIR__ . '/config/extractor.php', 'extractor');

        // Publish configuration files
        $this->publishes([
            __DIR__ . '/config/extractor.php' => base_path('config/extractor.php'),
        ], 'laravel-request-extractor-config');
    }
}
