<x-filament-panels::page>
    <div class="space-y-6">
        <!-- API Status and Usage Stats with Quick Templates - Horizontal Layout -->
        <div class="flex flex-wrap gap-4">
            <!-- Stats Section -->
            <div class="flex flex-wrap gap-4 flex-1">
                <x-filament::card class="flex-1 min-w-[200px]">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-signal class="h-6 w-6 text-green-500" />
                        </div>
                        <div class="ml-3">
                            <p class="text-xs font-medium text-gray-500">API Status</p>
                            <p class="text-sm font-semibold text-gray-900">Connected</p>
                        </div>
                    </div>
                </x-filament::card>

                <x-filament::card class="flex-1 min-w-[200px]">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-chart-bar class="h-6 w-6 text-blue-500" />
                        </div>
                        <div class="ml-3">
                            <p class="text-xs font-medium text-gray-500">Total Requests</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $this->getApiUsageStats()['total_requests'] }}</p>
                        </div>
                    </div>
                </x-filament::card>

                <x-filament::card class="flex-1 min-w-[200px]">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-currency-dollar class="h-6 w-6 text-yellow-500" />
                        </div>
                        <div class="ml-3">
                            <p class="text-xs font-medium text-gray-500">Est. Cost</p>
                            <p class="text-sm font-semibold text-gray-900">${{ number_format($this->getApiUsageStats()['cost_estimate'], 2) }}</p>
                        </div>
                    </div>
                </x-filament::card>

                <x-filament::card class="flex-1 min-w-[200px]">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-o-calendar class="h-6 w-6 text-purple-500" />
                        </div>
                        <div class="ml-3">
                            <p class="text-xs font-medium text-gray-500">Today's Requests</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $this->getApiUsageStats()['requests_today'] }}</p>
                        </div>
                    </div>
                </x-filament::card>
            </div>

                                    <!-- Quick Test Templates Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400 transition-colors duration-150">
                    <div class="flex items-center">
                        <x-heroicon-o-sparkles class="w-4 h-4 mr-2 text-blue-500 dark:text-blue-400" />
                        <span>Quick Test Templates</span>
                    </div>
                    <x-heroicon-o-chevron-down class="w-4 h-4 text-gray-400 dark:text-gray-500" x-bind:class="{'rotate-180': open}" />
                </button>

                <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 z-10 w-56 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg dark:shadow-gray-900/50">
                    <div class="py-1">
                        <button wire:click="loadProposalTemplate" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-700 dark:hover:text-blue-300 transition-colors duration-150">
                            <x-heroicon-o-document-text class="w-4 h-4 mr-3 text-blue-500 dark:text-blue-400" />
                            <span>Proposal Generation</span>
                        </button>

                        <button wire:click="loadTaskTemplate" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-700 dark:hover:text-green-300 transition-colors duration-150">
                            <x-heroicon-o-check-circle class="w-4 h-4 mr-3 text-green-500 dark:text-green-400" />
                            <span>Task Generation</span>
                        </button>

                        <button wire:click="loadCreativeTemplate" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:text-purple-700 dark:hover:text-purple-300 transition-colors duration-150">
                            <x-heroicon-o-light-bulb class="w-4 h-4 mr-3 text-purple-500 dark:text-purple-400" />
                            <span>Creative Writing</span>
                        </button>

                        <button wire:click="loadCodeTemplate" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:text-orange-700 dark:hover:text-orange-300 transition-colors duration-150">
                            <x-heroicon-o-code-bracket class="w-4 h-4 mr-3 text-orange-500 dark:text-orange-400" />
                            <span>Code Generation</span>
                        </button>

                        <button wire:click="loadAnalysisTemplate" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors duration-150">
                            <x-heroicon-o-chart-pie class="w-4 h-4 mr-3 text-indigo-500 dark:text-indigo-400" />
                            <span>Data Analysis</span>
                        </button>

                        <button wire:click="loadTranslationTemplate" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-teal-50 dark:hover:bg-teal-900/20 hover:text-teal-700 dark:hover:text-teal-300 transition-colors duration-150">
                            <x-heroicon-o-language class="w-4 h-4 mr-3 text-teal-500 dark:text-teal-400" />
                            <span>Translation</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Form with Tabs -->
        {{ $this->form }}

        <!-- Saved Settings Table -->
        <x-filament::card>
            <h3 class="text-lg font-semibold mb-4">Saved Settings</h3>
            {{ $this->table }}
        </x-filament::card>



        <!-- Response Section -->
        @if($this->apiResponse || $this->errorMessage)
        <x-filament::card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold">
                    @if($this->apiResponse)
                        API Response
                    @else
                        Error
                    @endif
                </h2>
                @if($this->responseMetadata)
                <div class="flex space-x-4 text-sm text-gray-600">
                    <span>Status: {{ $this->responseMetadata['status'] ?? 'N/A' }}</span>
                    <span>Time: {{ $this->responseMetadata['execution_time'] ?? 'N/A' }}ms</span>
                    <span>Model: {{ $this->responseMetadata['model'] ?? 'N/A' }}</span>
                    <span>Length: {{ $this->responseMetadata['response_length'] ?? 'N/A' }} chars</span>
                </div>
                @endif
            </div>

            @if($this->apiResponse)
            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border dark:border-gray-700">
                <div class="prose dark:prose-invert max-w-none">
                    @if($this->testType === 'function_call' || $this->testType === 'moderation')
                        <pre class="whitespace-pre-wrap text-sm text-gray-900 dark:text-gray-100 bg-transparent border-0 p-0 m-0 font-mono">{{ $this->apiResponse }}</pre>
                    @else
                        {!! nl2br(e($this->apiResponse)) !!}
                    @endif
                </div>
            </div>

            <div class="mt-4 flex space-x-2">
                <x-filament::button
                    onclick="navigator.clipboard.writeText('{{ addslashes($this->apiResponse) }}')"
                    color="secondary"
                    size="sm">
                    <x-heroicon-o-clipboard class="w-4 h-4 mr-2" />
                    Copy Response
                </x-filament::button>

                <x-filament::button
                    wire:click="$set('apiResponse', null)"
                    color="danger"
                    size="sm">
                    <x-heroicon-o-x-mark class="w-4 h-4 mr-2" />
                    Clear
                </x-filament::button>
            </div>
            @endif

            @if($this->errorMessage)
            <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-200 dark:border-red-800">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-red-400" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-800 dark:text-red-200">
                            {{ $this->errorMessage }}
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </x-filament::card>
        @endif


    </div>

    <!-- Loading Overlay -->
    @if($this->isLoading)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div>
            <span class="text-gray-700 dark:text-gray-300">Processing API request...</span>
        </div>
    </div>
    @endif
</x-filament-panels::page>
