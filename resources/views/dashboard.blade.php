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
                <div class="card-refined surface-blue p-10 soft-shadow-refined space-y-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                        <div>
                            <p class="body-small text-[#5BA3D6] uppercase tracking-[0.2em]">Overview</p>
                            <h1 class="heading-2 mb-2 mt-1">ダッシュボード</h1>
                        </div>
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
                        <a href="{{ route('diary.chat') }}" class="btn-primary">
                            内省を始める
                        </a>
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
                            <a href="{{ route('milestones.progress') }}" class="btn-secondary w-full text-center">
                                詳細を見る
                            </a>
                        </div>
                    </div>
                @endif

                <!-- AI伴走の履歴 -->
                @if($recentConversations->isNotEmpty())
                    <div class="card-refined surface-blue p-8 soft-shadow-refined">
                        <h2 class="heading-3 text-xl mb-6">最近の内省チャット</h2>
                        <div class="space-y-3">
                            @foreach($recentConversations as $conversation)
                                <div class="bg-white rounded-lg p-4 border border-[#6BB6FF]/20">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="body-text font-medium text-[#2E5C8A]">
                                                {{ $conversation->date ? \Carbon\Carbon::parse($conversation->date)->format('Y年m月d日') : \Carbon\Carbon::parse($conversation->updated_at)->format('Y年m月d日') }}
                                            </p>
                                            @if($conversation->diary && $conversation->diary->reflection_type)
                                                <p class="body-small text-[#1E3A5F]/60">
                                                    @if($conversation->diary->reflection_type === 'daily')
                                                        今日の振り返り
                                                    @elseif($conversation->diary->reflection_type === 'yesterday')
                                                        昨日の振り返り
                                                    @elseif($conversation->diary->reflection_type === 'weekly')
                                                        週次振り返り
                                                    @elseif($conversation->diary->reflection_type === 'deep')
                                                        深い内省
                                                    @elseif($conversation->diary->reflection_type === 'moya_moya')
                                                        モヤモヤ解消
                                                    @endif
                                                </p>
                                            @endif
                                        </div>
                                        <a href="{{ route('diary.chat', ['date' => $conversation->date ? \Carbon\Carbon::parse($conversation->date)->format('Y-m-d') : now()->format('Y-m-d')]) }}" class="body-small text-[#6BB6FF] hover:underline">
                                            開く →
                                        </a>
                                    </div>
                                </div>
                            @endforeach
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
