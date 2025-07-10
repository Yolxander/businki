<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Manual Generation Form -->
        {{ $this->form }}

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
    </div>

    <!-- Prompt Preview Modal -->
    <x-filament::modal id="prompt-preview-modal" width="4xl">
        <x-slot name="header">
            <h2 class="text-lg font-medium">Prompt Preview</h2>
        </x-slot>

        <div class="space-y-4">
            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg border dark:border-gray-700">
                <div class="prose dark:prose-invert max-w-none">
                    <pre class="whitespace-pre-wrap text-sm text-gray-900 dark:text-gray-100 bg-transparent border-0 p-0 m-0 font-sans">{{ $this->previewPrompt }}</pre>
                </div>
            </div>

            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <span class="font-medium">Length:</span> {{ strlen($this->previewPrompt ?? '') }} characters
                </div>

                <div class="flex space-x-3">
                    <x-filament::button
                        onclick="navigator.clipboard.writeText('{{ addslashes($this->previewPrompt ?? '') }}')"
                        color="secondary"
                        size="lg"
                        class="w-68  flex flex-row items-center justify-center ">
                        <x-heroicon-o-clipboard class="w-4 h-4" />
                        Copy Prompt
                    </x-filament::button>

                    <x-filament::button
                        wire:click="generateFromPreview"
                        color="primary"
                        size="lg"
                        class="w-48 flex flex-row items-center justify-center ">
                        <x-heroicon-o-sparkles class="w-4 h-4" />
                        Generate Content
                    </x-filament::button>
                </div>
            </div>
        </div>


    </x-filament::modal>
</x-filament-panels::page>
