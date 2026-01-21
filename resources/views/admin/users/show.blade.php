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
                        <button onclick="showTab('career-hug')" class="tab-button py-2 px-4">キャリハグ</button>
                    </nav>
                </div>

                <!-- Basic Info Tab -->
                <div id="tab-basic" class="tab-content">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-[#2E5C8A] mb-1">ユーザーID</label>
                            <p class="body-text font-mono">#{{ $user->id }}</p>
                        </div>
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

                <!-- Career Hug Tab -->
                <div id="tab-career-hug" class="tab-content hidden" x-data="careerHugForm()" x-init="init()">
                    <form method="POST" action="{{ route('admin.users.career-hug.update', $user) }}" @submit.prevent="saveCareerHug">
                        @csrf
                        <div class="space-y-8">
                            <!-- ① 基本情報 -->
                            <div class="border-b border-[#2E5C8A]/20 pb-6">
                                <h3 class="heading-3 text-lg mb-4">① 基本情報</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-[#2E5C8A] mb-1">表示名</label>
                                        <p class="body-text">{{ $user->name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-[#2E5C8A] mb-1">利用形態</label>
                                        <div class="flex gap-4">
                                            <label class="flex items-center">
                                                <input type="radio" name="usage_type" value="paid" x-model="formData.usage_type" class="mr-2">
                                                <span class="body-text">有償</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" name="usage_type" value="free" x-model="formData.usage_type" class="mr-2">
                                                <span class="body-text">無償（モニター）</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-[#2E5C8A] mb-1">担当者</label>
                                        <select name="assigned_admin_id" x-model="formData.assigned_admin_id" class="w-full px-3 py-2 border border-[#2E5C8A]/20 rounded-lg">
                                            <option value="">選択してください</option>
                                            @foreach($adminUsers as $admin)
                                                <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- ② キャリハグの位置づけ -->
                            <div class="border-b border-[#2E5C8A]/20 pb-6">
                                <h3 class="heading-3 text-lg mb-4">② キャリハグの位置づけ</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-[#2E5C8A] mb-1">キャリハグ開始日</label>
                                        <input type="date" name="start_date" x-model="formData.start_date" class="w-full px-3 py-2 border border-[#2E5C8A]/20 rounded-lg">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-[#2E5C8A] mb-1">現在のレベル</label>
                                        <select name="current_level" x-model="formData.current_level" class="w-full px-3 py-2 border border-[#2E5C8A]/20 rounded-lg">
                                            <option value="">選択してください</option>
                                            <option value="level1">レベル1（判断整理）</option>
                                            <option value="level2">レベル2（行動設計）</option>
                                            <option value="level3">レベル3（伴走）</option>
                                        </select>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="block text-sm font-medium text-[#2E5C8A] mb-2">レベル日付履歴</label>
                                        <div class="space-y-3">
                                            <template x-for="(levelDate, index) in levelDates" :key="levelDate.id || index">
                                                <div class="flex items-center gap-3 bg-white p-3 rounded-lg border border-[#2E5C8A]/20">
                                                    <select x-model="levelDate.level" class="px-3 py-2 border border-[#2E5C8A]/20 rounded-lg">
                                                        <option value="level1">レベル1</option>
                                                        <option value="level2">レベル2</option>
                                                        <option value="level3">レベル3</option>
                                                    </select>
                                                    <input type="date" x-model="levelDate.date" class="px-3 py-2 border border-[#2E5C8A]/20 rounded-lg">
                                                    <button type="button" @click="removeLevelDate(index)" class="btn-secondary text-xs">削除</button>
                                                </div>
                                            </template>
                                            <button type="button" @click="addLevelDate" class="btn-secondary text-sm">日付を追加</button>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-[#2E5C8A] mb-1">キャリハグの主目的</label>
                                        <div class="space-y-2">
                                            <label class="flex items-center">
                                                <input type="radio" name="main_purpose" value="judgment_organization" x-model="formData.main_purpose" class="mr-2">
                                                <span class="body-text">判断整理</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" name="main_purpose" value="action_design" x-model="formData.main_purpose" class="mr-2">
                                                <span class="body-text">行動設計</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" name="main_purpose" value="continuation_adjustment" x-model="formData.main_purpose" class="mr-2">
                                                <span class="body-text">継続・調整</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-[#2E5C8A] mb-1">入口トリガー</label>
                                        <input type="text" name="entry_trigger" x-model="formData.entry_trigger" placeholder="例：キャリフレ、友人紹介、ネット" class="w-full px-3 py-2 border border-[#2E5C8A]/20 rounded-lg">
                                    </div>
                                </div>
                            </div>

                            <!-- ④ セッション設計 -->
                            <div class="border-b border-[#2E5C8A]/20 pb-6">
                                <h3 class="heading-3 text-lg mb-4">④ セッション設計</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-[#2E5C8A] mb-2">使用している武器（複数可）</label>
                                        <div class="space-y-2">
                                            <label class="flex items-center">
                                                <input type="checkbox" name="weapons[]" value="career_satisfaction_diagnosis" x-model="formData.weapons" class="mr-2">
                                                <span class="body-text">職業満足度診断</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="weapons[]" value="wcm" x-model="formData.weapons" class="mr-2">
                                                <span class="body-text">WCM</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="weapons[]" value="life_history" x-model="formData.weapons" class="mr-2">
                                                <span class="body-text">人生史</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="checkbox" name="weapons[]" value="judgment_organization_frame" x-model="formData.weapons" class="mr-2">
                                                <span class="body-text">判断整理フレーム</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-[#2E5C8A] mb-1">現在の思考フェーズ</label>
                                        <div class="space-y-2">
                                            <label class="flex items-center">
                                                <input type="radio" name="current_phase" value="state_understanding" x-model="formData.current_phase" class="mr-2">
                                                <span class="body-text">状態把握中</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" name="current_phase" value="verbalization" x-model="formData.current_phase" class="mr-2">
                                                <span class="body-text">言語化中</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" name="current_phase" value="judgment_organization" x-model="formData.current_phase" class="mr-2">
                                                <span class="body-text">判断整理中</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" name="current_phase" value="action" x-model="formData.current_phase" class="mr-2">
                                                <span class="body-text">行動中</span>
                                            </label>
                                            <label class="flex items-center">
                                                <input type="radio" name="current_phase" value="continuation_adjustment" x-model="formData.current_phase" class="mr-2">
                                                <span class="body-text">継続調整中</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ⑤ キャリハグ利用ステータス -->
                            <div class="border-b border-[#2E5C8A]/20 pb-6">
                                <h3 class="heading-3 text-lg mb-4">⑤ キャリハグ利用ステータス</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-[#2E5C8A] mb-1">ステータス</label>
                                        <select name="status" x-model="formData.status" class="w-full px-3 py-2 border border-[#2E5C8A]/20 rounded-lg">
                                            <option value="not_started">未開始</option>
                                            <option value="in_use">利用中</option>
                                            <option value="paused">一時停止</option>
                                            <option value="completed">完了（卒業）</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-[#2E5C8A] mb-1">最終セッション日</label>
                                        <input type="date" name="last_session_date" x-model="formData.last_session_date" class="w-full px-3 py-2 border border-[#2E5C8A]/20 rounded-lg">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-[#2E5C8A] mb-1">次回セッション予定日</label>
                                        <input type="date" name="next_session_date" x-model="formData.next_session_date" class="w-full px-3 py-2 border border-[#2E5C8A]/20 rounded-lg">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-[#2E5C8A] mb-1">優先度（運用用）</label>
                                        <select name="priority" x-model="formData.priority" class="w-full px-3 py-2 border border-[#2E5C8A]/20 rounded-lg">
                                            <option value="">選択してください</option>
                                            <option value="high">高：判断局面／期限あり</option>
                                            <option value="medium">中：通常</option>
                                            <option value="low">低：様子見</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- ⑥ 接点ログ -->
                            <div class="border-b border-[#2E5C8A]/20 pb-6">
                                <h3 class="heading-3 text-lg mb-4">⑥ 接点ログ</h3>
                                <div class="space-y-4">
                                    <template x-for="(log, index) in contactLogs" :key="log.id || index">
                                        <div class="bg-white p-4 rounded-lg border border-[#2E5C8A]/20">
                                            <div class="flex justify-between items-start mb-3">
                                                <h4 class="font-semibold text-[#2E5C8A]" x-text="'接点ログ #' + (index + 1)"></h4>
                                                <div class="flex gap-2">
                                                    <button type="button" @click="editContactLog(index)" class="btn-secondary text-xs" x-show="!log.editing">編集</button>
                                                    <button type="button" @click="saveContactLog(index)" class="btn-secondary text-xs" x-show="log.editing">保存</button>
                                                    <button type="button" @click="cancelEditContactLog(index)" class="btn-secondary text-xs" x-show="log.editing">キャンセル</button>
                                                    <button type="button" @click="deleteContactLog(index)" class="btn-secondary text-xs" x-show="!log.editing">削除</button>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="!log.editing">
                                                <div>
                                                    <label class="block text-sm font-medium text-[#2E5C8A] mb-1">接点日</label>
                                                    <p class="body-text" x-text="log.contact_date"></p>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-[#2E5C8A] mb-1">接点種別</label>
                                                    <p class="body-text" x-text="getContactTypeLabel(log.contact_type)"></p>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label class="block text-sm font-medium text-[#2E5C8A] mb-1">扱ったテーマ</label>
                                                    <p class="body-text" x-text="log.theme || '-'"></p>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label class="block text-sm font-medium text-[#2E5C8A] mb-1">決まったこと／確認したこと</label>
                                                    <p class="body-text" x-text="log.decided_matters || '-'"></p>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label class="block text-sm font-medium text-[#2E5C8A] mb-1">次の一手</label>
                                                    <p class="body-text" x-text="log.next_action || '-'"></p>
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-show="log.editing">
                                                <div>
                                                    <label class="block text-sm font-medium text-[#2E5C8A] mb-1">接点日</label>
                                                    <input type="date" x-model="log.contact_date" class="w-full px-3 py-2 border border-[#2E5C8A]/20 rounded-lg">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-[#2E5C8A] mb-1">接点種別</label>
                                                    <select x-model="log.contact_type" class="w-full px-3 py-2 border border-[#2E5C8A]/20 rounded-lg">
                                                        <option value="session">セッション</option>
                                                        <option value="chat">チャット</option>
                                                        <option value="follow_up">フォロー連絡</option>
                                                    </select>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label class="block text-sm font-medium text-[#2E5C8A] mb-1">扱ったテーマ</label>
                                                    <textarea x-model="log.theme" rows="2" class="w-full px-3 py-2 border border-[#2E5C8A]/20 rounded-lg"></textarea>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label class="block text-sm font-medium text-[#2E5C8A] mb-1">決まったこと／確認したこと</label>
                                                    <textarea x-model="log.decided_matters" rows="2" class="w-full px-3 py-2 border border-[#2E5C8A]/20 rounded-lg"></textarea>
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label class="block text-sm font-medium text-[#2E5C8A] mb-1">次の一手</label>
                                                    <textarea x-model="log.next_action" rows="2" class="w-full px-3 py-2 border border-[#2E5C8A]/20 rounded-lg"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    <button type="button" @click="addContactLog" class="btn-secondary">接点ログを追加</button>
                                </div>
                            </div>

                            <!-- フリー記述欄 -->
                            <div>
                                <h3 class="heading-3 text-lg mb-4">フリー記述</h3>
                                <div>
                                    <label class="block text-sm font-medium text-[#2E5C8A] mb-1">メモ・備考</label>
                                    <textarea name="notes" x-model="formData.notes" rows="8" placeholder="自由にメモを記入してください" class="w-full px-3 py-2 border border-[#2E5C8A]/20 rounded-lg"></textarea>
                                </div>
                            </div>

                            <!-- 保存ボタン -->
                            <div class="flex justify-end">
                                <button type="submit" class="btn-primary">保存</button>
                            </div>
                        </div>
                    </form>
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

        function careerHugForm() {
            const careerHugData = @json($careerHugData ?? null);
            return {
                formData: {
                    usage_type: careerHugData?.usage_type ?? null,
                    assigned_admin_id: careerHugData?.assigned_admin_id ?? null,
                    start_date: careerHugData?.start_date ?? null,
                    current_level: careerHugData?.current_level ?? null,
                    main_purpose: careerHugData?.main_purpose ?? null,
                    entry_trigger: careerHugData?.entry_trigger ?? null,
                    current_phase: careerHugData?.current_phase ?? null,
                    status: careerHugData?.status ?? 'not_started',
                    last_session_date: careerHugData?.last_session_date ?? null,
                    next_session_date: careerHugData?.next_session_date ?? null,
                    priority: careerHugData?.priority ?? null,
                    weapons: careerHugData?.weapons ?? [],
                    notes: careerHugData?.notes ?? null,
                },
                levelDates: (careerHugData?.levelDates ?? []).map(date => ({ ...date })),
                contactLogs: (careerHugData?.contactLogs ?? []).map(log => ({ ...log, editing: false })),
                getCsrfToken() {
                    return document.querySelector('input[name="_token"]')?.value || 
                           document.querySelector('meta[name="csrf-token"]')?.content || 
                           '';
                },
                init() {
                    // 初期化処理
                },
                addLevelDate() {
                    this.levelDates.push({
                        id: null,
                        level: 'level1',
                        date: '',
                    });
                },
                removeLevelDate(index) {
                    const levelDate = this.levelDates[index];
                    if (levelDate.id) {
                        const url = `{{ url('admin/users/' . $user->id . '/career-hug/level-dates') }}/${levelDate.id}`;
                        fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': this.getCsrfToken(),
                            },
                        }).then(() => {
                            this.levelDates.splice(index, 1);
                        });
                    } else {
                        this.levelDates.splice(index, 1);
                    }
                },
                addContactLog() {
                    this.contactLogs.push({
                        id: null,
                        contact_date: '',
                        contact_type: 'session',
                        theme: '',
                        decided_matters: '',
                        next_action: '',
                        editing: true,
                    });
                },
                editContactLog(index) {
                    this.contactLogs[index].editing = true;
                },
                async saveContactLog(index) {
                    const log = this.contactLogs[index];
                    const url = log.id 
                        ? `{{ url('admin/users/' . $user->id . '/career-hug/contact-logs') }}/${log.id}`
                        : `{{ route('admin.users.career-hug.contact-logs.store', $user->id) }}`;
                    const method = log.id ? 'PUT' : 'POST';
                    
                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.getCsrfToken(),
                        },
                        body: JSON.stringify({
                            contact_date: log.contact_date,
                            contact_type: log.contact_type,
                            theme: log.theme,
                            decided_matters: log.decided_matters,
                            next_action: log.next_action,
                        }),
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        Object.assign(log, data.contactLog);
                        log.editing = false;
                    }
                },
                cancelEditContactLog(index) {
                    const log = this.contactLogs[index];
                    if (log.id) {
                        log.editing = false;
                    } else {
                        this.contactLogs.splice(index, 1);
                    }
                },
                async deleteContactLog(index) {
                    const log = this.contactLogs[index];
                    if (log.id) {
                        const url = `{{ url('admin/users/' . $user->id . '/career-hug/contact-logs') }}/${log.id}`;
                        const response = await fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': this.getCsrfToken(),
                            },
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.contactLogs.splice(index, 1);
                        }
                    } else {
                        this.contactLogs.splice(index, 1);
                    }
                },
                async saveCareerHug(event) {
                    event.preventDefault();
                    const form = event.target;
                    const formData = new FormData(form);
                    
                    // レベル日付を保存
                    for (const levelDate of this.levelDates) {
                        if (!levelDate.id && levelDate.date) {
                            await fetch(`{{ route('admin.users.career-hug.level-dates.store', $user->id) }}`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.getCsrfToken(),
                                },
                                body: JSON.stringify({
                                    level: levelDate.level,
                                    date: levelDate.date,
                                }),
                            });
                        }
                    }
                    
                    // メインフォームを送信
                    form.submit();
                },
                getContactTypeLabel(type) {
                    const labels = {
                        'session': 'セッション',
                        'chat': 'チャット',
                        'follow_up': 'フォロー連絡',
                    };
                    return labels[type] || type;
                },
            };
        }
    </script>
</x-admin.layouts.app>











