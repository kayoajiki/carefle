<x-layouts.app.sidebar :title="$title ?? '管理画面'">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>






