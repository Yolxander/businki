<x-filament-panels::page>
    <div class="space-y-6">
        <!-- API Status and Usage Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-filament::card>
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-signal class="h-8 w-8 text-green-500" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">API Status</p>
                        <p class="text-lg font-semibold text-gray-900">Connected</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-chart-bar class="h-8 w-8 text-blue-500" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Requests</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $this->getApiUsageStats()['total_requests'] }}</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-currency-dollar class="h-8 w-8 text-yellow-500" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Est. Cost</p>
                        <p class="text-lg font-semibold text-gray-900">${{ number_format($this->getApiUsageStats()['cost_estimate'], 2) }}</p>
                    </div>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <x-heroicon-o-calendar class="h-8 w-8 text-purple-500" />
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Today's Requests</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $this->getApiUsageStats()['requests_today'] }}</p>
                    </div>
                </div>
            </x-filament::card>
        </div>

        <!-- Main Form -->
        {{ $this->form }}

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

        <!-- Settings Management -->
        <x-filament::card>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold">Saved Settings</h2>
                <x-filament::button
                    wire:click="$toggle('showSaveSettingForm')"
                    color="primary"
                    size="sm">
                    <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                    Save Current Settings
                </x-filament::button>
            </div>

            @if($this->showSaveSettingForm ?? false)
            <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-filament::input.wrapper>
                        <x-filament::input
                            wire:model="settingName"
                            type="text"
                            placeholder="Setting name (e.g., 'proposal_generation')"
                            label="Setting Name" />
                    </x-filament::input.wrapper>

                    <x-filament::input.wrapper>
                        <x-filament::input
                            wire:model="settingDescription"
                            type="text"
                            placeholder="Brief description of this setting"
                            label="Description" />
                    </x-filament::input.wrapper>
                </div>

                <div class="mt-4 flex space-x-2">
                    <x-filament::button
                        wire:click="saveAsSetting"
                        color="success"
                        size="sm">
                        <x-heroicon-o-check class="w-4 h-4 mr-2" />
                        Save Setting
                    </x-filament::button>

                    <x-filament::button
                        wire:click="$set('showSaveSettingForm', false)"
                        color="secondary"
                        size="sm">
                        <x-heroicon-o-x-mark class="w-4 h-4 mr-2" />
                        Cancel
                    </x-filament::button>
                </div>
            </div>
            @endif

            {{ $this->table }}
        </x-filament::card>

        <!-- Quick Test Templates -->
        <x-filament::card>
            <h2 class="text-xl font-semibold mb-4">Quick Test Templates</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <x-filament::button
                    wire:click="loadProposalTemplate"
                    color="secondary"
                    class="h-auto p-4 text-left">
                    <div class="flex items-start">
                        <x-heroicon-o-document-text class="w-5 h-5 mr-3 mt-0.5" />
                        <div>
                            <div class="font-medium">Proposal Generation</div>
                            <div class="text-sm text-gray-500">Test proposal creation</div>
                        </div>
                    </div>
                </x-filament::button>

                <x-filament::button
                    wire:click="loadTaskTemplate"
                    color="secondary"
                    class="h-auto p-4 text-left">
                    <div class="flex items-start">
                        <x-heroicon-o-check-circle class="w-5 h-5 mr-3 mt-0.5" />
                        <div>
                            <div class="font-medium">Task Generation</div>
                            <div class="text-sm text-gray-500">Test task creation</div>
                        </div>
                    </div>
                </x-filament::button>

                <x-filament::button
                    wire:click="loadCreativeTemplate"
                    color="secondary"
                    class="h-auto p-4 text-left">
                    <div class="flex items-start">
                        <x-heroicon-o-light-bulb class="w-5 h-5 mr-3 mt-0.5" />
                        <div>
                            <div class="font-medium">Creative Writing</div>
                            <div class="text-sm text-gray-500">Test creative content</div>
                        </div>
                    </div>
                </x-filament::button>

                <x-filament::button
                    wire:click="loadCodeTemplate"
                    color="secondary"
                    class="h-auto p-4 text-left">
                    <div class="flex items-start">
                        <x-heroicon-o-code-bracket class="w-5 h-5 mr-3 mt-0.5" />
                        <div>
                            <div class="font-medium">Code Generation</div>
                            <div class="text-sm text-gray-500">Test code generation</div>
                        </div>
                    </div>
                </x-filament::button>

                <x-filament::button
                    wire:click="loadAnalysisTemplate"
                    color="secondary"
                    class="h-auto p-4 text-left">
                    <div class="flex items-start">
                        <x-heroicon-o-chart-pie class="w-5 h-5 mr-3 mt-0.5" />
                        <div>
                            <div class="font-medium">Data Analysis</div>
                            <div class="text-sm text-gray-500">Test data analysis</div>
                        </div>
                    </div>
                </x-filament::button>

                <x-filament::button
                    wire:click="loadTranslationTemplate"
                    color="secondary"
                    class="h-auto p-4 text-left">
                    <div class="flex items-start">
                        <x-heroicon-o-language class="w-5 h-5 mr-3 mt-0.5" />
                        <div>
                            <div class="font-medium">Translation</div>
                            <div class="text-sm text-gray-500">Test translation</div>
                        </div>
                    </div>
                </x-filament::button>
            </div>
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
</x-filament-panels::page>
