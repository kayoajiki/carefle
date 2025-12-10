<x-layouts.app.sidebar :title="'内省チャット'">
    <flux:main>
        <div class="card-refined p-6">
            <livewire:diary-chat-form :date="request()->get('date')" :reflectionType="request()->get('type', 'daily')" />
        </div>
    </flux:main>
</x-layouts.app.sidebar>



