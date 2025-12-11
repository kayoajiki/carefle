<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>キャリフレ - Career Fre</title>

        <link rel="icon" href="{{ asset('images/carekuma/carekuma-favicon.ico') }}" sizes="any">
        <link rel="icon" href="{{ asset('images/carekuma/carekuma-favicon.svg') }}" type="image/svg+xml">
        <link rel="apple-touch-icon" href="{{ asset('images/carekuma/carekuma-apple-touch-icon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=noto-sans-jp:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
        * {
            font-family: 'Noto Sans JP', ui-sans-serif, system-ui, sans-serif;
        }

        body {
            background-color: #F0F7FF; /* やわらかいブルー */
            color: #1E3A5F; /* 落ち着いたダークブルー */
        }

        .brand-headline {
            color: #2E5C8A;
        }

        .text-dim {
            color: rgba(30, 58, 95, 0.75);
        }

        .accent-bg {
            background-color: #6BB6FF;
        }
        .accent-text {
            color: #2E5C8A;
        }

        .badge-step {
            background-color: #2E5C8A;
            color: #fff;
        }

        .card-base {
            background-color: #ffffff;
            border-radius: 1rem; /* rounded-2xl系 */
            box-shadow: 0 10px 25px -5px rgba(74, 144, 226, 0.1), 0 4px 6px -2px rgba(74, 144, 226, 0.05);
        }

        .border-accent {
            border-color: #6BB6FF;
        }

        .soft-panel {
            background-color: #E8F4FF;
            border: 1px solid #6BB6FF;
            border-radius: 0.75rem;
        }

        .sample-leftbar {
            border-left: 4px solid #6BB6FF;
        }

        .section-pad {
            padding-top: 3rem;    /* py-12相当 */
            padding-bottom: 3rem;
        }
        @media(min-width:768px){
            .section-pad{
                padding-top:5rem; /* py-20相当 */
                padding-bottom:5rem;
            }
        }
        </style>
    </head>
