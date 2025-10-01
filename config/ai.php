<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the primary AI provider and backup options
    |
    */

    'provider' => env('AI_PROVIDER', 'claude'),
    'backup_provider' => env('AI_BACKUP_PROVIDER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    |
    | Configure models for each provider
    |
    */

    'claude_model' => env('AI_CLAUDE_MODEL', 'claude-3-sonnet-20240229'),
    'openai_model' => env('AI_OPENAI_MODEL', 'gpt-4o-mini'),

    /*
    |--------------------------------------------------------------------------
    | API Keys
    |--------------------------------------------------------------------------
    |
    | API keys for AI providers
    |
    */

    'claude_api_key' => env('CLAUDE_API_KEY'),
    'openai_api_key' => env('OPENAI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | AI Parameters
    |--------------------------------------------------------------------------
    |
    | Default parameters for AI requests
    |
    */

    'max_tokens' => env('AI_MAX_TOKENS', 4000),
    'temperature' => env('AI_TEMPERATURE', 0.7),
    'timeout_seconds' => env('AI_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Context Policy
    |--------------------------------------------------------------------------
    |
    | How to allocate token budget across different context types
    |
    */

    'context_budget' => [
        'myhome_entries' => 0.60,     // ~200 entries at ~20 tokens each
        'project_metadata' => 0.25,   // team, tasks, dates
        'file_excerpts' => 0.15,      // file content (when implemented)
    ],

    'max_entry_tokens' => 500,        // Drop entries larger than this
    'truncate_strategy' => 'drop_whole',

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limits for AI requests per user
    |
    */

    'rate_limits' => [
        'per_user_minute' => env('AI_RATE_LIMIT_MINUTE', 5),
        'per_user_day' => env('AI_RATE_LIMIT_DAY', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | PII Redaction
    |--------------------------------------------------------------------------
    |
    | Patterns and rules for PII redaction based on user roles
    |
    */

    'pii_patterns' => [
        'email' => '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/',
        'phone' => '/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/',
        'ssn' => '/\b\d{3}-\d{2}-\d{4}\b/',
        'credit_card' => '/\b\d{4}[- ]?\d{4}[- ]?\d{4}[- ]?\d{4}\b/',
    ],

    'redaction_by_role' => [
        'admin' => [],                    // No redaction
        'owner' => [],                    // No redaction
        'member' => [],                   // No redaction
        'consultant' => ['email', 'phone'], // Limited redaction
        'client' => ['email', 'phone', 'ssn', 'credit_card'], // Full redaction
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Tracking
    |--------------------------------------------------------------------------
    |
    | Cost per 1M tokens for each model (USD)
    |
    */

    'costs' => [
        'claude-3-sonnet-20240229' => [
            'input' => 3.00,
            'output' => 15.00,
        ],
        'gpt-4o-mini' => [
            'input' => 0.15,
            'output' => 0.60,
        ],
        'gpt-4o' => [
            'input' => 5.00,
            'output' => 15.00,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | System Prompts
    |--------------------------------------------------------------------------
    |
    | Default system prompts for different contexts
    |
    */

    'system_prompts' => [
        'default' => 'You are an AI assistant for project management. Provide helpful, accurate responses based on the project context. Be concise but thorough. If you don\'t have enough information, ask clarifying questions. Always maintain a professional and helpful tone.',
        
        'task_management' => 'You are an AI assistant specialized in task management and project coordination. Help users organize tasks, track progress, and identify blockers. Provide actionable insights and suggestions.',
        
        'time_tracking' => 'You are an AI assistant for time tracking and productivity analysis. Help users understand their time usage patterns and suggest improvements for better project management.',
        
        'file_management' => 'You are an AI assistant for file and document management. Help users organize, find, and understand their project files and documents.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable/disable specific AI features
    |
    */

    'features' => [
        'context_building' => env('AI_FEATURE_CONTEXT', true),
        'pii_redaction' => env('AI_FEATURE_PII_REDACTION', true),
        'cost_tracking' => env('AI_FEATURE_COST_TRACKING', true),
        'rate_limiting' => env('AI_FEATURE_RATE_LIMITING', true),
        'backup_provider' => env('AI_FEATURE_BACKUP', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Cache settings for AI responses and rate limits
    |
    */

    'cache' => [
        'rate_limit_ttl' => 3600,        // 1 hour
        'response_ttl' => 1800,          // 30 minutes
        'context_ttl' => 900,            // 15 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Logging configuration for AI requests
    |
    */

    'logging' => [
        'enabled' => env('AI_LOGGING_ENABLED', true),
        'level' => env('AI_LOGGING_LEVEL', 'info'),
        'log_prompts' => env('AI_LOG_PROMPTS', false),
        'log_responses' => env('AI_LOG_RESPONSES', false),
    ],
];
