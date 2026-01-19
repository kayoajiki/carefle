<x-admin.layouts.app title="ユーザー詳細: {{ $user->name }}">
    <div class="min-h-screen bg-gradient-to-b from-[#E9F2FF] to-[#F6FBFF]">
        <div class="w-full max-w-7xl mx-auto content-padding section-spacing-sm space-y-8">
            <div class="flex items-center justify-between">
                <h1 class="heading-1">ユーザー詳細</h1>
                <div class="flex gap-3">
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn-secondary">編集</a>
                    <a href="{{ route('admin.users.index') }}" class="btn-secondary">一覧に戻る</a>
                </div>
            </div>

            <!-- User Info Tabs -->
            <div class="card-refined surface-blue p-6">
                <div class="border-b border-[#2E5C8A]/20 mb-6">
                    <nav class="flex space-x-4">
                        <button onclick="showTab('basic')" class="tab-button active py-2 px-4 border-b-2 border-[#6BB6FF]">基本情報</button>
                        <button onclick="showTab('login')" class="tab-button py-2 px-4">ログイン履歴</button>
                        <button onclick="showTab('activity')" class="tab-button py-2 px-4">アクティビティログ</button>
                        <button onclick="showTab('data')" class="tab-button py-2 px-4">作成データ</button>
                        <button onclick="showTab('onboarding')" class="tab-button py-2 px-4">オンボーディング進捗</button>
                    </nav>
                </div>

                <!-- Basic Info Tab -->
                <div id="tab-basic" class="tab-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-[#2E5C8A] mb-1">名前</label>
                            <p class="body-text">{{ $user->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#2E5C8A] mb-1">メールアドレス</label>
                            <p class="body-text">{{ $user->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#2E5C8A] mb-1">登録日</label>
                            <p class="body-text">{{ $user->created_at->format('Y-m-d H:i:s') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#2E5C8A] mb-1">最終ログイン</label>
                            <p class="body-text">{{ $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#2E5C8A] mb-1">最終アクティビティ</label>
                            <p class="body-text">{{ $user->last_activity_at ? $user->last_activity_at->format('Y-m-d H:i:s') : '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#2E5C8A] mb-1">管理者</label>
                            <p class="body-text">{{ $user->is_admin ? 'はい' : 'いいえ' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Login History Tab -->
                <div id="tab-login" class="tab-content hidden">
                    <div class="space-y-3">
                        @forelse($loginHistory as $login)
                            <div class="border-b border-[#2E5C8A]/20 pb-3 last:border-0">
                                <p class="body-text">{{ $login->created_at->format('Y-m-d H:i:s') }}</p>
                                <p class="body-small text-[#1E3A5F]/70">IP: {{ $login->ip_address ?? '-' }}</p>
                            </div>
                        @empty
                            <p class="body-text text-[#1E3A5F]/70">ログイン履歴がありません</p>
                        @endforelse
                    </div>
                </div>

                <!-- Activity Logs Tab -->
                <div id="tab-activity" class="tab-content hidden">
                    <div class="space-y-3">
                        @forelse($activityLogs as $log)
                            <div class="border-b border-[#2E5C8A]/20 pb-3 last:border-0">
                                <p class="body-text">
                                    <span class="font-semibold">{{ $log->action }}</span>
                                    <span class="text-[#1E3A5F]/70"> - {{ $log->created_at->format('Y-m-d H:i:s') }}</span>
                                </p>
                            </div>
                        @empty
                            <p class="body-text text-[#1E3A5F]/70">アクティビティログがありません</p>
                        @endforelse
                    </div>
                </div>

                <!-- Created Data Tab -->
                <div id="tab-data" class="tab-content hidden">
                    <div class="space-y-6">
                        <!-- WCMシート -->
                        <div>
                            <h3 class="heading-3 text-lg mb-3">WCMシート (共有中: {{ $wcmSheets->count() }}件)</h3>
                            <div class="space-y-3">
                                @forelse($wcmSheets as $sheet)
                                    <div class="bg-white rounded-lg p-4 border border-[#2E5C8A]/20">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-1">
                                                    <p class="font-semibold text-[#2E5C8A]">{{ $sheet->title }}</p>
                                                    <span class="text-xs px-2 py-0.5 rounded bg-[#F0F7FF] border border-[#2E5C8A]/20 text-[#2E5C8A] font-medium">
                                                        v{{ $sheet->version }}
                                                    </span>
                                                </div>
                                                <p class="body-small text-[#1E3A5F]/70">作成日: {{ $sheet->created_at->format('Y年n月j日') }}</p>
                                                @if($sheet->will_text)
                                                <p class="body-small text-[#1E3A5F] mt-2">Will: {{ mb_substr($sheet->will_text, 0, 50) }}...</p>
                                                @endif
                                            </div>
                                            <a href="{{ route('admin.users.view-wcm', ['user' => $user->id, 'id' => $sheet->id]) }}" class="btn-secondary text-xs ml-4">
                                                詳細を見る
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <p class="body-text text-[#1E3A5F]/70">共有されたWCMシートがありません</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- 人生史 -->
                        <div>
                            <h3 class="heading-3 text-lg mb-3">人生史</h3>
                            @if($user->life_history_is_admin_visible)
                                <div class="bg-white rounded-lg p-4 border border-[#2E5C8A]/20">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <p class="font-semibold text-[#2E5C8A] mb-1">人生史一覧 ({{ $lifeEvents->count() }}件)</p>
                                            <p class="body-small text-[#1E3A5F]/70">全体共有が有効です</p>
                                        </div>
                                        <a href="{{ route('admin.users.view-life-history', ['user' => $user->id]) }}" class="btn-secondary text-xs ml-4">
                                            詳細を見る
                                        </a>
                                    </div>
                                </div>
                            @else
                                <p class="body-text text-[#1E3A5F]/70">共有された人生史がありません</p>
                            @endif
                        </div>

                        <!-- 現職満足度診断結果 -->
                        <div>
                            <h3 class="heading-3 text-lg mb-3">現職満足度診断結果 (共有中: {{ $diagnoses->count() }}件)</h3>
                            <div class="space-y-3">
                                @forelse($diagnoses as $diagnosis)
                                    <div class="bg-white rounded-lg p-4 border border-[#2E5C8A]/20">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <p class="font-semibold text-[#2E5C8A] mb-1">満足度: {{ $diagnosis->work_score ?? 0 }}点 / 重要度: {{ $diagnosis->life_score ?? 0 }}点</p>
                                                <p class="body-small text-[#1E3A5F]/70">診断ID: #{{ str_pad($diagnosis->id, 4, '0', STR_PAD_LEFT) }} | 診断日: {{ $diagnosis->created_at->format('Y年n月j日') }}</p>
                                            </div>
                                            <a href="{{ route('admin.users.view-diagnosis', ['user' => $user->id, 'id' => $diagnosis->id]) }}" class="btn-secondary text-xs ml-4">
                                                詳細を見る
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <p class="body-text text-[#1E3A5F]/70">共有された職業満足度診断結果がありません</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- 職業満足度診断結果 -->
                        <div>
                            <h3 class="heading-3 text-lg mb-3">職業満足度診断結果 (共有中: {{ $careerSatisfactionDiagnoses->count() }}件)</h3>
                            <div class="space-y-3">
                                @forelse($careerSatisfactionDiagnoses as $diagnosis)
                                    <div class="bg-white rounded-lg p-4 border border-[#2E5C8A]/20">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <p class="font-semibold text-[#2E5C8A] mb-1">満足度: {{ $diagnosis->work_score ?? 0 }}点</p>
                                                <p class="body-small text-[#1E3A5F]/70">診断日: {{ $diagnosis->created_at->format('Y年n月j日') }}</p>
                                            </div>
                                            <a href="{{ route('admin.users.view-career-satisfaction', ['user' => $user->id, 'id' => $diagnosis->id]) }}" class="btn-secondary text-xs ml-4">
                                                詳細を見る
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <p class="body-text text-[#1E3A5F]/70">共有された職業満足度診断結果がありません</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- 持ち味診断 -->
                        <div>
                            <h3 class="heading-3 text-lg mb-3">持ち味診断 (共有中: {{ $strengthsReports->count() }}件)</h3>
                            <div class="space-y-3">
                                @forelse($strengthsReports as $report)
                                    <div class="bg-white rounded-lg p-4 border border-[#2E5C8A]/20">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <p class="font-semibold text-[#2E5C8A] mb-1">{{ $report->content['title'] ?? '私の持ち味レポ' }}</p>
                                                <p class="body-small text-[#1E3A5F]/70">生成日: {{ $report->generated_at->format('Y年n月j日') }}</p>
                                            </div>
                                            <a href="{{ route('admin.users.view-strengths-report', ['user' => $user->id, 'id' => $report->id]) }}" class="btn-secondary text-xs ml-4">
                                                詳細を見る
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <p class="body-text text-[#1E3A5F]/70">共有された持ち味診断がありません</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- マイゴール -->
                        <div>
                            <h3 class="heading-3 text-lg mb-3">マイゴール</h3>
                            @if($myGoalShared)
                                <div class="bg-white rounded-lg p-4 border border-[#2E5C8A]/20">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <p class="font-semibold text-[#2E5C8A] mb-2">マイゴール</p>
                                            <p class="body-text text-[#1E3A5F]">{{ mb_substr($user->goal_image, 0, 100) }}...</p>
                                        </div>
                                        <a href="{{ route('admin.users.view-my-goal', ['user' => $user->id]) }}" class="btn-secondary text-xs ml-4">
                                            詳細を見る
                                        </a>
                                    </div>
                                </div>
                            @else
                                <p class="body-text text-[#1E3A5F]/70">共有されたマイゴールがありません</p>
                            @endif
                        </div>

                        <!-- マイルストーン -->
                        <div>
                            <h3 class="heading-3 text-lg mb-3">マイルストーン (共有中: {{ $milestones->count() }}件)</h3>
                            <div class="space-y-3">
                                @forelse($milestones as $milestone)
                                    <div class="bg-white rounded-lg p-4 border border-[#2E5C8A]/20">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <p class="font-semibold text-[#2E5C8A] mb-1">{{ $milestone->title }}</p>
                                                <p class="body-small text-[#1E3A5F]/70">目標年: {{ $milestone->target_year }}年</p>
                                                @if($milestone->description)
                                                <p class="body-small text-[#1E3A5F] mt-2">{{ mb_substr($milestone->description, 0, 100) }}...</p>
                                                @endif
                                            </div>
                                            <a href="{{ route('admin.users.view-milestone', ['user' => $user->id, 'id' => $milestone->id]) }}" class="btn-secondary text-xs ml-4">
                                                詳細を見る
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <p class="body-text text-[#1E3A5F]/70">共有されたマイルストーンがありません</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- 自己診断結果 -->
                        <div>
                            <h3 class="heading-3 text-lg mb-3">自己診断結果 (共有中: {{ $assessments->count() }}件)</h3>
                            <div class="space-y-3">
                                @forelse($assessments as $assessment)
                                    <div class="bg-white rounded-lg p-4 border border-[#2E5C8A]/20">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <p class="font-semibold text-[#2E5C8A] mb-1">{{ $assessment->assessment_name ?? strtoupper($assessment->assessment_type) }}</p>
                                                <p class="body-small text-[#1E3A5F]/70">記録日: {{ $assessment->completed_at ? $assessment->completed_at->format('Y年n月j日') : $assessment->created_at->format('Y年n月j日') }}</p>
                                            </div>
                                            <a href="{{ route('admin.users.view-personality-assessment', ['user' => $user->id, 'id' => $assessment->id]) }}" class="btn-secondary text-xs ml-4">
                                                詳細を見る
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <p class="body-text text-[#1E3A5F]/70">共有された自己診断結果がありません</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- 日記（共有機能なし） -->
                        <div>
                            <h3 class="heading-3 text-lg mb-3">日記 ({{ $diaries->count() }})</h3>
                            <p class="body-text text-[#1E3A5F]/70">日記は共有機能の対象外です</p>
                        </div>
                    </div>
                </div>

                <!-- Onboarding Progress Tab -->
                <div id="tab-onboarding" class="tab-content hidden">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-[#2E5C8A] mb-1">現在のステップ</label>
                            <p class="body-text">{{ $onboardingProgress->current_step ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#2E5C8A] mb-1">完了ステップ</label>
                            <p class="body-text">{{ implode(', ', $onboardingProgress->completed_steps ?? []) ?: '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active', 'border-[#6BB6FF]'));
            
            // Show selected tab
            document.getElementById('tab-' + tabName).classList.remove('hidden');
            event.target.classList.add('active', 'border-[#6BB6FF]');
        }
    </script>
</x-admin.layouts.app>











