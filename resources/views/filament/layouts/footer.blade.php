<footer class="flex flex-row items-center justify-center text-center space-x-1 p-4 text-gray-600 dark:text-gray-400">
    <span>&copy; {{ date('Y') }}</span>
    <a class="font-semibold" href="https://hypefox.net" target="_blank">Hypefox Ltd</a>,
    <a class="font-semibold" href="https://pelican.dev/docs/#core-team" target="_blank">Pelican</a>
    @if(config('app.debug'))
        <div class="flex space-x-1 text-xs ml-4">
            <x-filament::icon
                :icon="'tabler-clock'"
                @class(['w-4 h-4 text-gray-500 dark:text-gray-400'])
            />
            <span>{{ round(microtime(true) - LARAVEL_START, 3) }}s</span>
        </div>
    @endif
</footer>
