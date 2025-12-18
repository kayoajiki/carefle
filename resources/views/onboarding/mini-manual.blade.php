<x-layouts.app.sidebar title="持ち味レポ">
    <flux:main>
        <style>
            @media (min-width: 768px) {
                /* flux-mainに適度なパディングを設定 */
                flux-main {
                    padding-top: 1.5rem !important; /* 24px - 自然な間隔 */
                }
            }
        </style>
        <div class="min-h-screen bg-gradient-to-b from-[#E9F2FF] to-[#F6FBFF]">
            <div class="w-full max-w-4xl mx-auto content-padding pt-0 pb-8 md:pb-12">
            <div class="card-refined surface-blue p-10 soft-shadow-refined space-y-8">
                {{-- ヘッダー --}}
                <div class="text-center space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex-1"></div>
                        <div class="flex-1 text-center">
                            <h1 class="heading-1">{{ isset($manual['content']['title']) ? $manual['content']['title'] : '私の持ち味レポ' }}</h1>
                            <p class="body-text text-[#1E3A5F]/70">
                                生成日: {{ $manual['generated_at']->format('Y年n月j日') }}
                            </p>
                        </div>
                        <div class="flex-1 flex justify-end">
                            @if($canUpdate ?? false)
                                <form action="{{ route('onboarding.mini-manual.update') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn-secondary text-sm">
                                        更新する
                                    </button>
                                </form>
                            @else
                                @php
                                    $latestReport = \App\Models\StrengthsReport::getLatestForUser(auth()->id());
                                    $nextUpdateDate = $latestReport ? $latestReport->generated_at->copy()->addMonth() : null;
                                @endphp
                                @if($nextUpdateDate)
                                    <div class="text-right">
                                        <p class="body-small text-[#1E3A5F]/60">
                                            次回更新可能: {{ $nextUpdateDate->format('Y年n月j日') }}
                                        </p>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
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
                                データが不足しています。診断と日記の記録を続けることで、あなたの持ち味がより明確になります。
                            </p>
                        </div>
                    @endif
                </div>

                {{-- PDFダウンロードとSNSシェア案内 --}}
                <div class="mt-12 p-6 bg-[#E8F4FF] rounded-xl border border-[#6BB6FF]/20">
                    <h3 class="heading-3 text-lg mb-4 text-center">持ち味レポをシェアしよう</h3>
                    <p class="body-text text-[#1E3A5F]/70 text-center mb-6">
                        PDFをダウンロードして、XやInstagramに投稿してみましょう
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a 
                            href="{{ route('onboarding.mini-manual.pdf') }}" 
                            target="_blank"
                            class="btn-primary text-center"
                        >
                            PDFをダウンロード
                        </a>
                    </div>

                    <div class="mt-6 pt-6 border-t border-[#6BB6FF]/20">
                        <p class="body-small text-[#1E3A5F]/60 text-center mb-3">
                            SNSシェア用テキストテンプレート:
                        </p>
                        <div class="bg-white rounded-lg p-4 border border-[#2E5C8A]/10">
                            <p class="body-small text-[#1E3A5F] text-center">
                                「私の持ち味レポが完成しました！#キャリフレ」
                            </p>
                        </div>
                    </div>
                </div>

                {{-- クレジット --}}
                <div class="text-center pt-6 border-t border-[#2E5C8A]/10">
                    <p class="body-small text-[#1E3A5F]/60">
                        この持ち味レポはキャリフレで生成されました
                    </p>
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>

