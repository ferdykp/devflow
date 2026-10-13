<?php

return [
    // AI is off by default; developers may enable it with their own provider.
    'enabled' => env('DEVFLOW_AI_ENABLED', false),
    'endpoint' => env('DEVFLOW_AI_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
    'api_key' => env('DEVFLOW_AI_API_KEY'),
    'model' => env('DEVFLOW_AI_MODEL', 'gpt-4o-mini'),
    'timeout' => (int) env('DEVFLOW_AI_TIMEOUT', 15),
];
