<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Manual Generation Form -->
        <x-filament-forms::form wire:submit="generate">
            {{ $this->form }}

            <div class="mt-4">
                <x-filament::button type="submit" color="primary" size="lg">
                    <x-heroicon-o-sparkles class="w-5 h-5 mr-2" />
                    Generate Content
                </x-filament::button>
            </div>
        </x-filament-forms::form>

        <!-- Generated Content Section -->
        @if($this->generatedContent)
        <x-filament::card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold">Generated Content</h2>
                @if($this->generationStats)
                <div class="flex space-x-4 text-sm text-gray-600">
                    <span>Model: {{ $this->generationStats['model'] }}</span>
                    <span>Temperature: {{ $this->generationStats['temperature'] }}</span>
                    <span>Time: {{ $this->generationStats['execution_time'] }}ms</span>
                    <span>Length: {{ $this->generationStats['response_length'] }} chars</span>
                </div>
                @endif
            </div>

            <div class="bg-gray-50 p-4 rounded-lg border">
                <div class="prose max-w-none">
                    {!! nl2br(e($this->generatedContent)) !!}
                </div>
            </div>

            <div class="mt-4 flex space-x-2">
                <x-filament::button
                    onclick="navigator.clipboard.writeText('{{ addslashes($this->generatedContent) }}')"
                    color="secondary"
                    size="sm">
                    <x-heroicon-o-clipboard class="w-4 h-4 mr-2" />
                    Copy to Clipboard
                </x-filament::button>

                <x-filament::button
                    wire:click="$set('generatedContent', null)"
                    color="danger"
                    size="sm">
                    <x-heroicon-o-x-mark class="w-4 h-4 mr-2" />
                    Clear
                </x-filament::button>
            </div>
        </x-filament::card>
        @endif

        <!-- Statistics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-filament::card>
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-chart-bar class="w-8 h-8 text-blue-500" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Generations</p>
                        <p class="text-2xl font-semibold text-gray-900">0</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-clock class="w-8 h-8 text-green-500" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Avg Response Time</p>
                        <p class="text-2xl font-semibold text-gray-900">0ms</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-currency-dollar class="w-8 h-8 text-yellow-500" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Tokens</p>
                        <p class="text-2xl font-semibold text-gray-900">0</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-exclamation-triangle class="w-8 h-8 text-red-500" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Error Rate</p>
                        <p class="text-2xl font-semibold text-gray-900">0%</p>
                    </div>
                </div>
            </x-filament::card>
        </div>

        <!-- Documentation Section -->
        <x-filament::card>
            <div class="prose max-w-none">
                <h2 class="text-xl font-semibold mb-4">AI Generation Documentation</h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium mb-2">How It Works</h3>
                        <p class="mb-4">
                            The AI generation system uses OpenAI's GPT models to automatically create content for proposals,
                            projects, tasks, and other business documents. The system processes your prompts and generates
                            contextually relevant content based on the selected parameters.
                        </p>

                        <h3 class="text-lg font-medium mb-2">Generation Types</h3>
                        <ul class="list-disc pl-6 mb-4">
                            <li><strong>Proposal:</strong> Creates detailed project proposals with scope, timeline, and pricing</li>
                            <li><strong>Project:</strong> Generates project plans with tasks, milestones, and deliverables</li>
                            <li><strong>Task:</strong> Creates individual task descriptions with requirements and acceptance criteria</li>
                            <li><strong>Subtask:</strong> Breaks down complex tasks into smaller, manageable components</li>
                            <li><strong>Service:</strong> Generates service descriptions and feature lists</li>
                            <li><strong>Package:</strong> Creates package offerings with pricing tiers and features</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-lg font-medium mb-2">Parameters Explained</h3>
                        <ul class="list-disc pl-6 mb-4">
                            <li><strong>Model:</strong> Choose between GPT-4 (most capable), GPT-4 Turbo (faster), or GPT-3.5 Turbo (most economical)</li>
                            <li><strong>Temperature:</strong> Controls randomness (0 = deterministic, 2 = very creative)</li>
                            <li><strong>Max Tokens:</strong> Maximum length of the generated response</li>
                        </ul>

                        <h3 class="text-lg font-medium mb-2">Best Practices</h3>
                        <ul class="list-disc pl-6 mb-4">
                            <li>Be specific in your prompts for better results</li>
                            <li>Use lower temperature (0.3-0.7) for factual content</li>
                            <li>Use higher temperature (0.8-1.2) for creative content</li>
                            <li>Monitor token usage to control costs</li>
                            <li>Review and refine generated content before using</li>
                        </ul>

                        <h3 class="text-lg font-medium mb-2">Current Use Cases</h3>
                        <p class="mb-2">
                            The AI generation system is currently integrated across the following areas:
                        </p>
                        <ul class="list-disc pl-6">
                            <li>Automatic proposal generation from client intake forms</li>
                            <li>Project planning and task breakdown</li>
                            <li>Service and package description creation</li>
                            <li>Content optimization and enhancement</li>
                        </ul>
                    </div>
                </div>
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>
