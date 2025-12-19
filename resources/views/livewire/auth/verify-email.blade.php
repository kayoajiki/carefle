<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <div class="flex w-full flex-col text-center mb-4">
            <h1 class="text-2xl md:text-3xl font-bold brand-headline mb-2">メール認証</h1>
            <p class="text-sm text-dim leading-relaxed">
                ご登録いただいたメールアドレスに認証リンクを送信しました
            </p>
        </div>

        <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm p-4 rounded-lg">
            <p class="text-center">
                メールアドレスを確認するには、メールに記載されている認証リンクをクリックしてください。
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="bg-green-50 border border-green-200 text-green-800 text-sm p-4 rounded-lg">
                <p class="text-center font-medium">
                    新しい認証リンクをメールアドレスに送信しました。
                </p>
            </div>
        @endif

        <div class="flex flex-col gap-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button
                    type="submit"
                    class="w-full px-6 py-3 rounded-lg font-semibold text-base accent-bg accent-text shadow-md hover:opacity-90 transition"
                >
                    認証メールを再送信
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="w-full px-6 py-3 rounded-lg font-medium text-sm text-[#2E5C8A] border border-[#2E5C8A]/20 hover:bg-[#2E5C8A]/5 transition"
                    data-test="logout-button"
                >
                    ログアウト
                </button>
            </form>
        </div>
    </div>
</x-layouts.auth>
