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
            <div class="w-full max-w-4xl mx-auto content-padding pt-0 pb-8 md:pb-12" style="scroll-behavior: smooth;">
            <div class="card-refined surface-blue p-8 md:p-12 soft-shadow-refined">
                {{-- ヘッダー --}}
                <div class="report-header">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="flex-1"></div>
                        <div class="flex-1 text-center">
                            <h1 class="heading-1 text-4xl md:text-5xl mb-4 whitespace-nowrap">{{ isset($manual['content']['title']) ? $manual['content']['title'] : '私の持ち味レポ' }}</h1>
                            <div class="flex flex-col items-center gap-3">
                                <div class="report-date-badge">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>{{ $manual['generated_at']->format('Y年n月j日') }}</span>
                                </div>
                                @if(!($canUpdate ?? false))
                                    @php
                                        $latestReport = \App\Models\StrengthsReport::getLatestForUser(auth()->id());
                                        $nextUpdateDate = $latestReport ? $latestReport->generated_at->copy()->addMonth() : null;
                                    @endphp
                                    @if($nextUpdateDate)
                                        <p class="body-small text-[#1E3A5F]/60">
                                            次回更新可能: {{ $nextUpdateDate->format('Y年n月j日') }}
                                        </p>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="flex-1 flex justify-end">
                            @if($canUpdate ?? false)
                                <form action="{{ route('onboarding.mini-manual.update') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="btn-secondary text-sm">
                                        更新する
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- アジェンダ --}}
                @if(isset($manual['content']) && isset($manual['content']['agenda']))
                <div class="text-center pb-8 mb-10 border-b border-[#2E5C8A]/10">
                    <h2 class="heading-2 text-2xl md:text-3xl text-[#2E5C8A]">
                        {{ $manual['content']['agenda'] }}
                    </h2>
                </div>
                @endif

                {{-- 持ち味レポコンテンツ（特徴3点） --}}
                <div class="space-y-6 mb-10">
                    @if(isset($manual['content']) && isset($manual['content']['strengths']) && is_array($manual['content']['strengths']) && !empty($manual['content']['strengths']))
                        @foreach($manual['content']['strengths'] as $index => $strength)
                        @php
                            $cardClasses = ['strength-card--blue', 'strength-card--purple', 'strength-card--green'];
                            $numberClasses = ['strength-number--blue', 'strength-number--purple', 'strength-number--green'];
                            $cardClass = $cardClasses[$index] ?? 'strength-card--blue';
                            $numberClass = $numberClasses[$index] ?? 'strength-number--blue';
                        @endphp
                        <div class="strength-card {{ $cardClass }}">
                            <div class="flex items-start gap-6">
                                <div class="strength-number {{ $numberClass }}">
                                    {{ $index + 1 }}
                                </div>
                                <div class="flex-1">
                                    <h3 class="strength-title">
                                        {{ $strength['title'] }}
                                    </h3>
                                    <p class="strength-description whitespace-pre-wrap">
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

                    {{-- 変化要素の見出し（2回目以降のみ表示） --}}
                    @if(!empty($manual['content']['changes']))
                        <div class="section-divider mt-12 md:mt-16 mb-8"></div>
                        <div class="text-center pb-8">
                            <h2 class="heading-2 text-3xl md:text-4xl text-[#2E5C8A] mb-2">
                                前回からの持ち味の変化や成長
                            </h2>
                            <p class="body-text text-[#1E3A5F]/70">
                                あなたの成長の軌跡
                            </p>
                        </div>
                    @endif

                    {{-- 変化要素（2回目以降のみ表示） --}}
                    @if(!empty($manual['content']['changes']))
                        <div class="space-y-6">
                            @foreach($manual['content']['changes'] as $index => $change)
                            <div class="change-card">
                                <div class="flex items-start gap-6">
                                    <div class="strength-number strength-number--orange">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="strength-title">
                                            {{ $change['title'] }}
                                        </h3>
                                        <p class="strength-description whitespace-pre-wrap">
                                            {{ $change['description'] }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- PDFダウンロードとSNSシェア案内 --}}
                <div class="mt-16 mb-8 p-6 md:p-8 bg-gradient-to-br from-[#E8F4FF] to-[#F0F7FF] rounded-2xl border border-[#6BB6FF]/20 shadow-lg">
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
                <div class="report-credit">
                    <p class="report-credit-text">
                        この持ち味レポはキャリフレで生成されました
                    </p>
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>

