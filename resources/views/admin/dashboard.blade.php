<x-admin.layouts.app title="管理ダッシュボード">
    <div class="min-h-screen bg-gradient-to-b from-[#E9F2FF] to-[#F6FBFF]">
        <div class="w-full max-w-7xl mx-auto content-padding section-spacing-sm space-y-8">
            <div>
                <h1 class="heading-1">管理ダッシュボード</h1>
            </div>

            <!-- Active User Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="card-refined surface-blue p-6">
                    <h3 class="heading-3 text-lg mb-2">日次アクティブユーザー (DAU)</h3>
                    <p class="text-3xl font-bold text-[#2E5C8A]">{{ $activeUserStats['dau'] }}</p>
                </div>
                <div class="card-refined surface-blue p-6">
                    <h3 class="heading-3 text-lg mb-2">週次アクティブユーザー (WAU)</h3>
                    <p class="text-3xl font-bold text-[#2E5C8A]">{{ $activeUserStats['wau'] }}</p>
                </div>
                <div class="card-refined surface-blue p-6">
                    <h3 class="heading-3 text-lg mb-2">月次アクティブユーザー (MAU)</h3>
                    <p class="text-3xl font-bold text-[#2E5C8A]">{{ $activeUserStats['mau'] }}</p>
                </div>
            </div>

            <!-- New User Registrations -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="card-refined surface-blue p-6">
                    <h3 class="heading-3 text-lg mb-2">今日の新規登録</h3>
                    <p class="text-3xl font-bold text-[#2E5C8A]">{{ $newUsersToday }}</p>
                </div>
                <div class="card-refined surface-blue p-6">
                    <h3 class="heading-3 text-lg mb-2">今週の新規登録</h3>
                    <p class="text-3xl font-bold text-[#2E5C8A]">{{ $newUsersThisWeek }}</p>
                </div>
                <div class="card-refined surface-blue p-6">
                    <h3 class="heading-3 text-lg mb-2">今月の新規登録</h3>
                    <p class="text-3xl font-bold text-[#2E5C8A]">{{ $newUsersThisMonth }}</p>
                </div>
            </div>

            <!-- Main Operations Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="card-refined surface-blue p-6">
                    <h3 class="heading-3 text-lg mb-4">診断完了数</h3>
                    <div class="space-y-2">
                        <p class="body-text">今日: <span class="font-semibold">{{ $diagnosisCompletedToday }}</span></p>
                        <p class="body-text">今週: <span class="font-semibold">{{ $diagnosisCompletedThisWeek }}</span></p>
                    </div>
                </div>
                <div class="card-refined surface-blue p-6">
                    <h3 class="heading-3 text-lg mb-4">日記作成数</h3>
                    <div class="space-y-2">
                        <p class="body-text">今日: <span class="font-semibold">{{ $diaryCreatedToday }}</span></p>
                        <p class="body-text">今週: <span class="font-semibold">{{ $diaryCreatedThisWeek }}</span></p>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="card-refined surface-blue p-6">
                <h2 class="heading-2 mb-4">最近のアクティビティ</h2>
                <div class="space-y-3">
                    @forelse($recentActivities as $activity)
                        <div class="border-b border-[#2E5C8A]/20 pb-3 last:border-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="body-text">
                                        <span class="font-semibold">{{ $activity->user->name ?? 'Unknown' }}</span>
                                        <span class="text-[#1E3A5F]/70">が</span>
                                        <span class="font-semibold">{{ $activity->action }}</span>
                                        <span class="text-[#1E3A5F]/70">を実行</span>
                                    </p>
                                    <p class="body-small mt-1">{{ $activity->created_at->format('Y-m-d H:i:s') }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="body-text text-[#1E3A5F]/70">アクティビティがありません</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-admin.layouts.app>
