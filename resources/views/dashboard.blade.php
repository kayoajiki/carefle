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
                <!-- オンボーディング進捗バー -->
                <livewire:onboarding-progress-bar />
                
                <!-- マッピング進捗バー -->
                <livewire:mapping-progress-bar />
                
                <!-- 診断促進モーダル -->
                <livewire:diagnosis-prompt-modal />
                
                <!-- 日記促進モーダル -->
                <livewire:diary-prompt-modal />
                
                <!-- ヘッダー & CTA -->
                <div class="card-refined surface-blue p-10 soft-shadow-refined space-y-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div>
                            <p class="body-small text-[#5BA3D6] uppercase tracking-[0.2em]">Overview</p>
                            <h1 class="heading-2 mb-2 mt-1">ダッシュボード</h1>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('my-goal') }}" class="btn-secondary text-center">
                                マイゴール
                            </a>
                        </div>
                    </div>

                    {{-- ゴールイメージ（案1） --}}
                    @if($user?->goal_image)
                        <div class="bg-white rounded-2xl border-2 border-blue-200 p-6 space-y-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-xl">💫</span>
                                    <h2 class="heading-3 text-xl">あなたのゴールイメージ</h2>
                                </div>
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('my-goal.display-mode') }}" class="flex gap-1 bg-[#f4f8ff] border border-blue-100 rounded-full px-1 py-1">
                                        @csrf
                                        <input type="hidden" name="mode" value="text">
                                        <button type="submit" class="px-3 py-1 rounded-full body-small {{ $user->goal_display_mode === 'text' ? 'bg-[#2E5C8A] text-white' : 'text-[#2E5C8A]' }}">文字</button>
                                    </form>
                                    <form method="POST" action="{{ route('my-goal.display-mode') }}" class="flex gap-1 bg-[#f4f8ff] border border-blue-100 rounded-full px-1 py-1">
                                        @csrf
                                        <input type="hidden" name="mode" value="image">
                                        <button type="submit" class="px-3 py-1 rounded-full body-small {{ $user->goal_display_mode === 'image' ? 'bg-[#2E5C8A] text-white' : 'text-[#2E5C8A]' }}">図式</button>
                                    </form>
                                    <a href="{{ route('my-goal') }}" class="body-small text-[#2E5C8A] hover:text-[#6BB6FF]">編集</a>
                                </div>
                            </div>

                            @if($user->goal_display_mode === 'image')
                                @if($user->goal_image_url)
                                    <div class="bg-[#F6FBFF] border border-blue-100 rounded-xl p-4">
                                        <img src="{{ $user->goal_image_url }}" alt="ゴールイメージ" class="w-full rounded-lg">
                                    </div>
                                @else
                                    <div class="bg-[#F6FBFF] border border-dashed border-blue-200 rounded-xl p-6 text-center">
                                        <p class="body-small text-[#1E3A5F]/70 mb-3">図式がまだありません。マイゴール画面で生成してください。</p>
                                        <a href="{{ route('my-goal') }}" class="btn-primary text-sm">図式を生成する</a>
                                    </div>
                                @endif
                            @else
                                <p class="body-text text-[#1E3A5F] leading-relaxed whitespace-pre-line">{{ $user->goal_image }}</p>
                            @endif
                        </div>
                    @else
                        <div class="bg-white rounded-2xl border-2 border-dashed border-blue-200 p-6 text-center">
                            <p class="body-text text-[#1E3A5F]/80 mb-3">まだマイゴールが設定されていません。まずは作成しましょう。</p>
                            <a href="{{ route('my-goal') }}" class="btn-primary">マイゴールを設定する</a>
                        </div>
                    @endif
                </div>

                <!-- 内省ストリークカード -->
                <div class="card-refined surface-blue p-8 soft-shadow-refined">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="heading-3 text-xl mb-2">内省の習慣化</h2>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-3xl font-bold text-[#6BB6FF]">🔥</span>
                                    <div>
                                        <p class="body-text font-semibold text-[#2E5C8A]">連続記録</p>
                                        <p class="heading-2 text-[#6BB6FF]">{{ $reflectionStreak }}日</p>
                                    </div>
                                </div>
                                <div class="h-12 w-px bg-[#2E5C8A]/20"></div>
                                <div>
                                    <p class="body-small text-[#1E3A5F]/60">今週の内省</p>
                                    <p class="heading-3 text-[#2E5C8A]">{{ $weeklyReflectionCount }}回</p>
                                </div>
                                <div class="h-12 w-px bg-[#2E5C8A]/20"></div>
                                <div>
                                    <p class="body-small text-[#1E3A5F]/60">今月の内省</p>
                                    <p class="heading-3 text-[#2E5C8A]">{{ $monthlyReflectionCount }}回</p>
                                </div>
                            </div>
                        </div>

                        {{-- 7日間記録の進捗バー（オンボーディング中のみ表示） --}}
                        @if(isset($diary7DaysProgress) && $diary7DaysProgress['show'])
                            <div class="mt-6 p-4 bg-[#E8F4FF] rounded-xl border border-[#6BB6FF]/20">
                                <div class="flex items-center justify-between mb-3">
                                    <p class="body-text font-semibold text-[#2E5C8A]">持ち味レポまで</p>
                                    <p class="body-small text-[#1E3A5F]/70">{{ $diary7DaysProgress['current'] }}/{{ $diary7DaysProgress['target'] }}日</p>
                                </div>
                                
                                {{-- 7日間カレンダーミニマップ --}}
                                @if(isset($diary7DaysCalendar))
                                    <div class="flex items-center justify-between gap-1 mb-3">
                                        @foreach($diary7DaysCalendar as $day)
                                            <div class="flex-1 flex flex-col items-center">
                                                <p class="text-[10px] text-[#1E3A5F]/50 mb-1">{{ $day['dayOfWeek'] }}</p>
                                                <div class="w-8 h-8 rounded-lg flex items-center justify-center {{ $day['hasDiary'] ? 'bg-[#6BB6FF] text-white' : 'bg-white/60 text-[#1E3A5F]/30' }} border {{ $day['hasDiary'] ? 'border-[#6BB6FF]' : 'border-[#2E5C8A]/20' }}">
                                                    <span class="text-xs font-semibold">{{ $day['day'] }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                
                                <div class="w-full bg-white/60 rounded-full h-2 overflow-hidden mb-2">
                                    <div 
                                        class="h-2 bg-[#6BB6FF] transition-all duration-500"
                                        style="width: {{ $diary7DaysProgress['percentage'] }}%"
                                    ></div>
                                </div>
                                @if($diary7DaysProgress['remaining'] > 0)
                                    <p class="body-small text-[#1E3A5F]/70 text-center">
                                        あと{{ $diary7DaysProgress['remaining'] }}日で持ち味レポが生成されます！
                                    </p>
                                @else
                                    <p class="body-small text-[#2E5C8A] font-semibold text-center">
                                        🎉 7日間の記録が完了しました！持ち味レポを生成できます
                                    </p>
                                @endif
                            </div>
                        @endif
                        <div class="flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('diary.chat') }}" class="btn-primary text-center flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                                今日の内省を始める
                            </a>
                            <a href="{{ route('diary') }}" class="btn-secondary text-center">
                                日記カレンダー
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

                <!-- マイルストーン進捗 -->
                @if(!empty($milestoneProgress))
                    <div class="card-refined surface-blue p-8 soft-shadow-refined">
                        <h2 class="heading-3 text-xl mb-6">マイルストーン進捗</h2>
                        <div class="space-y-4">
                            @foreach($milestoneProgress as $progress)
                                <div class="bg-white rounded-xl p-6 border-2 border-[#6BB6FF]">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1">
                                            <h3 class="body-text font-semibold text-[#2E5C8A] mb-1">{{ $progress['title'] }}</h3>
                                            @if($progress['target_date'])
                                                <p class="body-small text-[#1E3A5F]/60">
                                                    目標日: {{ \Carbon\Carbon::parse($progress['target_date'])->format('Y年m月d日') }}
                                                </p>
                                            @endif
                                        </div>
                                        <span class="heading-3 text-[#6BB6FF]">{{ $progress['completion_rate'] }}%</span>
                                    </div>
                                    <div class="w-full bg-[#E8F4FF] rounded-full h-3 overflow-hidden mb-2">
                                        <div 
                                            class="h-3 bg-[#6BB6FF] transition-all duration-500"
                                            style="width: {{ $progress['completion_rate'] }}%"
                                        ></div>
                                    </div>
                                    <p class="body-small text-[#1E3A5F]/60">
                                        完了: {{ $progress['completed_actions'] }}/{{ $progress['total_actions'] }}アクション
                                    </p>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-6">
                            <a href="{{ route('career.milestones') }}" class="btn-secondary w-full text-center">
                                詳細を見る
                            </a>
                        </div>
                    </div>
                @endif


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
                            <a href="https://b-book.run/@careerpartner.co-14aa198d820a08a2" target="_blank" rel="noopener noreferrer" class="btn-primary w-full text-center">
                                面談を申し込む
                            </a>
                        </div>
                    </div>

                    <!-- LINE登録 -->
                    <div class="card-refined soft-shadow-refined-hover overflow-hidden">
                        <div class="p-8">
                            <div class="flex items-start gap-4 mb-6">
                                <div class="w-14 h-14 rounded-2xl bg-[#06C755]/10 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-7 h-7 text-[#06C755]" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.281.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.086.766.062 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="heading-3 text-xl mb-1">LINE登録はこちら</h3>
                                    <p class="body-small">公式LINEでお気軽にご相談</p>
                                </div>
                            </div>
                            <p class="body-text mb-6">
                                LINEで気軽に相談。各種独自診断もご用意しています。
                            </p>
                            <a href="https://line.me/R/ti/p/@824flemq?ts=08191453&oat_content=url" target="_blank" rel="noopener noreferrer" class="btn-primary w-full text-center">
                                LINEで登録する
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 過去の自分を思い出す（Phase 8.1） -->
                @if($pastRecords['has_past_records'] && !empty($pastRecords['past_items']))
                <div class="card-refined surface-blue p-10 soft-shadow-refined">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="text-3xl">💭</span>
                        <h2 class="heading-2 text-2xl">過去の自分を思い出す</h2>
                    </div>
                    <p class="body-text text-[#1E3A5F]/70 mb-8">
                        過去の記録を振り返ることで、自分の変容を実感できます。
                    </p>

                    {{-- スライド形式のカルーセル --}}
                    <div 
                        x-data="{
                            currentIndex: 0,
                            items: @js($pastRecords['past_items']),
                            autoSlideInterval: null,
                            isPaused: false,
                            init() {
                                this.startAutoSlide();
                            },
                            startAutoSlide() {
                                if (this.items.length <= 1) return;
                                this.autoSlideInterval = setInterval(() => {
                                    if (!this.isPaused) {
                                        this.next();
                                    }
                                }, 5000);
                            },
                            stopAutoSlide() {
                                if (this.autoSlideInterval) {
                                    clearInterval(this.autoSlideInterval);
                                    this.autoSlideInterval = null;
                                }
                            },
                            next() {
                                this.currentIndex = (this.currentIndex + 1) % this.items.length;
                            },
                            prev() {
                                this.currentIndex = (this.currentIndex - 1 + this.items.length) % this.items.length;
                            },
                            goTo(index) {
                                this.currentIndex = index;
                            },
                            pause() {
                                this.isPaused = true;
                            },
                            resume() {
                                this.isPaused = false;
                            }
                        }"
                        @mouseenter="pause()"
                        @mouseleave="resume()"
                        class="relative"
                    >
                        {{-- スライドコンテナ --}}
                        <div class="relative overflow-hidden rounded-xl">
                            <div class="flex transition-transform duration-500 ease-in-out" :style="`transform: translateX(-${currentIndex * 100}%)`">
                                @foreach($pastRecords['past_items'] as $index => $item)
                                <div class="w-full flex-shrink-0 min-w-0">
                                    <div class="bg-white/50 rounded-xl p-4 md:p-6 border border-[#6BB6FF]/20">
                                        @if($item['type'] === 'diagnosis')
                                            {{-- 診断スライド --}}
                                            <div class="flex items-center gap-2 mb-4">
                                                <svg class="w-5 h-5 text-[#6BB6FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                </svg>
                                                <h3 class="heading-3 text-lg">過去の診断</h3>
                                            </div>
                                            <a 
                                                href="{{ route('diagnosis.result', $item['data']['id']) }}" 
                                                class="block p-4 bg-white rounded-lg hover:bg-[#E8F4FF] transition-colors"
                                            >
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="body-small text-[#1E3A5F]/70">{{ $item['data']['date'] }}</span>
                                                </div>
                                                <div class="flex gap-4">
                                                    <span class="body-text text-[#1E3A5F] font-semibold">仕事: {{ $item['data']['work_score'] }}点</span>
                                                    <span class="body-text text-[#1E3A5F] font-semibold">生活: {{ $item['data']['life_score'] }}点</span>
                                                </div>
                                            </a>
                                            <a href="{{ route('diagnosis.start') }}" class="btn-secondary text-sm w-full text-center mt-4">
                                                新しい診断を実施
                                            </a>
                                        @elseif($item['type'] === 'diary')
                                            {{-- 日記スライド --}}
                                            <div class="flex items-center gap-2 mb-4">
                                                <svg class="w-5 h-5 text-[#6BB6FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <h3 class="heading-3 text-lg">過去の日記</h3>
                                            </div>
                                            <a 
                                                href="{{ route('diary') }}?date={{ $item['data']['date_key'] }}" 
                                                class="block p-4 bg-white rounded-lg hover:bg-[#E8F4FF] transition-colors"
                                            >
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="body-small text-[#1E3A5F]/70">{{ $item['data']['date'] }}</span>
                                                    @if($item['data']['motivation'])
                                                    <span class="body-small text-[#6BB6FF]">モチベーション: {{ $item['data']['motivation'] }}</span>
                                                    @endif
                                                </div>
                                                <p class="body-text text-[#1E3A5F]/80">{{ $item['data']['content_preview'] }}</p>
                                            </a>
                                            <a href="{{ route('diary') }}" class="btn-secondary text-sm w-full text-center mt-4">
                                                日記カレンダーを見る
                                            </a>
                                        @elseif($item['type'] === 'strengths_report')
                                            {{-- 持ち味レポスライド --}}
                                            <div class="flex items-center gap-2 mb-4">
                                                <svg class="w-5 h-5 text-[#6BB6FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                                </svg>
                                                <h3 class="heading-3 text-lg">持ち味レポ</h3>
                                            </div>
                                            <p class="body-text text-[#1E3A5F]/70 mb-4">
                                                {{ $item['data']['description'] }}
                                            </p>
                                            <a href="{{ route('onboarding.mini-manual') }}" class="btn-primary text-sm w-full text-center">
                                                持ち味レポを見る
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- ナビゲーションボタン --}}
                        @if(count($pastRecords['past_items']) > 1)
                        <div class="flex items-center justify-between mt-6 px-2">
                            {{-- 前のボタン --}}
                            <button 
                                @click="prev(); pause(); setTimeout(() => resume(), 3000)"
                                class="flex items-center justify-center w-10 h-10 md:w-12 md:h-12 rounded-full bg-[#6BB6FF]/20 hover:bg-[#6BB6FF]/30 active:bg-[#6BB6FF]/40 text-[#6BB6FF] transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                                :disabled="currentIndex === 0"
                            >
                                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            {{-- インジケーター（ドット） --}}
                            <div class="flex gap-2 flex-1 justify-center max-w-xs overflow-x-auto">
                                @foreach($pastRecords['past_items'] as $index => $item)
                                <button 
                                    @click="goTo({{ $index }}); pause(); setTimeout(() => resume(), 3000)"
                                    class="w-2 h-2 rounded-full transition-all duration-300 flex-shrink-0"
                                    :class="currentIndex === {{ $index }} ? 'bg-[#6BB6FF] w-6' : 'bg-[#6BB6FF]/30 hover:bg-[#6BB6FF]/50'"
                                    :aria-label="'スライド {{ $index + 1 }}'"
                                ></button>
                                @endforeach
                            </div>

                            {{-- 次のボタン --}}
                            <button 
                                @click="next(); pause(); setTimeout(() => resume(), 3000)"
                                class="flex items-center justify-center w-10 h-10 md:w-12 md:h-12 rounded-full bg-[#6BB6FF]/20 hover:bg-[#6BB6FF]/30 active:bg-[#6BB6FF]/40 text-[#6BB6FF] transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                                :disabled="currentIndex === {{ count($pastRecords['past_items']) - 1 }}"
                            >
                                <svg class="w-5 h-5 md:w-6 md:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>
