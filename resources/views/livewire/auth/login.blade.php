<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <div class="flex w-full flex-col text-center mb-4">
            <h1 class="text-2xl md:text-3xl font-bold brand-headline mb-2">ログイン</h1>
            <p class="text-sm text-dim leading-relaxed">
                メールアドレスとパスワードを入力してください
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

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Email Address -->
            <div class="flex flex-col gap-2">
                <label for="email" class="text-sm font-medium text-[#2E5C8A]">
                    メールアドレス
                </label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    required
                    autofocus
                    autocomplete="email"
                    value="{{ old('email') }}"
                    class="w-full px-4 py-3 rounded-lg border border-[#2E5C8A]/20 bg-white text-[#2E5C8A] focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-transparent"
                    placeholder="email@example.com"
                />
            </div>

            <!-- Password -->
            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <label for="password" class="text-sm font-medium text-[#2E5C8A]">
                        パスワード
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs text-[#2E5C8A] underline underline-offset-2 hover:text-[#6BB6FF] transition-colors" wire:navigate>
                            パスワードをお忘れですか？
                        </a>
                    @endif
                </div>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="w-full px-4 py-3 rounded-lg border border-[#2E5C8A]/20 bg-white text-[#2E5C8A] focus:outline-none focus:ring-2 focus:ring-[#6BB6FF] focus:border-transparent"
                    placeholder="パスワードを入力"
                />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center gap-2">
                <input
                    id="remember"
                    name="remember"
                    type="checkbox"
                    class="w-4 h-4 text-[#6BB6FF] border-[#2E5C8A]/20 rounded focus:ring-[#6BB6FF]"
                    {{ old('remember') ? 'checked' : '' }}
                />
                <label for="remember" class="text-sm text-dim">
                    ログイン状態を保持する
                </label>
            </div>

            <button
                type="submit"
                class="w-full px-6 py-3 rounded-lg font-semibold text-base accent-bg accent-text shadow-md hover:opacity-90 transition"
                data-test="login-button"
            >
                ログイン
            </button>
        </form>

        @if (Route::has('register'))
            <div class="text-sm text-center text-dim pt-4 border-t border-[#2E5C8A]/10">
                <span>アカウントをお持ちでない方は</span>
                <a href="{{ route('register') }}" class="text-[#2E5C8A] font-semibold underline underline-offset-2 hover:text-[#6BB6FF] transition-colors" wire:navigate>
                    新規登録
                </a>
            </div>
        @endif
    </div>
</x-layouts.auth>
