<x-layouts.app.sidebar title="{{ $contextLabel }}の持ち味レポ">
    <flux:main>
        <style>
            @media (min-width: 768px) {
                flux-main {
                    padding-top: 1.5rem !important;
                }
            }
        </style>
        <div class="min-h-screen bg-gradient-to-b from-[#E9F2FF] to-[#F6FBFF]">
            <div class="w-full max-w-4xl mx-auto content-padding pt-0 pb-8 md:pb-12">
                @if($canGenerate && $manual)
                <div class="card-refined surface-blue p-10 soft-shadow-refined space-y-8">
                    {{-- ヘッダー --}}
                    <div class="text-center space-y-4">
                        <h1 class="heading-1">{{ isset($manual['content']['title']) ? $manual['content']['title'] : "私の{$contextLabel}の持ち味レポ" }}</h1>
                        <p class="body-text text-[#1E3A5F]/70">
                            生成日: {{ $manual['generated_at']->format('Y年n月j日') }}
                        </p>
                    </div>

                    {{-- アジェンダ --}}
                    @if(isset($manual['content']) && isset($manual['content']['agenda']))
                    <div class="text-center pb-6 border-b border-[#2E5C8A]/10">
                        <h2 class="heading-2 text-2xl text-[#2E5C8A]">
                            {{ $manual['content']['agenda'] }}
                        </h2>
                    </div>
                    @endif

                    {{-- 持ち味レポコンテンツ（特徴3点） --}}
                    <div class="space-y-8">
                        @if(isset($manual['content']) && isset($manual['content']['strengths']) && is_array($manual['content']['strengths']) && !empty($manual['content']['strengths']))
                            @foreach($manual['content']['strengths'] as $index => $strength)
                            <div class="border-b border-[#2E5C8A]/10 pb-8 last:border-b-0 last:pb-0">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-[#6BB6FF] text-white flex items-center justify-center font-bold text-lg">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="heading-3 text-xl mb-4 text-[#2E5C8A]">
                                            {{ $strength['title'] }}
                                        </h3>
                                        <p class="body-text text-[#1E3A5F] leading-relaxed whitespace-pre-wrap">
                                            {{ $strength['description'] }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        @else
                            <div class="text-center py-8">
                                <p class="body-text text-[#1E3A5F]/70">
                                    データが不足しています。{{ $contextLabel }}に関する日記の記録を続けることで、あなたの持ち味がより明確になります。
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- クレジット --}}
                    <div class="text-center pt-6 border-t border-[#2E5C8A]/10">
                        <p class="body-small text-[#1E3A5F]/60">
                            この持ち味レポはキャリフレで生成されました
                        </p>
                    </div>
                </div>
                @else
                {{-- 生成できない場合 --}}
                <div class="card-refined surface-blue p-10 soft-shadow-refined space-y-8">
                    <div class="text-center space-y-4">
                        <h1 class="heading-1">{{ $contextLabel }}の持ち味レポ</h1>
                        <p class="body-text text-[#1E3A5F]/70">
                            {{ $contextLabel }}に関する日記がまだ不足しています
                        </p>
                    </div>

                    <div class="bg-[#E8F4FF] rounded-xl p-6 border border-[#6BB6FF]/20">
                        <div class="text-center space-y-4">
                            <div class="text-4xl font-bold text-[#6BB6FF]">
                                {{ $currentCount }}/{{ $minCount }}件
                            </div>
                            <p class="body-text text-[#1E3A5F]/70">
                                あと{{ $minCount - $currentCount }}件の{{ $contextLabel }}に関する日記を記録すると、持ち味レポを生成できます。
                            </p>
                        </div>
                    </div>

                    <div class="text-center">
                        <a 
                            href="{{ route('diary') }}" 
                            class="btn-primary inline-block"
                        >
                            日記を書く
                        </a>
                    </div>

                    <div class="text-center">
                        <a 
                            href="{{ route('manual.index') }}" 
                            class="body-text text-[#6BB6FF] hover:underline"
                        >
                            ← コンテキスト一覧に戻る
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>


