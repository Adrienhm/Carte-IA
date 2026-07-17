<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Generation IA des cartes (CDC section 9)
    |--------------------------------------------------------------------------
    |
    | Le driver determine quel service produit le nom, la description, les
    | statistiques et l'image d'une carte :
    |
    |   "fake"   -> generation locale deterministe, sans appel reseau ni cle.
    |               Permet de developper et de faire la demo hors-ligne.
    |   "openai" -> GPT pour le texte, DALL-E pour l'image.
    |
    | Les cles d'API ne vivent que dans le .env, jamais dans le code (CDC 9.3).
    */

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

    /*
    | Univers injecte dans les prompts pour rester coherent avec NationsGlory
    | (serveur Minecraft competitif de nations et de conquete territoriale).
    */
    'theme' => env('CARD_AI_THEME', 'medieval war game set in the NationsGlory universe of competing nations'),

    'image_style' => 'digital art style, detailed illustration, fantasy theme, centered composition',

    /*
    | Ou sont rangees les illustrations generees (disque "public").
    */
    'image_disk' => 'public',
    'image_dir' => 'cards',
];
