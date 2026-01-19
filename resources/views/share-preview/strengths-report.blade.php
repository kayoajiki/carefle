<x-layouts.app.sidebar title="管理者への共有確認">
    <flux:main>
        <div class="min-h-screen bg-gradient-to-b from-[#E9F2FF] to-[#F6FBFF]">
            <div class="w-full max-w-4xl mx-auto content-padding section-spacing-sm">
                <div class="card-refined surface-blue p-8">
                    <h1 class="heading-2 mb-6">管理者への共有確認</h1>
                    
                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="body-text text-[#1E3A5F]">
                            以下の内容を管理者が閲覧できるようにしますか？<br>
                            共有を許可すると、管理者はこの内容を確認できるようになります。
                        </p>
                    </div>

                    <div class="mb-8">
                        <h2 class="heading-3 text-xl mb-4">共有する内容</h2>
                        <div class="bg-white rounded-xl p-6 border border-[#2E5C8A]/20">
                            <h3 class="font-semibold text-[#2E5C8A] mb-4">{{ $report->content['title'] ?? '私の持ち味レポ' }}</h3>
                            
                            @if(isset($report->content['agenda']))
                            <p class="body-text text-[#1E3A5F] mb-4">{{ $report->content['agenda'] }}</p>
                            @endif

                            @if(isset($report->content['strengths']) && is_array($report->content['strengths']))
                            <div class="space-y-3">
                                @foreach($report->content['strengths'] as $index => $strength)
                                <div class="border-b border-[#2E5C8A]/10 pb-3 last:border-0">
                                    <h4 class="font-semibold text-[#1E3A5F] mb-1">{{ $strength['title'] ?? '' }}</h4>
                                    <p class="body-small text-[#1E3A5F]/70">{{ mb_substr($strength['description'] ?? '', 0, 100) }}...</p>
                                </div>
                                @endforeach
                            </div>
                            @endif

                            <div class="mt-4 text-sm text-[#1E3A5F]">
                                <p>生成日: {{ $report->generated_at->format('Y年n月j日') }}</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('share-preview.confirm') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">
                        <input type="hidden" name="id" value="{{ $id }}">
                        
                        <div class="flex gap-4">
                            <button type="submit" class="btn-primary flex-1">
                                共有を許可する
                            </button>
                            <a href="{{ route('onboarding.mini-manual') }}" class="btn-secondary flex-1 text-center">
                                キャンセル
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>
