<x-admin.layouts.app title="ユーザー編集: {{ $user->name }}">
    <div class="min-h-screen bg-gradient-to-b from-[#E9F2FF] to-[#F6FBFF]">
        <div class="w-full max-w-3xl mx-auto content-padding section-spacing-sm space-y-8">
            <div class="flex items-center justify-between">
                <h1 class="heading-1">ユーザー編集</h1>
                <a href="{{ route('admin.users.show', $user) }}" class="btn-secondary">キャンセル</a>
            </div>

            <div class="card-refined surface-blue p-6">
                <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-[#2E5C8A] mb-2">名前</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" 
                               required class="w-full px-4 py-2 border border-[#2E5C8A]/30 rounded-lg">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#2E5C8A] mb-2">メールアドレス</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" 
                               required class="w-full px-4 py-2 border border-[#2E5C8A]/30 rounded-lg">
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }} 
                                   class="w-4 h-4 text-[#6BB6FF] border-[#2E5C8A]/30 rounded">
                            <span class="text-sm font-medium text-[#2E5C8A]">管理者</span>
                        </label>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="btn-primary">更新</button>
                        <a href="{{ route('admin.users.show', $user) }}" class="btn-secondary">キャンセル</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin.layouts.app>

