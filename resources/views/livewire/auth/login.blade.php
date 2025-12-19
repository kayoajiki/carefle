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

        {{-- Google認証（一時的にコメントアウト） --}}
        {{-- <!-- Divider -->
        <div class="relative my-4">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-[#2E5C8A]/20"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-[#1E3A5F]/70">または</span>
            </div>
        </div>

        <!-- Google Login Button -->
        <a
            href="{{ route('auth.google') }}"
            class="w-full px-6 py-3 rounded-lg font-semibold text-base bg-white text-[#1E3A5F] border-2 border-[#2E5C8A]/20 shadow-sm hover:bg-gray-50 transition flex items-center justify-center gap-3"
        >
            <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Googleでログイン
        </a> --}}

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
