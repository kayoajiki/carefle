<x-layouts.app.sidebar title="自己診断結果">
    <flux:main>
        <style>
            @media (min-width: 768px) {
                /* flux-mainに適度なパディングを設定 */
                flux-main {
                    padding-top: 1.5rem !important; /* 24px - 自然な間隔 */
                }
            }
        </style>
        <livewire:personality-assessment-form />
    </flux:main>
</x-layouts.app.sidebar>

