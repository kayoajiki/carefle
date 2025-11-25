@extends('components.layouts.simple')

@section('content')
<div class="min-h-screen bg-[#F0F7FF] flex flex-col items-center content-padding section-spacing-sm">
    <!-- Header / Intro -->
    <div class="w-full max-w-2xl mb-12 text-center">
        <h1 class="heading-2 mb-4">
            現職・ライフ満足度チェック
        </h1>
        <p class="body-large">
            いまの働き方と暮らしの状態を、ざっくり数分で可視化します。<br>
            コメントを残すと、後のセッションでより深いフィードバックができます🌿
        </p>
    </div>

    <!-- Livewire component -->
    <div class="w-full max-w-2xl">
        <livewire:diagnosis-form />
    </div>

    <!-- Footer small note -->
    <div class="w-full max-w-2xl text-center body-small mt-12">
        回答はあなた専用の記録として保存され、あとから見返せます。
    </div>
</div>
@endsection

