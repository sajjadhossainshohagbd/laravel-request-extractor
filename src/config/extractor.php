<?php

namespace Sajjadhossainshohagbd\Extractor;

return [

    /*
        Path to scan for controllers. Only files in this directory will be scanned.
        If you want to scan all controllers, you can set this to 'app/Http/Controllers'.
    */
    'scan_path' => base_path('app/Http/Controllers/Test'),

    /*
        Exclude certain controllers from being scanned. This is useful if you want to exclude certain controllers from being scanned.
        If you want to exclude all controllers, you can set this to [].
    */
    'scan_exclude' => [
        // For example, if you want to exclude the AgentController, you can add it to the array.
        // 'App\Http\Controllers\AgentController',
    ],

];
