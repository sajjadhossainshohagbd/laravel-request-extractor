<?php

namespace Sajjadhossainshohagbd\Extractor;

use Illuminate\Support\ServiceProvider;
use Sajjadhossainshohagbd\Extractor\Console\ExtractCommand;

class ExtractorServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register extractor command
        $this->commands([
            ExtractCommand::class,
        ]);
    }

    public function boot()
    {
        // Merge extractor config to laravel config
        $this->mergeConfigFrom(__DIR__.'/config/extractor.php', 'extractor');

        // Publish configuration files
        $this->publishes([
            __DIR__.'/config/extractor.php' => base_path('config/extractor.php'),
        ], 'laravel-request-extractor-config');
    }
}
