<x-admin.layouts.app title="アクティビティログ">
    <div class="min-h-screen bg-gradient-to-b from-[#E9F2FF] to-[#F6FBFF]">
        <div class="w-full max-w-7xl mx-auto content-padding section-spacing-sm space-y-8">
            <div class="flex items-center justify-between">
                <h1 class="heading-1">アクティビティログ</h1>
                <a href="{{ route('admin.activity-logs.export', request()->query()) }}" class="btn-primary">CSVエクスポート</a>
            </div>

            <!-- Filters -->
            <div class="card-refined surface-blue p-6">
                <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[#2E5C8A] mb-2">ユーザー</label>
                            <select name="user_id" class="w-full px-4 py-2 border border-[#2E5C8A]/30 rounded-lg">
                                <option value="">すべて</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#2E5C8A] mb-2">アクション</label>
                            <select name="action" class="w-full px-4 py-2 border border-[#2E5C8A]/30 rounded-lg">
                                <option value="">すべて</option>
                                @foreach($actions as $action)
                                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                        {{ $action }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#2E5C8A] mb-2">開始日</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                                   class="w-full px-4 py-2 border border-[#2E5C8A]/30 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#2E5C8A] mb-2">終了日</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                                   class="w-full px-4 py-2 border border-[#2E5C8A]/30 rounded-lg">
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="btn-primary">検索</button>
                    </div>
                </form>
            </div>

            <!-- Activity Logs Table -->
            <div class="card-refined surface-blue p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-[#2E5C8A]/20">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-[#2E5C8A]">日時</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-[#2E5C8A]">ユーザー</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-[#2E5C8A]">アクション</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-[#2E5C8A]">IPアドレス</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activityLogs as $log)
                                <tr class="border-b border-[#2E5C8A]/10 hover:bg-[#F0F7FF]">
                                    <td class="py-3 px-4">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td class="py-3 px-4">{{ $log->user->name ?? 'Unknown' }}</td>
                                    <td class="py-3 px-4">{{ $log->action }}</td>
                                    <td class="py-3 px-4">{{ $log->ip_address ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-[#1E3A5F]/70">アクティビティログが見つかりません</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $activityLogs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-admin.layouts.app>


