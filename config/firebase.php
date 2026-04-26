<?php

declare(strict_types=1);

return [
    /*
     * ------------------------------------------------------------------------
     * Default Firebase project
     * ------------------------------------------------------------------------
     */

    'default' => env('FIREBASE_PROJECT', 'app'),

    /*
     * ------------------------------------------------------------------------
     * Firebase project configurations
     * ------------------------------------------------------------------------
     */

    'projects' => [
        'app' => [

            /*
             * ------------------------------------------------------------------------
             * Credentials / Service Account
             * ------------------------------------------------------------------------
             *
             * In order to access a Firebase project, the SDK needs to authenticate.
             * The recommended way is to use a service account JSON file.
             * 
             * Save your Firebase Admin SDK JSON file to storage/app/firebase-credentials.json
             */

            'credentials' => storage_path('app/firebase-credentials.json'),

            /*
             * ------------------------------------------------------------------------
             * Firebase Realtime Database
             * ------------------------------------------------------------------------
             */

            'database' => [
                'url' => env('FIREBASE_DATABASE_URL'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firestore
             * ------------------------------------------------------------------------
             */

            'firestore' => [
                'database' => env('FIREBASE_FIRESTORE_DATABASE'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Dynamic Links
             * ------------------------------------------------------------------------
             */

            'dynamic_links' => [
                'default_domain' => env('FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Firebase Cloud Storage
             * ------------------------------------------------------------------------
             */

            'storage' => [
                'default_bucket' => env('FIREBASE_STORAGE_DEFAULT_BUCKET'),
            ],

            /*
             * ------------------------------------------------------------------------
             * Caching
             * ------------------------------------------------------------------------
             */

            'cache_store' => env('FIREBASE_CACHE_STORE', 'file'),

            /*
             * ------------------------------------------------------------------------
             * Logging
             * ------------------------------------------------------------------------
             */

            'logging' => [
                'http_log_channel' => env('FIREBASE_HTTP_LOG_CHANNEL'),
                'http_debug_log_channel' => env('FIREBASE_HTTP_DEBUG_LOG_CHANNEL'),
            ],

            /*
             * ------------------------------------------------------------------------
             * HTTP Client Options
             * ------------------------------------------------------------------------
             */

            'http_client_options' => [
                'proxy' => env('FIREBASE_HTTP_PROXY'),
                'timeout' => env('FIREBASE_HTTP_TIMEOUT'),
                'connect_timeout' => env('FIREBASE_HTTP_CONNECT_TIMEOUT'),
            ],

        ],
    ],
];
