<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold">AI Generation Quick Actions</h2>
            <a href="/admin/ai-generation" class="text-primary-600 hover:text-primary-500 text-sm font-medium">
                View Full Dashboard →
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach($actions as $action)
            <a href="{{ $action['url'] }}"
               class="flex flex-col items-center justify-center p-4 bg-white rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-sm transition-all duration-200 group">
                <div class="w-8 h-8 mb-2 flex items-center justify-center rounded-full bg-{{ $action['color'] }}-100 group-hover:bg-{{ $action['color'] }}-200 transition-colors">
                    <x-dynamic-component :component="$action['icon']" class="w-5 h-5 text-{{ $action['color'] }}-600" />
                </div>
                <span class="text-xs font-medium text-gray-700 text-center leading-tight">
                    {{ $action['label'] }}
                </span>
            </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
