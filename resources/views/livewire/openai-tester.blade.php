<div class="p-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">OpenAI API Tester</h2>
        <p class="text-gray-600 dark:text-gray-400">Test and manage your OpenAI API integration with real-time streaming and advanced features.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Configuration Panel -->
        <div class="lg:col-span-1 space-y-4">
            <!-- API Configuration -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">API Configuration</h3>

                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Key</label>
                        <input type="password" wire:model="apiKey" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Model</label>
                        <select wire:model="model" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                            <option value="gpt-4o">GPT-4o</option>
                            <option value="gpt-4o-mini">GPT-4o Mini</option>
                            <option value="gpt-4-turbo">GPT-4 Turbo</option>
                            <option value="gpt-4">GPT-4</option>
                            <option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Temperature</label>
                            <input type="number" wire:model="temperature" step="0.1" min="0" max="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Max Tokens</label>
                            <input type="number" wire:model="maxTokens" min="1" max="32000" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Type Selection -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Test Type</h3>

                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="radio" wire:model="selectedTest" value="chat" class="mr-2">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Chat Completion</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" wire:model="selectedTest" value="function_call" class="mr-2">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Function Call</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" wire:model="selectedTest" value="streaming" class="mr-2">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Streaming</span>
                    </label>
                </div>
            </div>

            <!-- Quick Templates -->
            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Quick Templates</h3>

                <div class="space-y-2">
                    <button wire:click="loadTemplate('proposal')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded">
                        📄 Proposal Generation
                    </button>
                    <button wire:click="loadTemplate('creative')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded">
                        ✨ Creative Writing
                    </button>
                    <button wire:click="loadTemplate('code')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded">
                        💻 Code Generation
                    </button>
                    <button wire:click="loadTemplate('analysis')" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 rounded">
                        📊 Data Analysis
                    </button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-2">
                <button wire:click="testConnection" wire:loading.attr="disabled" class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium disabled:opacity-50">
                    <span wire:loading.remove wire:target="testConnection">🔗 Test Connection</span>
                    <span wire:loading wire:target="testConnection">Testing...</span>
                </button>

                <button wire:click="sendRequest" wire:loading.attr="disabled" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium disabled:opacity-50">
                    <span wire:loading.remove wire:target="sendRequest">🚀 Send Request</span>
                    <span wire:loading wire:target="sendRequest">Sending...</span>
                </button>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Prompts -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">Prompts</h3>

                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">System Prompt</label>
                        <textarea wire:model="systemPrompt" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white" placeholder="You are a helpful AI assistant..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">User Prompt</label>
                        <textarea wire:model="userPrompt" rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white" placeholder="Enter your test prompt here..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Response Area -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Response</h3>
                    @if($response)
                    <button onclick="navigator.clipboard.writeText('{{ addslashes($response) }}')" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                        📋 Copy
                    </button>
                    @endif
                </div>

                @if($error)
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3 mb-3">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-800 dark:text-red-200">{{ $error }}</p>
                        </div>
                    </div>
                </div>
                @endif

                @if($streaming)
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-3 mb-3">
                    <div class="flex items-center">
                        <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-2"></div>
                        <span class="text-sm text-blue-800 dark:text-blue-200">Streaming response...</span>
                    </div>
                </div>
                @endif

                @if($response)
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="prose dark:prose-invert max-w-none text-sm">
                        @if($selectedTest === 'function_call')
                            <pre class="whitespace-pre-wrap text-gray-900 dark:text-gray-100 bg-transparent border-0 p-0 m-0 font-mono">{{ $response }}</pre>
                        @else
                            {!! nl2br(e($response)) !!}
                        @endif
                    </div>
                </div>
                @elseif(!$error && !$isLoading)
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                    <p class="text-gray-500 dark:text-gray-400">No response yet. Send a request to see the result.</p>
                </div>
                @endif
            </div>

            <!-- Test History -->
            @if(count($testHistory) > 0)
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Test History</h3>
                    <button wire:click="clearHistory" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                        🗑️ Clear
                    </button>
                </div>

                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @foreach(array_slice($testHistory, -10) as $test)
                    <div class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ Str::limit($test['prompt'], 50) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $test['model'] }} • {{ $test['type'] }} • {{ \Carbon\Carbon::parse($test['timestamp'])->diffForHumans() }}</p>
                        </div>
                        <button wire:click="loadFromHistory('{{ $test['id'] }}')" class="ml-2 text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            Load
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Advanced Options Toggle -->
    <div class="mt-6">
        <button wire:click="$toggle('showAdvanced')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
            {{ $showAdvanced ? 'Hide' : 'Show' }} Advanced Options
        </button>

        @if($showAdvanced)
        <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <h4 class="text-md font-semibold text-gray-900 dark:text-white mb-3">Advanced Configuration</h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Function Schema (JSON)</label>
                    <textarea wire:model="functionSchema" rows="4" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white" placeholder='{"type": "object", "properties": {...}}'></textarea>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Top P</label>
                        <input type="number" step="0.1" min="0" max="1" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Frequency Penalty</label>
                        <input type="number" step="0.1" min="-2" max="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Presence Penalty</label>
                        <input type="number" step="0.1" min="-2" max="2" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Handle streaming updates
    document.addEventListener('livewire:init', () => {
        Livewire.on('stream-update', (event) => {
            // Handle real-time streaming updates
            console.log('Stream update:', event.content);
        });
    });
</script>
@endpush
