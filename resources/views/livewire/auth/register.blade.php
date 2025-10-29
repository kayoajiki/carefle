<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <div class="flex w-full flex-col text-center mb-4">
            <h1 class="text-2xl md:text-3xl font-bold brand-headline mb-2">新規登録</h1>
            <p class="text-sm text-dim leading-relaxed">
                以下の情報を入力してアカウントを作成してください
            </p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center text-sm text-red-600" :status="session('status')" />

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 text-sm p-3 rounded-md">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Name -->
            <div class="flex flex-col gap-2">
                <label for="name" class="text-sm font-medium text-[#00473e]">
                    お名前
                </label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    value="{{ old('name') }}"
                    class="w-full px-4 py-3 rounded-lg border border-[#00473e]/20 bg-white text-[#00473e] focus:outline-none focus:ring-2 focus:ring-[#faae2b] focus:border-transparent"
                    placeholder="山田 太郎"
                />
            </div>

            <!-- Email Address -->
            <div class="flex flex-col gap-2">
                <label for="email" class="text-sm font-medium text-[#00473e]">
                    メールアドレス
                </label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    required
                    autocomplete="email"
                    value="{{ old('email') }}"
                    class="w-full px-4 py-3 rounded-lg border border-[#00473e]/20 bg-white text-[#00473e] focus:outline-none focus:ring-2 focus:ring-[#faae2b] focus:border-transparent"
                    placeholder="email@example.com"
                />
            </div>

            <!-- Password -->
            <div class="flex flex-col gap-2">
                <label for="password" class="text-sm font-medium text-[#00473e]">
                    パスワード
                </label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="w-full px-4 py-3 rounded-lg border border-[#00473e]/20 bg-white text-[#00473e] focus:outline-none focus:ring-2 focus:ring-[#faae2b] focus:border-transparent"
                    placeholder="パスワードを入力"
                />
                <p class="text-xs text-dim">
                    8文字以上で設定してください
                </p>
            </div>

            <!-- Confirm Password -->
            <div class="flex flex-col gap-2">
                <label for="password_confirmation" class="text-sm font-medium text-[#00473e]">
                    パスワード（確認）
                </label>
                <input
                    id="password_confirmation"
                    name="password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    class="w-full px-4 py-3 rounded-lg border border-[#00473e]/20 bg-white text-[#00473e] focus:outline-none focus:ring-2 focus:ring-[#faae2b] focus:border-transparent"
                    placeholder="パスワードを再度入力"
                />
            </div>

            <button
                type="submit"
                class="w-full px-6 py-3 rounded-lg font-semibold text-base accent-bg accent-text shadow-md hover:opacity-90 transition"
            >
                アカウントを作成
            </button>
        </form>

        <div class="text-sm text-center text-dim pt-4 border-t border-[#00473e]/10">
            <span>すでにアカウントをお持ちの方は</span>
            <a href="{{ route('login') }}" class="text-[#00473e] font-semibold underline underline-offset-2 hover:text-[#faae2b] transition-colors" wire:navigate>
                ログイン
            </a>
        </div>
    </div>
</x-layouts.auth>