<body class="antialiased selection:bg-blue-200/60 selection:text-[#2E5C8A]">

    <!-- =========================
         Header / Navigation
    ========================== -->
    <header class="w-full px-6 py-4 md:px-8">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="text-xl md:text-2xl font-bold brand-headline flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-[#6BB6FF] shadow-sm"></span>
                <span>キャリフレ</span>
            </div>

            @if (Route::has('login'))
                <nav class="flex items-center gap-3 text-sm md:text-base font-medium">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-4 py-2 rounded-lg text-[#2E5C8A] hover:bg-white/60 transition-colors">
                            ダッシュボード
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg text-[#2E5C8A] hover:bg-white/60 transition-colors">
                            ログイン
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg font-semibold accent-bg accent-text hover:opacity-90 transition">
                                新規登録
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </div>
        </header>

    <!-- =========================
         HERO
    ========================== -->
    <section class="px-6 md:px-8 section-pad" id="top">
        <div class="max-w-7xl mx-auto flex flex-col gap-10 lg:gap-12">

            <div class="space-y-6 text-center">
                <div class="inline-flex items-center gap-2 text-xs md:text-sm font-semibold badge-step rounded-full px-3 py-1 tracking-wide w-fit mx-auto">
                    自己理解の旅を始める「キャリフレ」
                </div>

                <!-- キャリくま -->
                <div class="flex justify-center">
                    <div class="w-32 h-32 md:w-40 md:h-40 lg:w-48 lg:h-48">
                        <img 
                            src="{{ asset('images/carekuma/carekuma-full.png') }}" 
                            alt="キャリくま" 
                            class="w-full h-full object-contain"
                            loading="eager"
                            onerror="this.onerror=null; this.src='{{ asset('images/carekuma/carekuma-full.jpg') }}';"
                        />
                    </div>
                </div>

                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold leading-tight brand-headline">
                    自分を知る → 記録する → 行動する<br class="hidden md:block">
                    あなたの自己理解の旅が、ここから始まります。
                </h1>

                <p class="text-lg md:text-xl leading-relaxed text-dim max-w-4xl mx-auto">
                    キャリフレは、診断・記録・行動計画のツールで、あなたの「今」を可視化し、<br class="hidden md:block">
                    「これから」を具体化する自己理解プラットフォームです。<br class="hidden md:block">
                    転職する／続ける／働き方を変える——まずは自分を理解することから始めましょう。
                </p>

                <div class="flex flex-col sm:flex-row gap-4 pt-2 justify-center">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-6 py-3 rounded-lg font-semibold text-lg accent-bg accent-text shadow-md hover:opacity-90 transition">
                            無料で始める
                        </a>
                    @endif
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="inline-flex justify-center items-center px-6 py-3 rounded-lg font-semibold text-lg bg-white text-[#2E5C8A] shadow-md border border-[#6BB6FF] hover:bg-[#E8F4FF] transition">
                            ログイン
                        </a>
                    @endif
                </div>

                <p class="text-[12px] md:text-[13px] text-dim leading-relaxed">
                    ※登録後すぐに、診断や記録ツールを無料でご利用いただけます。
                </p>
            </div>
        </div>
    </section>

    <!-- =========================
         FEATURES / キャリフレの機能
    ========================== -->
    <section id="features" class="px-6 md:px-8 section-pad bg-white/60">
        <div class="max-w-7xl mx-auto">
            <div class="space-y-6 text-center mb-12 md:mb-16">
                <div class="text-xs md:text-sm font-semibold inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-[#6BB6FF] text-[#2E5C8A] shadow-sm w-fit mx-auto">
                    あなたの自己理解を支えるツール
                </div>

                <h2 class="text-2xl md:text-3xl font-bold brand-headline leading-tight">
                    診断・記録・行動計画。<br class="hidden md:block">
                    すべてがあなたの手の中に。
                </h2>

                <p class="text-lg md:text-xl text-dim leading-relaxed max-w-4xl mx-auto">
                    キャリフレは、自分で気づくためのツールを提供します。<br class="hidden md:block">
                    データとして蓄積され、可視化される——日常的な自己理解の習慣が、あなたを変えます。
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8 mb-12">
                <!-- 診断・分析 -->
                <div class="card-base p-6 bg-white border border-[#6BB6FF]">
                    <div class="text-[#6BB6FF] text-3xl mb-3">📊</div>
                    <h3 class="font-semibold text-[#2E5C8A] text-base md:text-lg mb-2">
                        診断・分析
                    </h3>
                    <ul class="text-dim text-[14px] md:text-[15px] leading-relaxed space-y-1">
                        <li>• 現職満足度診断</li>
                        <li>• 自己診断結果</li>
                        <li>• レーダーチャート可視化</li>
                    </ul>
                    <div class="mt-4">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-[#6BB6FF] text-sm font-medium hover:underline">
                                診断を始める →
                            </a>
                        @endif
                    </div>
                </div>

                <!-- 記録・振り返り -->
                <div class="card-base p-6 bg-white border border-[#6BB6FF]">
                    <div class="text-[#6BB6FF] text-3xl mb-3">📝</div>
                    <h3 class="font-semibold text-[#2E5C8A] text-base md:text-lg mb-2">
                        記録・振り返り
                    </h3>
                    <ul class="text-dim text-[14px] md:text-[15px] leading-relaxed space-y-1">
                        <li>• 日記</li>
                        <li>• 人生史</li>
                        <li>• マイルストーン</li>
                    </ul>
                    <div class="mt-4">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-[#6BB6FF] text-sm font-medium hover:underline">
                                記録を始める →
                            </a>
                        @endif
                    </div>
                </div>

                <!-- 行動計画 -->
                <div class="card-base p-6 bg-white border border-[#6BB6FF]">
                    <div class="text-[#6BB6FF] text-3xl mb-3">🎯</div>
                    <h3 class="font-semibold text-[#2E5C8A] text-base md:text-lg mb-2">
                        行動計画
                    </h3>
                    <ul class="text-dim text-[14px] md:text-[15px] leading-relaxed space-y-1">
                        <li>• WCMシート</li>
                        <li>• マイルストーン</li>
                        <li>• アクション管理</li>
                    </ul>
                    <div class="mt-4">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-[#6BB6FF] text-sm font-medium hover:underline">
                                計画を立てる →
                            </a>
                        @endif
                    </div>
                </div>

                <!-- サポート -->
                <div class="card-base p-6 bg-white border border-[#6BB6FF]">
                    <div class="text-[#6BB6FF] text-3xl mb-3">💬</div>
                    <h3 class="font-semibold text-[#2E5C8A] text-base md:text-lg mb-2">
                        サポート
                    </h3>
                    <ul class="text-dim text-[14px] md:text-[15px] leading-relaxed space-y-1">
                        <li>• 面談申し込み</li>
                        <li>• チャット相談</li>
                        <li>• 専門家との対話</li>
                    </ul>
                    <div class="mt-4">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-[#6BB6FF] text-sm font-medium hover:underline">
                                相談する →
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="text-center">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-6 py-3 rounded-lg font-semibold text-lg accent-bg accent-text shadow-md hover:opacity-90 transition">
                        無料で始める
                    </a>
                @endif
            </div>
        </div>
    </section>

    <!-- =========================
         OUTPUT / あなた専用アウトプット
    ========================== -->
    <section id="output" class="px-6 md:px-8 section-pad bg-white/0">
        <div class="max-w-7xl mx-auto">
            <div class="space-y-6 text-center">
                <div class="text-xs md:text-sm font-semibold inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-[#6BB6FF] text-[#2E5C8A] shadow-sm w-fit mx-auto">
                    データとして蓄積される、あなたの自己理解
                </div>

                <h2 class="text-2xl md:text-3xl font-bold brand-headline leading-tight">
                    診断・記録・行動計画が、<br class="hidden md:block">
                    あなたの「ぶれない軸」と「次の一歩」を可視化します。
                </h2>

                <p class="text-lg md:text-xl text-dim leading-relaxed max-w-4xl mx-auto">
                    キャリフレでは、あなたの診断結果・記録・行動計画がすべてデータとして蓄積されます。<br class="hidden md:block">
                    レーダーチャートやタイムラインで可視化され、いつでも振り返ることができます。<br class="hidden md:block">
                    悩んだときに確認できる、あなただけの自己理解マップです。
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8 max-w-7xl mx-auto">
                    <!-- Card 1 -->
                    <div class="card-base p-6 bg-white border border-[#6BB6FF]">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="text-[#2E5C8A] font-bold text-xl flex-shrink-0">●</span>
                            <h3 class="font-semibold text-[#2E5C8A] text-base md:text-lg leading-tight">
                                あなたが大事にしてきた価値観
                            </h3>
                        </div>
                        <p class="text-dim text-[14px] md:text-[15px] leading-relaxed pl-8">
                            どんな場面で心が動き、何を守ろうとしてきたのかを言葉にします。
                        </p>
                    </div>

                    <!-- Card 2 -->
                    <div class="card-base p-6 bg-white border border-[#6BB6FF]">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="text-[#2E5C8A] font-bold text-xl flex-shrink-0">●</span>
                            <h3 class="font-semibold text-[#2E5C8A] text-base md:text-lg leading-tight">
                                強みと活かし方
                            </h3>
                        </div>
                        <p class="text-dim text-[14px] md:text-[15px] leading-relaxed pl-8">
                            「今どこで頼りにされているのか」「どんな環境ならもっと活きるのか」を整理します。
                        </p>
                    </div>

                    <!-- Card 3 -->
                    <div class="card-base p-6 bg-white border border-[#6BB6FF]">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="text-[#2E5C8A] font-bold text-xl flex-shrink-0">●</span>
                            <h3 class="font-semibold text-[#2E5C8A] text-base md:text-lg leading-tight">
                                Will / Can / Must<br>（これからの指針）
                            </h3>
                        </div>
                        <p class="text-dim text-[14px] md:text-[15px] leading-relaxed pl-8">
                            "こう在りたい"を軸に、次の90日で実際に動くアクションプランまで落とし込みます。
                        </p>
                    </div>
                </div>

                <div class="soft-panel p-4 text-[14px] md:text-[15px] leading-relaxed text-[#2E5C8A] max-w-3xl mx-auto">
                    「転職するべき？」ではなく、<br class="hidden md:block">
                    「私は何を守りたいから、こう動くのか」まで言葉になります。
                </div>

                <div class="pt-4">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-5 py-3 rounded-lg font-semibold text-base md:text-lg accent-bg accent-text shadow-md hover:opacity-90 transition">
                            無料登録して、準備シートを見る
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- =========================
         SAMPLE / サンプルイメージ
    ========================== -->
    {{--
    <section id="sample" class="px-6 md:px-8 section-pad">
        <div class="max-w-7xl mx-auto">
            <div class="max-w-2xl mx-auto">
                <div class="card-base p-6 md:p-8 bg-white border border-[#6BB6FF] relative">
                    <div class="absolute -top-3 -left-3 bg-white shadow rounded-lg border border-[#6BB6FF] px-3 py-1 text-[11px] md:text-[12px] font-semibold text-[#2E5C8A] flex items-center gap-1">
                        <span class="text-[#6BB6FF]">◆</span>
                        サンプルイメージ
                    </div>

                    <h3 class="text-xl md:text-2xl font-bold text-[#2E5C8A] leading-snug mb-4">
                        キャリフレ（サマリーPDF）
                    </h3>

                    <div class="space-y-6 text-[14px] md:text-[15px] text-dim leading-relaxed">
                        <div class="sample-leftbar pl-3">
                            <div class="text-[#2E5C8A] font-semibold text-[14px] md:text-[15px] mb-1">あなたの価値観リスト</div>
                        <div class="leading-relaxed">
                            ●「安心できる人間関係」<br>
                            ●「成果より成長の実感」<br>
                            ●「無視されないこと・声が届くこと」
                        </div>
                    </div>

                    <div class="sample-leftbar pl-3">
                        <div class="text-[#2E5C8A] font-semibold text-[14px] md:text-[15px] mb-1">あなたの強み</div>
                        <div class="leading-relaxed">
                            ・相手の気持ちを先に受け止める聞き方ができる<br>
                            ・混乱している現場を落ち着かせる役割を取れる
                        </div>
                    </div>

                    <div class="sample-leftbar pl-3">
                        <div class="text-[#2E5C8A] font-semibold text-[14px] md:text-[15px] mb-1">次の90日アクション</div>
                        <div class="leading-relaxed">
                            1. 週1回、「不満」ではなく「こうしたい」を上司に伝える<br>
                            2. 転職サイトではなく、OB訪問で業界情報を集める
                        </div>
                    </div>
                </div>

                    <p class="mt-6 text-[11px] md:text-[12px] text-dim leading-relaxed border-t border-gray-200 pt-4">
                        ※上記はサンプルです。実際はあなたの振り返り内容・言葉をそのまま反映します。
                    </p>
                </div>
            </div>
        </div>
    </section>
    --}}
    <!-- =========================
         JOURNEY / 自己理解の3ステップ
    ========================== -->
    <section id="journey" class="px-6 md:px-8 section-pad bg-white/60">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12 md:mb-16">
                <div class="text-xs md:text-sm font-semibold inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-[#6BB6FF] text-[#2E5C8A] shadow-sm w-fit mx-auto mb-4">
                    自己理解の旅
                </div>
                <h2 class="text-2xl md:text-3xl font-bold brand-headline leading-tight mb-4">
                    自分を知る → 記録する → 行動する
                </h2>
                <p class="text-lg md:text-xl text-dim leading-relaxed">
                    診断で「今の自分」を可視化し、記録で「過去と現在」を言語化し、<br class="hidden md:block">
                    行動計画で「未来」を具体化する——3つのステップで、あなたの自己理解が深まります。
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">

                <!-- Step 1 / 診断する -->
                <div class="card-base p-6 flex flex-col bg-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-xs md:text-sm font-semibold badge-step rounded-full px-3 py-1">
                            ステップ1：診断する
                        </div>
                        <div class="text-right text-[11px] md:text-[12px] font-medium text-dim">
                            テーマ：今の自分
                        </div>
                    </div>

                    <h3 class="text-lg md:text-xl font-bold text-[#2E5C8A] leading-snug mb-3">
                        「今の自分」を可視化する
                    </h3>

                    <p class="text-base md:text-lg text-dim leading-relaxed mb-4">
                        現職満足度診断と自己診断で、<br>
                        あなたの「満足度」と「重要度」を比較し、<br>
                        レーダーチャートで可視化します。
                    </p>

                    <ul class="text-base text-[#1E3A5F] space-y-2 mb-6">
                        <li class="flex items-start gap-2">
                            <span class="text-[#6BB6FF] font-bold flex-shrink-0 mt-0.5">・</span>
                            <span class="leading-relaxed">現職満足度診断（満足度vs重要度）</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#6BB6FF] font-bold flex-shrink-0 mt-0.5">・</span>
                            <span class="leading-relaxed">自己診断結果（MBTI/ストレングス/FFSなど）</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#6BB6FF] font-bold flex-shrink-0 mt-0.5">・</span>
                            <span class="leading-relaxed">レーダーチャートで可視化</span>
                        </li>
                    </ul>

                    <div class="mt-auto">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex justify-center items-center w-full px-4 py-2 rounded-lg font-semibold text-sm accent-bg accent-text shadow-sm hover:opacity-90 transition">
                                診断を始める
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Step 2 / 記録する -->
                <div class="card-base p-6 flex flex-col bg-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-xs md:text-sm font-semibold badge-step rounded-full px-3 py-1">
                            ステップ2：記録する
                        </div>
                        <div class="text-right text-[11px] md:text-[12px] font-medium text-dim">
                            テーマ：過去と現在
                        </div>
                    </div>

                    <h3 class="text-lg md:text-xl font-bold text-[#2E5C8A] leading-snug mb-3">
                        「過去と現在」を言語化する
                    </h3>

                    <p class="text-base md:text-lg text-dim leading-relaxed mb-4">
                        日記で毎日のコンディションを記録し、<br>
                        人生史で価値観のルーツを可視化し、<br>
                        マイルストーンで目標を管理します。
                    </p>

                    <ul class="text-base text-[#1E3A5F] space-y-2 mb-6">
                        <li class="flex items-start gap-2">
                            <span class="text-[#6BB6FF] font-bold flex-shrink-0 mt-0.5">・</span>
                            <span class="leading-relaxed">日記（モチベーション・写真付きカレンダー）</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#6BB6FF] font-bold flex-shrink-0 mt-0.5">・</span>
                            <span class="leading-relaxed">人生史（タイムラインで価値観のルーツ）</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#6BB6FF] font-bold flex-shrink-0 mt-0.5">・</span>
                            <span class="leading-relaxed">マイルストーン（目標とアクション管理）</span>
                        </li>
                    </ul>

                    <div class="mt-auto">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex justify-center items-center w-full px-4 py-2 rounded-lg font-semibold text-sm accent-bg accent-text shadow-sm hover:opacity-90 transition">
                                記録を始める
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Step 3 / 行動する -->
                <div class="card-base p-6 flex flex-col bg-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-xs md:text-sm font-semibold badge-step rounded-full px-3 py-1">
                            ステップ3：行動する
                        </div>
                        <div class="text-right text-[11px] md:text-[12px] font-medium text-dim">
                            テーマ：未来
                        </div>
                    </div>

                    <h3 class="text-lg md:text-xl font-bold text-[#2E5C8A] leading-snug mb-3">
                        「未来」を具体化する
                    </h3>

                    <p class="text-base md:text-lg text-dim leading-relaxed mb-4">
                        WCMシートで「Will・Can・Must」を整理し、<br>
                        マイルストーンで目標とアクションを管理し、<br>
                        次の一歩を具体化します。
                    </p>

                    <ul class="text-base text-[#1E3A5F] space-y-2 mb-6">
                        <li class="flex items-start gap-2">
                            <span class="text-[#6BB6FF] font-bold flex-shrink-0 mt-0.5">・</span>
                            <span class="leading-relaxed">WCMシート（Will・Can・Must）</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#6BB6FF] font-bold flex-shrink-0 mt-0.5">・</span>
                            <span class="leading-relaxed">マイルストーン（目標とアクション）</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#6BB6FF] font-bold flex-shrink-0 mt-0.5">・</span>
                            <span class="leading-relaxed">次の一歩を具体化</span>
                        </li>
                    </ul>

                    <div class="mt-auto">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex justify-center items-center w-full px-4 py-2 rounded-lg font-semibold text-sm accent-bg accent-text shadow-sm hover:opacity-90 transition">
                                計画を立てる
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- =========================
         VALUES / 私たちが大事にしていること
    ========================== -->
    <section class="px-6 md:px-8 section-pad bg-white/60">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12 md:mb-16">
                <div class="text-xs md:text-sm font-semibold inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-[#6BB6FF] text-[#2E5C8A] shadow-sm w-fit mx-auto mb-4">
                    キャリフレの価値
                </div>
                <h2 class="text-2xl md:text-3xl font-bold brand-headline leading-tight mb-4">
                    自分で気づくツールを提供します
                </h2>
                <p class="text-lg md:text-xl text-dim leading-relaxed max-w-4xl mx-auto">
                    答えを与えるのではなく、あなたの中の答えを見えるようにする。<br class="hidden md:block">
                    日常的な自己理解の習慣が、あなたを変えます。
                </p>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16">
            <div class="space-y-8">

                <div class="space-y-6 text-base md:text-lg leading-relaxed text-[#1E3A5F]">

                    <div class="p-4 rounded-lg bg-white border border-[#6BB6FF] shadow-sm">
                        <div class="font-semibold text-[#2E5C8A] mb-2 text-base md:text-lg">1. 自分で気づくツールを提供</div>
                        <div class="text-dim text-[14px] md:text-[15px] leading-relaxed">
                            診断・記録・可視化のツールで、あなたの中の答えを見えるようにします。
                            決めつけたり、特定の選択を押しつけたりはしません。
                        </div>
                    </div>

                    <div class="p-4 rounded-lg bg-white border border-[#6BB6FF] shadow-sm">
                        <div class="font-semibold text-[#2E5C8A] mb-2 text-base md:text-lg">2. "今のあなた"を否定しない</div>
                        <div class="text-dim text-[14px] md:text-[15px] leading-relaxed">
                            今の環境で耐えてきたことも、守ってきたものも、ちゃんと意味がある。
                            「がまんしてきた自分」を置き去りにしません。
                        </div>
                    </div>

                    <div class="p-4 rounded-lg bg-white border border-[#6BB6FF] shadow-sm">
                        <div class="font-semibold text-[#2E5C8A] mb-2 text-base md:text-lg">3. データとして蓄積され、可視化される</div>
                        <div class="text-dim text-[14px] md:text-[15px] leading-relaxed">
                            ふわっとした自己理解では終わらせません。
                            あなたの診断結果・記録・行動計画がすべてデータとして蓄積され、
                            レーダーチャートやタイムラインで可視化されます。
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-base p-6 md:p-8 bg-white border border-[#6BB6FF]">
                <h3 class="text-xl md:text-2xl font-bold text-[#2E5C8A] leading-snug mb-4">
                    日常的な自己理解の習慣が、あなたを変えます
                </h3>
                <ul class="text-base md:text-lg space-y-5 text-[#1E3A5F] leading-relaxed">
                    <li class="flex flex-col">
                        <span class="font-semibold text-[#2E5C8A]">● 診断で「今の自分」を可視化</span>
                        <span class="text-dim text-[14px] md:text-[15px] leading-relaxed">
                            満足度と重要度を比較し、レーダーチャートで見える化。
                        </span>
                    </li>
                    <li class="flex flex-col">
                        <span class="font-semibold text-[#2E5C8A]">● 記録で「過去と現在」を言語化</span>
                        <span class="text-dim text-[14px] md:text-[15px] leading-relaxed">
                            日記・人生史・マイルストーンで、価値観のルーツを発見。
                        </span>
                    </li>
                    <li class="flex flex-col">
                        <span class="font-semibold text-[#2E5C8A]">● 行動計画で「未来」を具体化</span>
                        <span class="text-dim text-[14px] md:text-[15px] leading-relaxed">
                            WCMシートとマイルストーンで、次の一歩を明確に。
                        </span>
                    </li>
                </ul>

                <div class="mt-6 text-[12px] md:text-[13px] text-dim leading-relaxed border-t border-gray-200 pt-4">
                    悩み続ける時間を「進んでいる実感がある時間」に置き換える——それがキャリフレの目的です。
                </div>
            </div>
        </div>
    </section>

    <!-- =========================
         FINAL CTA
    ========================== -->
    <section class="px-6 md:px-8 section-pad bg-white/0">
        <div class="max-w-7xl mx-auto">
            <div class="max-w-3xl mx-auto">
                <div class="card-base bg-white text-center p-8 md:p-12 border border-[#6BB6FF]">
            <h2 class="text-xl md:text-2xl font-bold brand-headline leading-snug mb-4">
                まずは診断から始めましょう
            </h2>

            <p class="text-lg md:text-xl text-dim leading-relaxed mb-6 max-w-2xl mx-auto">
                登録後すぐに、診断・記録・行動計画のツールを無料でご利用いただけます。<br class="hidden md:block">
                自分を知る旅を、今日から始めませんか？
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-6 py-3 rounded-lg font-semibold text-lg accent-bg accent-text shadow-md hover:opacity-90 transition">
                        無料で始める
                    </a>
        @endif

                <a href="#top" class="inline-flex justify-center items-center px-6 py-3 rounded-lg font-semibold text-lg bg-white text-[#2E5C8A] shadow-md border border-[#6BB6FF] hover:bg-[#E8F4FF] transition">
                    もう一度くわしく読む
                </a>
            </div>

            <p class="text-[12px] md:text-[13px] text-dim leading-relaxed mt-6">
                ※無理な勧誘や転職あっせん等は行いません。<br>
                ※あなたの答えを、あなた自身と一緒に見つけるサービスです。
            </p>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================
         FOOTER
    ========================== -->
    <footer class="max-w-7xl mx-auto px-6 md:px-8 py-10 text-center text-[12px] md:text-[13px] leading-relaxed text-dim">
        <p class="mb-2">&copy; {{ date('Y') }} キャリフレ / Career Fre</p>
        <p>
            本サービスはキャリア相談・自己理解支援を目的としたプログラムです。医療・法律・労務上の助言には該当しません。
        </p>
    </footer>

    </body>
</html>
