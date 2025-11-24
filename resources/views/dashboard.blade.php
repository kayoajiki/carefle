<x-layouts.app.sidebar :title="'ダッシュボード'">
    <flux:main>
        @php
            $diagnosisStatus = $latestDiagnosis
                ? '最終診断: '.$latestDiagnosis->updated_at?->format('n月j日')
                : ($draftDiagnosis ? '途中保存があります' : 'まだ診断を実施していません');
            $wcmStatus = $latestWcmSheet
                ? '更新日: '.$latestWcmSheet->updated_at?->format('n月j日')
                : 'まだ作成されていません';
            $assessmentStatus = 'まだ登録がありません';
            if ($latestAssessment) {
                $assessmentLabel = $latestAssessment->assessment_name ?: strtoupper($latestAssessment->assessment_type);
                $dateLabel = $latestAssessment->completed_at?->format('n月j日') ?? '日付未設定';
                $assessmentStatus = ($assessmentLabel ? $assessmentLabel.' / ' : '').$dateLabel;
            }
        @endphp

        <div class="min-h-screen bg-gradient-to-b from-[#E9F2FF] to-[#F6FBFF]">
            <div class="w-full max-w-7xl mx-auto content-padding section-spacing-sm space-y-12">
                <!-- ヘッダー & CTA -->
                <div class="card-refined surface-blue p-10 soft-shadow-refined">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div>
                            <p class="body-small text-[#5BA3D6] uppercase tracking-[0.2em]">Overview</p>
                            <h1 class="heading-2 mb-2 mt-1">ダッシュボード</h1>
                            <p class="body-large text-[#1E3A5F]/80">
                                今日の状態と次の一手をすばやく確認しましょう。
                            </p>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('diary') }}" class="btn-primary text-center">
                                日記を開く
                            </a>
                            <a href="{{ route('career.milestones') }}" class="btn-secondary text-center">
                                マイルストーンを見る
                            </a>
                        </div>
                    </div>
                </div>

                <!-- メイン機能カード -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- 現職満足度診断 -->
                    <div class="card-refined soft-shadow-refined-hover overflow-hidden h-full">
                        <div class="p-8 flex flex-col h-full">
                            <div class="flex items-start gap-4 mb-6">
                                <div class="w-14 h-14 rounded-2xl bg-[#6BB6FF]/10 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-7 h-7 text-[#6BB6FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="heading-3 text-xl mb-1">現職満足度診断</h3>
                                    <p class="body-small">{{ $diagnosisStatus }}</p>
                                </div>
                            </div>
                            <p class="body-text mb-6 flex-1">
                                仕事と暮らしの満足度バランスをレーダーチャートで把握できます。
                            </p>
                            <div class="flex flex-col gap-3">
                                @if($latestDiagnosis)
                                    <a href="{{ route('diagnosis.result', $latestDiagnosis->id) }}" class="btn-primary text-center">
                                        結果を見る
                                    </a>
                                    <a href="{{ route('diagnosis.start') }}" class="btn-secondary text-center text-sm">
                                        再診断する
                                    </a>
                                @elseif($draftDiagnosis)
                                    <a href="{{ route('diagnosis.start') }}" class="btn-primary text-center">
                                        続きから再開
                                    </a>
                                @else
                                    <a href="{{ route('diagnosis.start') }}" class="btn-primary text-center">
                                        診断を始める
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 自己診断結果 -->
                    <div class="card-refined soft-shadow-refined-hover overflow-hidden h-full">
                        <div class="p-8 flex flex-col h-full">
                            <div class="flex items-start gap-4 mb-6">
                                <div class="w-14 h-14 rounded-2xl bg-[#7C8CFF]/10 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-7 h-7 text-[#7C8CFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A4 4 0 018 16h8a4 4 0 012.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="heading-3 text-xl mb-1">自己診断結果</h3>
                                    <p class="body-small">{{ $assessmentStatus }}</p>
                                </div>
                            </div>
                            <p class="body-text mb-6 flex-1">
                                MBTIやストレングスなどの自己診断を記録し、比較しながら今の強みを把握できます。
                            </p>
                            <div class="flex flex-col gap-3">
                                <a href="{{ route('assessments.index') }}" class="btn-primary text-center">
                                    {{ $latestAssessment ? '最新結果を開く' : '診断結果を登録する' }}
                                </a>
                                @if($latestAssessment)
                                    <a href="{{ route('assessments.visualization') }}" class="btn-secondary text-center text-sm">
                                        可視化を見る
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 人生史 -->
                    <div class="card-refined soft-shadow-refined-hover overflow-hidden h-full">
                        <div class="p-8 flex flex-col h-full">
                            <div class="flex items-start gap-4 mb-6">
                                <div class="w-14 h-14 rounded-2xl bg-[#4A90E2]/10 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-7 h-7 text-[#4A90E2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="heading-3 text-xl mb-1">人生史</h3>
                                    <p class="body-small">{{ $hasLifeHistory ? $lifeEventCount.'件の出来事' : 'まだ作成がありません' }}</p>
                                </div>
                            </div>
                            <p class="body-text mb-6 flex-1">
                                人生の転機や背景をタイムラインで整理し、価値観のルーツを可視化します。
                            </p>
                            <div class="flex flex-col gap-3">
                                <a href="{{ route('life-history.timeline') }}" class="btn-primary text-center">
                                    {{ $hasLifeHistory ? 'タイムラインを見る' : '人生史を作成する' }}
                                </a>
                                @if($hasLifeHistory)
                                    <a href="{{ route('life-history') }}" class="btn-secondary text-center text-sm">
                                        編集一覧へ
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- WCM -->
                    <div class="card-refined soft-shadow-refined-hover overflow-hidden h-full">
                        <div class="p-8 flex flex-col h-full">
                            <div class="flex items-start gap-4 mb-6">
                                <div class="w-14 h-14 rounded-2xl bg-[#5BA3D6]/20 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-7 h-7 text-[#5BA3D6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="heading-3 text-xl mb-1">WCMシート</h3>
                                    <p class="body-small">{{ $wcmStatus }}</p>
                                </div>
                            </div>
                            <p class="body-text mb-6 flex-1">
                                Will・Can・Must を一枚にまとめ、今後の指針や行動計画を言語化します。
                            </p>
                            <div class="flex flex-col gap-3">
                                @if($latestWcmSheet)
                                    <a href="{{ route('wcm.sheet', $latestWcmSheet->id) }}" class="btn-primary text-center">
                                        最新シートを見る
                                    </a>
                                    <a href="{{ route('wcm.start') }}" class="btn-secondary text-center text-sm">
                                        新規作成する
                                    </a>
                                @else
                                    <a href="{{ route('wcm.start') }}" class="btn-primary text-center">
                                        WCMを始める
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- その他の機能カード -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- 面談申し込み -->
                    <div class="card-refined soft-shadow-refined-hover overflow-hidden">
                        <div class="p-8">
                            <div class="flex items-start gap-4 mb-6">
                                <div class="w-14 h-14 rounded-2xl bg-[#4A90E2]/10 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-7 h-7 text-[#4A90E2]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="heading-3 text-xl mb-1">面談申し込み</h3>
                                    <p class="body-small">専門家との1on1セッション</p>
                                </div>
                            </div>
                            <p class="body-text mb-6">
                                カウンセラーとじっくり対話して、今後の一歩を具体化しましょう。
                            </p>
                            <a href="{{ route('consultation.request') }}" class="btn-primary w-full text-center">
                                面談を申し込む
                            </a>
                        </div>
                    </div>

                    <!-- チャット相談 -->
                    <div class="card-refined soft-shadow-refined-hover overflow-hidden">
                        <div class="p-8">
                            <div class="flex items-start gap-4 mb-6">
                                <div class="w-14 h-14 rounded-2xl bg-[#6BB6FF]/10 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-7 h-7 text-[#6BB6FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="heading-3 text-xl mb-1">チャット相談</h3>
                                    <p class="body-small">気になることをすぐに相談</p>
                                </div>
                            </div>
                            <p class="body-text mb-6">
                                迷ったときはチャットで素早く相談。小さな違和感も言語化して伴走します。
                            </p>
                            <a href="{{ route('chat.index') }}" class="btn-primary w-full text-center">
                                チャットを開く
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>
