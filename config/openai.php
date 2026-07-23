<?php

return [
    'api_key' => env('OPENAI_API_KEY'),
    'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),

    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'chatbot_model' => env('OPENAI_CHATBOT_MODEL', 'gpt-4o-mini'),
    'article_model' => env('OPENAI_ARTICLE_MODEL', 'gpt-4o-mini'),

    'max_tokens' => [
        'suggestion' => (int) env('OPENAI_MAX_TOKENS_SUGGESTION', 120),
        'chatbot' => (int) env('OPENAI_MAX_TOKENS_CHATBOT', 400),
        'article' => (int) env('OPENAI_MAX_TOKENS_ARTICLE', 4000),
        'budget_advisor' => (int) env('OPENAI_MAX_TOKENS_BUDGET', 300),
    ],

    'timeout' => (int) env('OPENAI_TIMEOUT', 60),
    'tool_max_rounds' => (int) env('OPENAI_TOOL_MAX_ROUNDS', 3),
];
