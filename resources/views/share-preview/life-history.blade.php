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
                            <div class="mb-2">
                                <span class="font-semibold text-[#2E5C8A]">{{ $event->year }}年</span>
                            </div>
                            <h3 class="font-semibold text-[#1E3A5F] mb-2">{{ $event->title }}</h3>
                            
                            @if($event->description)
                            <p class="body-text text-[#1E3A5F] whitespace-pre-wrap mb-3">{{ $event->description }}</p>
                            @endif

                            <div class="text-sm text-[#1E3A5F]">
                                <span class="px-2 py-1 rounded-lg bg-[#F0F7FF] border border-[#2E5C8A]/10">
                                    モチベーション: {{ $event->motivation }}
                                </span>
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
                            <a href="{{ route('life-history.timeline') }}" class="btn-secondary flex-1 text-center">
                                キャンセル
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>
