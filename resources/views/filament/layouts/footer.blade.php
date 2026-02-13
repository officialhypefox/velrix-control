<footer class="flex flex-row flex-wrap items-center justify-center text-center space-x-1 p-4 text-gray-600 dark:text-gray-400">
    {{ \Filament\Support\Facades\FilamentView::renderHook(\App\Enums\CustomRenderHooks::FooterStart->value) }}

    <span>&copy; {{ date('Y') }}</span>
    <span>·</span>
    <a class="font-semibold" href="https://hypefox.net" target="_blank">Hypefox AB</a>
    <span>&amp;</span>
    <a class="font-semibold" href="https://pelican.dev/docs/#core-team" target="_blank">Pelican</a>

    @if(config('app.debug'))
        <div class="flex items-center space-x-1 text-xs ml-2">
            <x-filament::icon
                :icon="'tabler-clock'"
                @class(['w-4 h-4 text-gray-500 dark:text-gray-400'])
            />
            <span>{{ round(microtime(true) - LARAVEL_START, 3) }}s</span>
        </div>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\App\Enums\CustomRenderHooks::FooterEnd->value) }}
</footer>
