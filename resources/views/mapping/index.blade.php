<x-layouts.app.sidebar title="曼荼羅マッピング">
    <flux:main>
        <style>
            @media (min-width: 768px) {
                flux-main {
                    padding-top: 1.5rem !important;
                }
            }
        </style>
        <div class="min-h-screen bg-gradient-to-b from-[#E9F2FF] to-[#F6FBFF]">
            <div class="w-full max-w-6xl mx-auto content-padding pt-0 pb-8 md:pb-12">
                {{-- マッピング進捗バー --}}
                <livewire:mapping-progress-bar />

                {{-- 曼荼羅可視化 --}}
                <div class="mt-8">
                    <livewire:user-mapping-visualization />
                </div>

                {{-- 変容の可視化（Phase 8.2） --}}
                <div class="mt-8">
                    <livewire:transformation-visualization />
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>

