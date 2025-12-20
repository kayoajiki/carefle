<x-layouts.app.sidebar title="コンテキスト別持ち味レポ">
    <flux:main>
        <style>
            @media (min-width: 768px) {
                flux-main {
                    padding-top: 1.5rem !important;
                }
            }
        </style>
        <div class="min-h-screen bg-gradient-to-b from-[#E9F2FF] to-[#F6FBFF]">
            <div class="w-full max-w-4xl mx-auto content-padding pt-0 pb-8 md:pb-12">
                <div class="card-refined surface-blue p-10 soft-shadow-refined space-y-8">
                    {{-- ヘッダー --}}
                    <div class="text-center space-y-4">
                        <h1 class="heading-1">コンテキスト別持ち味レポ</h1>
                        <p class="body-text text-[#1E3A5F]/70">
                            各コンテキスト（仕事、家族、趣味など）におけるあなたの持ち味を確認できます
                        </p>
                    </div>

                    {{-- コンテキスト一覧 --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                        @foreach($contexts as $context)
                        <a 
                            href="{{ route('manual.context', ['context' => $context['key']]) }}"
                            class="block"
                        >
                            <div class="card-refined surface-white p-6 hover:shadow-lg transition-shadow border-2 {{ $context['canGenerate'] ? 'border-[#6BB6FF]' : 'border-gray-200' }}">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="heading-3 text-xl text-[#2E5C8A]">
                                        {{ $context['label'] }}
                                    </h3>
                                    @if($context['canGenerate'])
                                        <span class="px-3 py-1 bg-[#6BB6FF] text-white rounded-full text-sm font-medium">
                                            生成可能
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-gray-200 text-gray-600 rounded-full text-sm">
                                            {{ $context['count'] }}/5件
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="space-y-2">
                                    <p class="body-text text-[#1E3A5F]/70">
                                        記録数: <span class="font-semibold">{{ $context['count'] }}件</span>
                                    </p>
                                    @if(!$context['canGenerate'])
                                        <p class="body-small text-[#1E3A5F]/60">
                                            あと{{ 5 - $context['count'] }}件で生成可能です
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @endforeach
                    </div>

                    {{-- 説明 --}}
                    <div class="mt-8 p-6 bg-[#E8F4FF] rounded-xl border border-[#6BB6FF]/20">
                        <h3 class="heading-3 text-lg mb-3">コンテキスト別持ち味レポについて</h3>
                        <p class="body-text text-[#1E3A5F]/70 mb-4">
                            各コンテキスト（仕事、家族、趣味など）に関する日記が5件以上蓄積されると、そのコンテキスト専用の持ち味レポを生成できます。
                        </p>
                        <p class="body-text text-[#1E3A5F]/70">
                            「仕事の自分」「家族の自分」など、コンテキストごとの持ち味を発見することで、より深い自己理解につながります。
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </flux:main>
</x-layouts.app.sidebar>




