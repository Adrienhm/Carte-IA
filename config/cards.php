<?php

return [

    'driver' => env('CARD_AI_DRIVER', 'fake'),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'text_model' => env('OPENAI_TEXT_MODEL', 'gpt-4o-mini'),
        'image_model' => env('OPENAI_IMAGE_MODEL', 'dall-e-3'),
        'image_size' => env('OPENAI_IMAGE_SIZE', '1024x1024'),
        'timeout' => (int) env('OPENAI_TIMEOUT', 60),
        'max_retries' => (int) env('OPENAI_MAX_RETRIES', 2),
        'base_uri' => env('OPENAI_BASE_URI', 'https://api.openai.com/v1'),
    ],

    'theme' => env('CARD_AI_THEME', 'medieval war game set in the NationsGlory universe of competing nations'),

    'image_style' => 'digital art style, detailed illustration, fantasy theme, centered composition',

    'image_disk' => 'public',
    'image_dir' => 'cards',
];
