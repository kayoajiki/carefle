<x-admin.layouts.app title="マイゴール: {{ $user->name }}">
    <div class="min-h-screen w-full bg-[#F0F7FF] text-[#1E3A5F] content-padding section-spacing-sm">
        {{-- ヘッダー --}}
        <div class="max-w-4xl mx-auto mb-12">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between mb-6 gap-4">
                <div>
                    <h1 class="heading-2 mb-4">
                        {{ $user->name }}さんのマイゴール
                    </h1>
                    <p class="body-large">
                        ユーザーが設定したマイゴールを確認できます。
                    </p>
                </div>
                <a href="{{ route('admin.users.show', ['user' => $user->id]) }}" class="btn-secondary text-sm">
                    ユーザー詳細に戻る
                </a>
            </div>
        </div>

        {{-- マイゴール表示 --}}
        <div class="max-w-4xl mx-auto">
            <div class="card-refined p-8">
                <h2 class="heading-3 text-xl mb-6">マイゴール</h2>
                
                @if($user->goal_image)
                    <div class="mb-6">
                        <div class="bg-white rounded-xl p-6 border border-[#2E5C8A]/20">
                            <p class="body-text text-[#1E3A5F] whitespace-pre-wrap">{{ $user->goal_image }}</p>
                        </div>
                    </div>
                @endif

                @if($user->goal_image_url)
                    <div class="mb-6">
                        <h3 class="heading-3 text-lg mb-4">図式</h3>
                        <div class="bg-white rounded-xl p-6 border border-[#2E5C8A]/20">
                            <img src="{{ $user->goal_image_url }}" alt="マイゴール図式" class="max-w-full rounded-lg">
                        </div>
                    </div>
                @endif

                @if(!$user->goal_image && !$user->goal_image_url)
                    <div class="text-center py-12">
                        <p class="body-text text-[#1E3A5F]/70">マイゴールが設定されていません</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin.layouts.app>
