<x-admin.layouts.app title="ユーザー管理">
    <div class="min-h-screen bg-gradient-to-b from-[#E9F2FF] to-[#F6FBFF]">
        <div class="w-full max-w-7xl mx-auto content-padding section-spacing-sm space-y-8">
            <div class="flex items-center justify-between">
                <h1 class="heading-1">ユーザー管理</h1>
            </div>

            <!-- Search and Filter -->
            <div class="card-refined surface-blue p-6">
                <form method="GET" action="{{ route('admin.users.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-[#2E5C8A] mb-2">検索</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="名前またはメールアドレス" 
                                   class="w-full px-4 py-2 border border-[#2E5C8A]/30 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#2E5C8A] mb-2">管理者フィルター</label>
                            <select name="is_admin" class="w-full px-4 py-2 border border-[#2E5C8A]/30 rounded-lg">
                                <option value="">すべて</option>
                                <option value="1" {{ request('is_admin') === '1' ? 'selected' : '' }}>管理者のみ</option>
                                <option value="0" {{ request('is_admin') === '0' ? 'selected' : '' }}>一般ユーザーのみ</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="btn-primary w-full">検索</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="card-refined surface-blue p-6">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-[#2E5C8A]/20">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-[#2E5C8A]">名前</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-[#2E5C8A]">メール</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-[#2E5C8A]">登録日</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-[#2E5C8A]">最終ログイン</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-[#2E5C8A]">管理者</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-[#2E5C8A]">アクション</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr class="border-b border-[#2E5C8A]/10 hover:bg-[#F0F7FF]">
                                    <td class="py-3 px-4">{{ $user->name }}</td>
                                    <td class="py-3 px-4">{{ $user->email }}</td>
                                    <td class="py-3 px-4">{{ $user->created_at->format('Y-m-d') }}</td>
                                    <td class="py-3 px-4">{{ $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i') : '-' }}</td>
                                    <td class="py-3 px-4">
                                        @if($user->is_admin)
                                            <span class="badge-pill badge-pill--accent">管理者</span>
                                        @else
                                            <span class="text-[#1E3A5F]/50">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <a href="{{ route('admin.users.show', $user) }}" class="btn-secondary text-sm px-3 py-1">詳細</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-[#1E3A5F]/70">ユーザーが見つかりません</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-admin.layouts.app>
