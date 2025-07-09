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
</x-filament-panels::page>
