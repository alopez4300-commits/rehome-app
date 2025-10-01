<?php

namespace App\Services\Agent;

use App\Models\Project;
use App\Models\User;
use App\Services\MyHome\MyHomeService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AgentService
{
    private MyHomeService $myHomeService;
    private ContextBuilder $contextBuilder;

    public function __construct(MyHomeService $myHomeService, ContextBuilder $contextBuilder)
    {
        $this->myHomeService = $myHomeService;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * Process AI request with Claude and OpenAI backup
     */
    public function processRequest(Project $project, User $user, string $query): array
    {
        $startTime = microtime(true);
        
        try {
            // Check rate limits
            if (!$this->checkRateLimit($user)) {
                return $this->createErrorResponse('Rate limit exceeded. Please try again later.');
            }

            // Build context
            $context = $this->contextBuilder->buildAgentContext($project, $user, $query);
            
            // Log the prompt
            $this->myHomeService->createAIPrompt($project, $user, $query);

            // Try Claude first, then OpenAI as backup
            $response = $this->tryClaude($context);
            
            if (!$response['success']) {
                Log::warning('Claude request failed, trying OpenAI backup', [
                    'project_id' => $project->id,
                    'user_id' => $user->id,
                    'error' => $response['error']
                ]);
                
                $response = $this->tryOpenAI($context);
            }

            if (!$response['success']) {
                return $this->createErrorResponse('AI service temporarily unavailable. Please try again later.');
            }

            // Log the response
            $this->myHomeService->createAIResponse($project, $user, $response['content'], [
                'provider' => $response['provider'],
                'model' => $response['model'],
                'tokens_used' => $response['tokens_used'] ?? 0,
                'response_time' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            // Update rate limit
            $this->updateRateLimit($user);

            return [
                'success' => true,
                'content' => $response['content'],
                'provider' => $response['provider'],
                'model' => $response['model'],
                'tokens_used' => $response['tokens_used'] ?? 0,
                'response_time' => round((microtime(true) - $startTime) * 1000, 2),
            ];

        } catch (\Exception $e) {
            Log::error('AI request failed', [
                'project_id' => $project->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->createErrorResponse('An unexpected error occurred. Please try again.');
        }
    }

    /**
     * Try Claude API
     */
    private function tryClaude(array $context): array
    {
        $apiKey = config('ai.claude_api_key');
        
        if (!$apiKey) {
            return ['success' => false, 'error' => 'Claude API key not configured'];
        }

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'x-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => config('ai.claude_model', 'claude-3-sonnet-20240229'),
                    'max_tokens' => config('ai.max_tokens', 4000),
                    'temperature' => config('ai.temperature', 0.7),
                    'system' => $context['system_prompt'],
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $this->formatContextForClaude($context)
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'content' => $data['content'][0]['text'] ?? '',
                    'provider' => 'claude',
                    'model' => $data['model'] ?? 'claude-3-sonnet-20240229',
                    'tokens_used' => $data['usage']['total_tokens'] ?? 0,
                ];
            }

            return [
                'success' => false,
                'error' => 'Claude API error: ' . $response->status()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Claude request failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Try OpenAI API as backup
     */
    private function tryOpenAI(array $context): array
    {
        $apiKey = config('ai.openai_api_key');
        
        if (!$apiKey) {
            return ['success' => false, 'error' => 'OpenAI API key not configured'];
        }

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('ai.openai_model', 'gpt-4o-mini'),
                    'max_tokens' => config('ai.max_tokens', 4000),
                    'temperature' => config('ai.temperature', 0.7),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $context['system_prompt']
                        ],
                        [
                            'role' => 'user',
                            'content' => $this->formatContextForOpenAI($context)
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'content' => $data['choices'][0]['message']['content'] ?? '',
                    'provider' => 'openai',
                    'model' => $data['model'] ?? 'gpt-4o-mini',
                    'tokens_used' => $data['usage']['total_tokens'] ?? 0,
                ];
            }

            return [
                'success' => false,
                'error' => 'OpenAI API error: ' . $response->status()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'OpenAI request failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Format context for Claude
     */
    private function formatContextForClaude(array $context): string
    {
        $formatted = "Project Context:\n";
        $formatted .= "Project: {$context['context']['project']['name']}\n";
        $formatted .= "Description: {$context['context']['project']['description']}\n";
        $formatted .= "Status: {$context['context']['project']['status']}\n\n";

        $formatted .= "Recent Activity:\n";
        foreach ($context['context']['recent_activity'] as $entry) {
            $formatted .= "- [{$entry['ts']}] {$entry['kind']}: ";
            if (isset($entry['text'])) $formatted .= $entry['text'];
            if (isset($entry['title'])) $formatted .= $entry['title'];
            $formatted .= "\n";
        }

        $formatted .= "\nUser Query: {$context['query']}\n";
        
        return $formatted;
    }

    /**
     * Format context for OpenAI
     */
    private function formatContextForOpenAI(array $context): string
    {
        return $this->formatContextForClaude($context);
    }

    /**
     * Check rate limits
     */
    private function checkRateLimit(User $user): bool
    {
        $key = "ai_rate_limit:{$user->id}";
        $limits = Cache::get($key, ['minute' => 0, 'day' => 0, 'last_reset' => now()]);

        // Reset daily counter if needed
        if (now()->diffInDays($limits['last_reset']) >= 1) {
            $limits['day'] = 0;
            $limits['last_reset'] = now();
        }

        // Check limits
        $minuteLimit = config('ai.rate_limits.per_user_minute', 5);
        $dayLimit = config('ai.rate_limits.per_user_day', 50);

        if ($limits['minute'] >= $minuteLimit || $limits['day'] >= $dayLimit) {
            return false;
        }

        return true;
    }

    /**
     * Update rate limits
     */
    private function updateRateLimit(User $user): void
    {
        $key = "ai_rate_limit:{$user->id}";
        $limits = Cache::get($key, ['minute' => 0, 'day' => 0, 'last_reset' => now()]);

        $limits['minute']++;
        $limits['day']++;

        // Cache for 1 hour (minute limit) and 24 hours (day limit)
        Cache::put($key, $limits, 3600);
    }

    /**
     * Create error response
     */
    private function createErrorResponse(string $message): array
    {
        return [
            'success' => false,
            'error' => $message,
            'content' => null,
            'provider' => null,
            'model' => null,
            'tokens_used' => 0,
            'response_time' => 0,
        ];
    }

    /**
     * Get AI usage statistics for user
     */
    public function getUserStats(User $user): array
    {
        $key = "ai_rate_limit:{$user->id}";
        $limits = Cache::get($key, ['minute' => 0, 'day' => 0, 'last_reset' => now()]);

        return [
            'requests_today' => $limits['day'],
            'requests_this_minute' => $limits['minute'],
            'daily_limit' => config('ai.rate_limits.per_user_day', 50),
            'minute_limit' => config('ai.rate_limits.per_user_minute', 5),
            'last_reset' => $limits['last_reset'],
        ];
    }

    /**
     * Get AI usage statistics for project
     */
    public function getProjectStats(Project $project): array
    {
        $aiInteractions = $this->myHomeService->getAIInteractions($project);
        $prompts = $aiInteractions->where('kind', '/ai.prompt');
        $responses = $aiInteractions->where('kind', '/ai.response');

        return [
            'total_prompts' => $prompts->count(),
            'total_responses' => $responses->count(),
            'by_user' => $aiInteractions->groupBy('author_name')->map->count(),
            'recent_interactions' => $aiInteractions->take(10)->values(),
            'total_tokens' => $responses->sum('metadata.tokens_used'),
            'average_response_time' => $responses->avg('metadata.response_time'),
        ];
    }
}
