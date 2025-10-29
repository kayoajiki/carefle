@extends('components.layouts.simple')

@section('content')
<div class="min-h-screen bg-[#f2f7f5] flex flex-col items-center px-4 py-8">
    <!-- Header / Intro -->
    <div class="w-full max-w-xl mb-6 text-center">
        <h1 class="text-2xl font-semibold text-[#00473e]">
            現職・ライフ満足度チェック
        </h1>
        <p class="text-sm text-[#475d5b] mt-2 leading-relaxed">
            いまの働き方と暮らしの状態を、ざっくり数分で可視化します。<br>
            コメントを残すと、後のセッションでより深いフィードバックができます🌿
        </p>
    </div>

    <!-- Livewire component -->
    <div class="w-full max-w-xl">
        <livewire:diagnosis-form />
    </div>

    <!-- Footer small note -->
    <div class="w-full max-w-xl text-center text-[11px] text-[#475d5b] mt-8 leading-snug">
        回答はあなた専用の記録として保存され、あとから見返せます。
    </div>
</div>
@endsection

