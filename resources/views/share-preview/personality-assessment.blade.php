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
                            <h3 class="font-semibold text-[#2E5C8A] mb-2">
                                {{ $assessment->assessment_name ?? strtoupper($assessment->assessment_type) }}
                            </h3>
                            
                            @php
                                $formattedResult = $assessment->formatted_result;
                            @endphp

                            @if($assessment->assessment_type === 'mbti' && isset($formattedResult['type']))
                            <div class="mb-3">
                                <p class="body-text text-[#1E3A5F]">タイプ: <span class="font-semibold">{{ $formattedResult['type'] }}</span></p>
                            </div>
                            @endif

                            @if($assessment->assessment_type === 'strengthsfinder' && isset($formattedResult['top5']))
                            <div class="mb-3">
                                <p class="body-text text-[#1E3A5F] mb-2">トップ5の強み:</p>
                                <ul class="list-disc list-inside body-small text-[#1E3A5F]">
                                    @foreach($formattedResult['top5'] as $strength)
                                    <li>{{ $strength }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            @if($assessment->assessment_type === 'enneagram' && isset($formattedResult['type']))
                            <div class="mb-3">
                                <p class="body-text text-[#1E3A5F]">タイプ: <span class="font-semibold">{{ $formattedResult['type'] }}</span></p>
                                @if(isset($formattedResult['wing']))
                                <p class="body-small text-[#1E3A5F]">ウィング: {{ $formattedResult['wing'] }}</p>
                                @endif
                            </div>
                            @endif

                            @if($assessment->notes)
                            <div class="mt-3">
                                <p class="body-text text-[#1E3A5F] whitespace-pre-wrap">{{ $assessment->notes }}</p>
                            </div>
                            @endif

                            <div class="mt-4 text-sm text-[#1E3A5F]">
                                <p>記録日: {{ $assessment->completed_at ? $assessment->completed_at->format('Y年n月j日') : $assessment->created_at->format('Y年n月j日') }}</p>
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
                            <a href="{{ route('assessments.index') }}" class="btn-secondary flex-1 text-center">
                                キャンセル
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>
