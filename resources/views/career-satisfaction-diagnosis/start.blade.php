@extends('components.layouts.simple')

@section('content')
<div class="min-h-screen bg-[#F0F7FF] flex flex-col items-center content-padding section-spacing-sm">
    <!-- Header / Intro -->
    <div class="w-full max-w-2xl mb-12 text-center">
        <h1 class="heading-2 mb-4">
            職業満足度診断
        </h1>
        <p class="body-large">
            今の仕事との「関係」を、判断や圧力なく、安全に言語化します。<br>
            あなたの状態を「問題」ではなく「進捗レポート」として可視化し、<br>
            次のステップを一緒に考えていきましょう。
        </p>
    </div>

    <!-- Livewire component -->
    <div class="w-full max-w-2xl">
        <livewire:career-satisfaction-diagnosis-form />
    </div>

    <!-- Footer small note -->
    <div class="w-full max-w-2xl text-center body-small mt-12">
        回答はあなた専用の記録として保存され、あとから見返せます。
    </div>
</div>
@endsection

