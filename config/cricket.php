<?php

return [
    'api_key' => env('CRICKETDATA_API_KEY', 'c7d0228c-6e2b-49f4-a27d-7fe329dc9d39'),
    'base_url' => env('CRICKETDATA_BASE_URL', 'https://api.cricapi.com/v1/'),
    'cache_ttl' => (int) env('CRICKETDATA_CACHE_TTL', 900), // 15 minutes cache
];
