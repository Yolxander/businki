<?php

namespace App\Livewire;

use App\Services\OpenAIService;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OpenAITester extends Component
{
    public $apiKey;
    public $model = 'gpt-4o-mini';
    public $temperature = 0.7;
    public $maxTokens = 4000;
    public $systemPrompt = 'You are a helpful AI assistant.';
    public $userPrompt = '';
    public $response = '';
    public $isLoading = false;
    public $error = '';
    public $streaming = false;
    public $streamedResponse = '';
    public $testHistory = [];
    public $selectedTest = 'chat';
    public $functionSchema = '';
    public $showAdvanced = false;

    protected $listeners = ['echo:openai,test-completed' => 'handleTestCompleted'];

    public function mount()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->loadTestHistory();
    }

    public function render()
    {
        return view('livewire.openai-tester');
    }

    public function testConnection()
    {
        $this->isLoading = true;
        $this->error = '';
        $this->response = '';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(10)->get('https://api.openai.com/v1/models');

            if ($response->successful()) {
                $models = $response->json('data', []);
                $this->response = "✅ Connection successful!\n\nAvailable models:\n";
                foreach (array_slice($models, 0, 10) as $model) {
                    $this->response .= "- {$model['id']}\n";
                }
                if (count($models) > 10) {
                    $this->response .= "... and " . (count($models) - 10) . " more models\n";
                }
            } else {
                $this->error = "❌ Connection failed: " . $response->status() . " - " . $response->body();
            }
        } catch (\Exception $e) {
            $this->error = "❌ Connection error: " . $e->getMessage();
        }

        $this->isLoading = false;
        $this->saveToHistory('connection_test', $this->response ?: $this->error);
    }

    public function sendRequest()
    {
        if (empty($this->userPrompt)) {
            $this->error = 'Please enter a prompt to test.';
            return;
        }

        $this->isLoading = true;
        $this->error = '';
        $this->response = '';
        $this->streamedResponse = '';

        try {
            $startTime = microtime(true);

            switch ($this->selectedTest) {
                case 'chat':
                    $this->sendChatRequest();
                    break;
                case 'function_call':
                    $this->sendFunctionCallRequest();
                    break;
                case 'streaming':
                    $this->sendStreamingRequest();
                    break;
                default:
                    $this->sendChatRequest();
            }

            $executionTime = (microtime(true) - $startTime) * 1000;
            $this->updateUsageStats($executionTime);

        } catch (\Exception $e) {
            $this->error = "❌ Request failed: " . $e->getMessage();
            Log::error('OpenAI API test failed', [
                'error' => $e->getMessage(),
                'prompt' => $this->userPrompt,
                'model' => $this->model,
            ]);
        }

        $this->isLoading = false;
        $this->saveToHistory($this->selectedTest, $this->response ?: $this->error);
    }

    private function sendChatRequest()
    {
        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->systemPrompt
                ],
                [
                    'role' => 'user',
                    'content' => $this->userPrompt
                ]
            ],
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', $payload);

        if ($response->successful()) {
            $data = $response->json();
            $this->response = $data['choices'][0]['message']['content'] ?? 'No response received';
        } else {
            throw new \Exception($response->status() . ' - ' . $response->body());
        }
    }

    private function sendFunctionCallRequest()
    {
        $functions = [
            [
                'name' => 'test_function',
                'description' => 'A test function for API testing',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'result' => [
                            'type' => 'string',
                            'description' => 'The result of the function call'
                        ],
                        'status' => [
                            'type' => 'string',
                            'enum' => ['success', 'error'],
                            'description' => 'The status of the operation'
                        ]
                    ],
                    'required' => ['result', 'status']
                ]
            ]
        ];

        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->systemPrompt
                ],
                [
                    'role' => 'user',
                    'content' => $this->userPrompt
                ]
            ],
            'functions' => $functions,
            'function_call' => ['name' => 'test_function'],
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', $payload);

        if ($response->successful()) {
            $data = $response->json();
            $functionCall = $data['choices'][0]['message']['function_call'] ?? null;
            if ($functionCall) {
                $this->response = json_encode($functionCall, JSON_PRETTY_PRINT);
            } else {
                $this->response = 'No function call received';
            }
        } else {
            throw new \Exception($response->status() . ' - ' . $response->body());
        }
    }

    private function sendStreamingRequest()
    {
        $this->streaming = true;
        $this->streamedResponse = '';

        $payload = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $this->systemPrompt
                ],
                [
                    'role' => 'user',
                    'content' => $this->userPrompt
                ]
            ],
            'max_tokens' => $this->maxTokens,
            'temperature' => $this->temperature,
            'stream' => true,
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(120)->post('https://api.openai.com/v1/chat/completions', $payload);

        if ($response->successful()) {
            $lines = explode("\n", $response->body());
            foreach ($lines as $line) {
                if (str_starts_with($line, 'data: ')) {
                    $data = substr($line, 6);
                    if ($data === '[DONE]') {
                        break;
                    }

                    $jsonData = json_decode($data, true);
                    if ($jsonData && isset($jsonData['choices'][0]['delta']['content'])) {
                        $content = $jsonData['choices'][0]['delta']['content'];
                        $this->streamedResponse .= $content;
                        $this->dispatch('stream-update', content: $content);
                    }
                }
            }
            $this->response = $this->streamedResponse;
        } else {
            throw new \Exception($response->status() . ' - ' . $response->body());
        }

        $this->streaming = false;
    }

    public function loadTemplate($type)
    {
        $templates = [
            'proposal' => [
                'system' => 'You are a professional business consultant specializing in creating compelling proposals.',
                'user' => 'Create a professional proposal for a web development project. The client needs a modern e-commerce website with payment processing, inventory management, and customer analytics. Budget is $15,000-$25,000. Timeline is 8-12 weeks.',
                'model' => 'gpt-4o-mini',
                'temperature' => 0.7,
            ],
            'creative' => [
                'system' => 'You are a creative writer with a vivid imagination and engaging storytelling abilities.',
                'user' => 'Write a short story about a time traveler who discovers they can only travel to moments when they were happy.',
                'model' => 'gpt-4o',
                'temperature' => 0.9,
            ],
            'code' => [
                'system' => 'You are an expert software developer. Write clean, well-documented code with best practices.',
                'user' => 'Write a Python function that implements a binary search algorithm. Include proper error handling and documentation.',
                'model' => 'gpt-4o',
                'temperature' => 0.3,
            ],
            'analysis' => [
                'system' => 'You are a data analyst. Provide clear, actionable insights from data.',
                'user' => 'Analyze this sales data and provide insights: Q1: $50K, Q2: $65K, Q3: $45K, Q4: $80K. What trends do you see and what recommendations would you make?',
                'model' => 'gpt-4o',
                'temperature' => 0.2,
            ],
        ];

        if (isset($templates[$type])) {
            $template = $templates[$type];
            $this->systemPrompt = $template['system'];
            $this->userPrompt = $template['user'];
            $this->model = $template['model'];
            $this->temperature = $template['temperature'];
            $this->selectedTest = 'chat';
        }
    }

    private function updateUsageStats($executionTime)
    {
        $totalRequests = Cache::get('openai_total_requests', 0) + 1;
        $requestsToday = Cache::get('openai_requests_today', 0) + 1;

        Cache::put('openai_total_requests', $totalRequests, now()->addYear());
        Cache::put('openai_requests_today', $requestsToday, now()->addDay());

        // Simple cost estimation (this would be more sophisticated in production)
        $estimatedCost = $totalRequests * 0.01; // Rough estimate
        Cache::put('openai_cost_estimate', $estimatedCost, now()->addYear());
    }

    private function saveToHistory($type, $result)
    {
        $history = Cache::get('openai_test_history', []);
        $history[] = [
            'id' => uniqid(),
            'type' => $type,
            'prompt' => $this->userPrompt,
            'response' => $result,
            'model' => $this->model,
            'temperature' => $this->temperature,
            'timestamp' => now()->toISOString(),
        ];

        // Keep only last 50 tests
        if (count($history) > 50) {
            $history = array_slice($history, -50);
        }

        Cache::put('openai_test_history', $history, now()->addMonth());
        $this->testHistory = $history;
    }

    private function loadTestHistory()
    {
        $this->testHistory = Cache::get('openai_test_history', []);
    }

    public function clearHistory()
    {
        Cache::forget('openai_test_history');
        $this->testHistory = [];
    }

    public function loadFromHistory($id)
    {
        $test = collect($this->testHistory)->firstWhere('id', $id);
        if ($test) {
            $this->userPrompt = $test['prompt'];
            $this->model = $test['model'];
            $this->temperature = $test['temperature'];
            $this->response = $test['response'];
        }
    }

    #[On('stream-update')]
    public function handleStreamUpdate($content)
    {
        $this->streamedResponse .= $content;
    }
}
