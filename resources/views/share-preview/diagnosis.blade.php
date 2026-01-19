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
                            <div class="mb-4">
                                <p class="body-text text-[#1E3A5F] mb-2">満足度スコア</p>
                                <p class="heading-1 text-4xl text-[#2E5C8A]">{{ $diagnosis->work_score ?? 0 }}<span class="text-xl">/100</span></p>
                            </div>
                            
                            @if($diagnosis->life_score)
                            <div class="mb-4">
                                <p class="body-text text-[#1E3A5F] mb-2">重要度スコア</p>
                                <p class="heading-1 text-4xl text-[#2E5C8A]">{{ $diagnosis->life_score }}<span class="text-xl">/100</span></p>
                            </div>
                            @endif

                            <div class="text-sm text-[#1E3A5F]">
                                <p>診断ID: #{{ str_pad($diagnosis->id, 4, '0', STR_PAD_LEFT) }}</p>
                                <p>診断日: {{ $diagnosis->created_at->format('Y年n月j日') }}</p>
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
                            <a href="{{ route('diagnosis.result', ['id' => $id]) }}" class="btn-secondary flex-1 text-center">
                                キャンセル
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>
