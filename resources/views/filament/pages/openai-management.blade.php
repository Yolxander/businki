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
                        <button wire:click="confirmTemplate('proposal')" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 hover:text-blue-700 dark:hover:text-blue-300 transition-colors duration-150">
                            <x-heroicon-o-document-text class="w-4 h-4 mr-3 text-blue-500 dark:text-blue-400" />
                            <span>Proposal Generation</span>
                        </button>

                        <button wire:click="confirmTemplate('tasks')" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-green-50 dark:hover:bg-green-900/20 hover:text-green-700 dark:hover:text-green-300 transition-colors duration-150">
                            <x-heroicon-o-check-circle class="w-4 h-4 mr-3 text-green-500 dark:text-green-400" />
                            <span>Task Generation</span>
                        </button>

                        <button wire:click="confirmTemplate('creative')" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-purple-50 dark:hover:bg-purple-900/20 hover:text-purple-700 dark:hover:text-purple-300 transition-colors duration-150">
                            <x-heroicon-o-light-bulb class="w-4 h-4 mr-3 text-purple-500 dark:text-purple-400" />
                            <span>Creative Writing</span>
                        </button>

                        <button wire:click="confirmTemplate('code')" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-orange-50 dark:hover:bg-orange-900/20 hover:text-orange-700 dark:hover:text-orange-300 transition-colors duration-150">
                            <x-heroicon-o-code-bracket class="w-4 h-4 mr-3 text-orange-500 dark:text-orange-400" />
                            <span>Code Generation</span>
                        </button>

                        <button wire:click="confirmTemplate('analysis')" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors duration-150">
                            <x-heroicon-o-chart-pie class="w-4 h-4 mr-3 text-indigo-500 dark:text-indigo-400" />
                            <span>Data Analysis</span>
                        </button>

                        <button wire:click="confirmTemplate('translation')" @click="open = false" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-teal-50 dark:hover:bg-teal-900/20 hover:text-teal-700 dark:hover:text-teal-300 transition-colors duration-150">
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

    <!-- Template Confirmation Modal -->
    @if($this->showTemplateModal)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50" x-data>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <x-heroicon-o-information-circle class="h-6 w-6 text-blue-500" />
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Confirm Template Application
                    </h3>
                </div>
            </div>

            <div class="mb-6">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $this->templateDescription }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-500 mt-2">
                    This will replace your current configuration settings. Are you sure you want to proceed?
                </p>
            </div>

            <div class="flex justify-end space-x-3">
                <x-filament::button
                    wire:click="cancelTemplate"
                    color="secondary"
                    size="sm">
                    Cancel
                </x-filament::button>
                <x-filament::button
                    wire:click="applyTemplate"
                    color="primary"
                    size="sm">
                    Apply Template
                </x-filament::button>
            </div>
        </div>
    </div>
    @endif

    <!-- API Response Modal -->
    @if($this->showResponseModal && ($this->apiResponse || $this->errorMessage))
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50" x-data>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-4xl w-full mx-4 shadow-xl max-h-[90vh] overflow-hidden flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if($this->apiResponse)
                            <x-heroicon-o-check-circle class="h-6 w-6 text-green-500" />
                        @else
                            <x-heroicon-o-exclamation-triangle class="h-6 w-6 text-red-500" />
                        @endif
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            @if($this->apiResponse)
                                API Response
                            @else
                                Error Response
                            @endif
                        </h3>
                    </div>
                </div>
                <button wire:click="closeResponseModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <x-heroicon-o-x-mark class="h-6 w-6" />
                </button>
            </div>

            @if($this->responseMetadata)
            <div class="flex space-x-4 text-sm text-gray-600 dark:text-gray-400 mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <span>Status: {{ $this->responseMetadata['status'] ?? 'N/A' }}</span>
                <span>Time: {{ $this->responseMetadata['execution_time'] ?? 'N/A' }}ms</span>
                <span>Model: {{ $this->responseMetadata['model'] ?? 'N/A' }}</span>
                <span>Length: {{ $this->responseMetadata['response_length'] ?? 'N/A' }} chars</span>
            </div>
            @endif

            <div class="flex-1 overflow-y-auto">
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
            </div>

            <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex space-x-2">
                    @if($this->apiResponse)
                    <x-filament::button
                        onclick="navigator.clipboard.writeText('{{ addslashes($this->apiResponse) }}')"
                        color="secondary"
                        size="sm">
                        <x-heroicon-o-clipboard class="w-4 h-4 mr-2" />
                        Copy Response
                    </x-filament::button>
                    @endif
                </div>
                <x-filament::button
                    wire:click="closeResponseModal"
                    color="primary"
                    size="sm">
                    Close
                </x-filament::button>
            </div>
        </div>
    </div>
    @endif
</x-filament-panels::page>
