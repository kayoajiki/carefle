<x-layouts.app.sidebar :title="'ダッシュボード'">
    <flux:main>
        <style>
            * {
                font-family: 'Noto Sans JP', ui-sans-serif, system-ui, sans-serif;
            }
        </style>
        <div class="min-h-screen bg-[#f2f7f5] px-4 py-8 md:px-8">
            <div class="w-full max-w-7xl mx-auto">
                <!-- ヘッダー -->
                <div class="mb-8">
                    <h1 class="text-3xl md:text-4xl font-bold text-[#00473e] mb-2">
                        ダッシュボード
                    </h1>
                    <p class="text-base md:text-lg text-[#475d5b]">
                        あなたのキャリアを見つめ直すためのツールへアクセス
                    </p>
                </div>

                <!-- メイン機能カード -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <!-- 現職満足度診断 -->
                    <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-[#faae2b]/10 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-[#faae2b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-[#00473e]">現職満足度診断</h3>
                                        <p class="text-xs text-[#475d5b] mt-0.5">
                                            @if($latestDiagnosis)
                                                完了済み
                                            @elseif($draftDiagnosis)
                                                下書き保存中
                                            @else
                                                未開始
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm text-[#475d5b] leading-relaxed mb-4">
                                現在の仕事と生活の満足度を可視化し、バランスを確認します。
                            </p>
                            <div class="flex flex-col gap-2">
                                @if($latestDiagnosis)
                                    <a href="{{ route('diagnosis.result', $latestDiagnosis->id) }}" 
                                       class="inline-flex items-center justify-center px-4 py-2.5 bg-[#faae2b] text-[#00473e] font-semibold rounded-lg hover:bg-[#faae2b]/90 transition-colors">
                                        結果を見る
                                    </a>
                                    <a href="{{ route('diagnosis.start') }}" 
                                       class="inline-flex items-center justify-center px-4 py-2.5 border-2 border-[#00473e] text-[#00473e] font-semibold rounded-lg hover:bg-[#00473e]/5 transition-colors text-sm">
                                        再診断する
                                    </a>
                                @elseif($draftDiagnosis)
                                    <a href="{{ route('diagnosis.start') }}" 
                                       class="inline-flex items-center justify-center px-4 py-2.5 bg-[#faae2b] text-[#00473e] font-semibold rounded-lg hover:bg-[#faae2b]/90 transition-colors">
                                        続きから始める
                                    </a>
                                @else
                                    <a href="{{ route('diagnosis.start') }}" 
                                       class="inline-flex items-center justify-center px-4 py-2.5 bg-[#faae2b] text-[#00473e] font-semibold rounded-lg hover:bg-[#faae2b]/90 transition-colors">
                                        診断を始める
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 人生史 -->
                    <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-[#00473e]/10 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-[#00473e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-[#00473e]">人生史</h3>
                                        <p class="text-xs text-[#475d5b] mt-0.5">
                                            @if($hasLifeHistory)
                                                {{ $lifeEventCount }}件の出来事
                                            @else
                                                未作成
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm text-[#475d5b] leading-relaxed mb-4">
                                これまでの人生のターニングポイントを整理し、自分の歩みを振り返ります。
                            </p>
                            <div class="flex flex-col gap-2">
                                <a href="{{ route('life-history.timeline') }}" 
                                   class="inline-flex items-center justify-center px-4 py-2.5 bg-[#faae2b] text-[#00473e] font-semibold rounded-lg hover:bg-[#faae2b]/90 transition-colors">
                                    {{ $hasLifeHistory ? 'タイムラインを見る' : '人生史を作成する' }}
                                </a>
                                @if($hasLifeHistory)
                                    <a href="{{ route('life-history') }}" 
                                       class="inline-flex items-center justify-center px-4 py-2.5 border-2 border-[#00473e] text-[#00473e] font-semibold rounded-lg hover:bg-[#00473e]/5 transition-colors text-sm">
                                        一覧を見る
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- WCM -->
                    <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-[#ffa8ba]/20 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-[#ffa8ba]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-[#00473e]">WCMシート</h3>
                                        <p class="text-xs text-[#475d5b] mt-0.5">
                                            @if($latestWcmSheet)
                                                最新版あり
                                            @else
                                                未作成
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm text-[#475d5b] leading-relaxed mb-4">
                                Will（やりたいこと）、Can（できること）、Must（やるべきこと）を整理します。
                            </p>
                            <div class="flex flex-col gap-2">
                                @if($latestWcmSheet)
                                    <a href="{{ route('wcm.sheet', $latestWcmSheet->id) }}" 
                                       class="inline-flex items-center justify-center px-4 py-2.5 bg-[#faae2b] text-[#00473e] font-semibold rounded-lg hover:bg-[#faae2b]/90 transition-colors">
                                        シートを見る
                                    </a>
                                    <a href="{{ route('wcm.start') }}" 
                                       class="inline-flex items-center justify-center px-4 py-2.5 border-2 border-[#00473e] text-[#00473e] font-semibold rounded-lg hover:bg-[#00473e]/5 transition-colors text-sm">
                                        新規作成する
                                    </a>
                                @else
                                    <a href="{{ route('wcm.start') }}" 
                                       class="inline-flex items-center justify-center px-4 py-2.5 bg-[#faae2b] text-[#00473e] font-semibold rounded-lg hover:bg-[#faae2b]/90 transition-colors">
                                        WCMを始める
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- その他の機能カード -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- 面談申し込み -->
                    <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-[#cddeaf]/30 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-[#00473e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-[#00473e]">面談申し込み</h3>
                                        <p class="text-xs text-[#475d5b] mt-0.5">専門家と対話</p>
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm text-[#475d5b] leading-relaxed mb-4">
                                キャリアカウンセラーとの1対1の面談を予約し、より深くキャリアについて対話します。
                            </p>
                            <a href="{{ route('consultation.request') }}" 
                               class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-[#faae2b] text-[#00473e] font-semibold rounded-lg hover:bg-[#faae2b]/90 transition-colors">
                                面談を申し込む
                            </a>
                        </div>
                    </div>

                    <!-- チャット相談 -->
                    <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-gray-100">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-[#d3f9d8]/40 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-[#00473e]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-[#00473e]">チャット相談</h3>
                                        <p class="text-xs text-[#475d5b] mt-0.5">気軽に相談</p>
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm text-[#475d5b] leading-relaxed mb-4">
                                ちょっとした疑問や不安をチャットで気軽に相談できます。
                            </p>
                            <a href="{{ route('chat.index') }}" 
                               class="inline-flex items-center justify-center w-full px-4 py-2.5 bg-[#faae2b] text-[#00473e] font-semibold rounded-lg hover:bg-[#faae2b]/90 transition-colors">
                                チャットを開く
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>
