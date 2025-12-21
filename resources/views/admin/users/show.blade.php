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
                        <div>
                            <h3 class="heading-3 text-lg mb-3">診断 ({{ $diagnoses->count() }})</h3>
                            <div class="space-y-2">
                                @forelse($diagnoses as $diagnosis)
                                    <p class="body-text">{{ $diagnosis->created_at->format('Y-m-d') }}</p>
                                @empty
                                    <p class="body-text text-[#1E3A5F]/70">診断がありません</p>
                                @endforelse
                            </div>
                        </div>
                        <div>
                            <h3 class="heading-3 text-lg mb-3">日記 ({{ $diaries->count() }})</h3>
                            <div class="space-y-2">
                                @forelse($diaries->take(10) as $diary)
                                    <p class="body-text">{{ $diary->date->format('Y-m-d') }}</p>
                                @empty
                                    <p class="body-text text-[#1E3A5F]/70">日記がありません</p>
                                @endforelse
                            </div>
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






