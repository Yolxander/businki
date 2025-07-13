<x-filament-panels::page>
    <div x-data="{ activeTab: 'overview' }">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
            <nav class="-mb-px flex space-x-24" aria-label="Tabs">
                <button
                    @click="activeTab = 'overview'"
                    :class="{ 'border-primary-500 text-primary-600 dark:text-primary-400': activeTab === 'overview', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'overview' }"
                    class="whitespace-nowrap py-2 px-6 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors duration-200"
                >
                    <x-heroicon-o-information-circle class="w-4 h-4" />
                    Overview
                </button>

                <button
                    @click="activeTab = 'api-endpoints'"
                    :class="{ 'border-primary-500 text-primary-600 dark:text-primary-400': activeTab === 'api-endpoints', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'api-endpoints' }"
                    class="whitespace-nowrap py-2 px-6 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors duration-200"
                >
                    <x-heroicon-o-code-bracket class="w-4 h-4" />
                    API Endpoints
                </button>

                <button
                    @click="activeTab = 'request-examples'"
                    :class="{ 'border-primary-500 text-primary-600 dark:text-primary-400': activeTab === 'request-examples', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'request-examples' }"
                    class="whitespace-nowrap py-2 px-6 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors duration-200"
                >
                    <x-heroicon-o-arrow-up-tray class="w-4 h-4" />
                    Request Examples
                </button>

                <button
                    @click="activeTab = 'response-examples'"
                    :class="{ 'border-primary-500 text-primary-600 dark:text-primary-400': activeTab === 'response-examples', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'response-examples' }"
                    class="whitespace-nowrap py-2 px-6 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors duration-200"
                >
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
                    Response Examples
                </button>

                <button
                    @click="activeTab = 'best-practices'"
                    :class="{ 'border-primary-500 text-primary-600 dark:text-primary-400': activeTab === 'best-practices', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'best-practices' }"
                    class="whitespace-nowrap py-2 px-6 border-b-2 font-medium text-sm flex items-center gap-2 transition-colors duration-200"
                >
                    <x-heroicon-o-light-bulb class="w-4 h-4" />
                    Best Practices
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div>
            <div x-show="activeTab === 'overview'" x-transition>
                @include('filament.pages.documentation.overview')
            </div>

            <div x-show="activeTab === 'api-endpoints'" x-transition style="display: none;">
                @include('filament.pages.documentation.api-endpoints')
            </div>

            <div x-show="activeTab === 'request-examples'" x-transition style="display: none;">
                @include('filament.pages.documentation.request-examples')
            </div>

            <div x-show="activeTab === 'response-examples'" x-transition style="display: none;">
                @include('filament.pages.documentation.response-examples')
            </div>

            <div x-show="activeTab === 'best-practices'" x-transition style="display: none;">
                @include('filament.pages.documentation.best-practices')
            </div>
        </div>
    </div>
</x-filament-panels::page>
