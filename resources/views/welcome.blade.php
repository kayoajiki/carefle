<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>キャリフレ - Career Fre</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=noto-sans-jp:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
        * {
            font-family: 'Noto Sans JP', ui-sans-serif, system-ui, sans-serif;
        }

        body {
            background-color: #f2f7f5; /* やわらかいグリーン */
            color: #1f2e2c; /* 落ち着いた深めグリーン */
        }

        .brand-headline {
            color: #00473e;
        }

        .text-dim {
            color: rgba(31,46,44,0.75);
        }

        .accent-bg {
            background-color: #faae2b;
        }
        .accent-text {
            color: #00473e;
        }

        .badge-step {
            background-color: #00473e;
            color: #fff;
        }

        .card-base {
            background-color: #ffffff;
            border-radius: 1rem; /* rounded-2xl系 */
            box-shadow: 0 24px 48px -12px rgba(0,0,0,0.15);
        }

        .border-accent {
            border-color: #faae2b;
        }

        .soft-panel {
            background-color: #fff9eb;
            border: 1px solid #faae2b;
            border-radius: 0.75rem;
        }

        .sample-leftbar {
            border-left: 4px solid #faae2b;
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
<body class="antialiased selection:bg-yellow-200/60 selection:text-[#00473e]">

    <!-- =========================
         Header / Navigation
    ========================== -->
    <header class="w-full px-6 py-4 md:px-8">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="text-xl md:text-2xl font-bold brand-headline flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-[#faae2b] shadow-sm"></span>
                <span>キャリフレ</span>
            </div>

            @if (Route::has('login'))
                <nav class="flex items-center gap-3 text-sm md:text-base font-medium">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-4 py-2 rounded-lg text-[#00473e] hover:bg-white/60 transition-colors">
                            ダッシュボード
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded-lg text-[#00473e] hover:bg-white/60 transition-colors">
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
                    内省支援サービス「キャリフレ」
                </div>

                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold leading-tight brand-headline">
                    「このままでいいのか？」に、<br class="hidden md:block">
                    自分の言葉で答えが出せるようになる。
                </h1>

                <p class="text-lg md:text-xl leading-relaxed text-dim max-w-4xl mx-auto">
                    キャリフレは、あなたの「過去・現在・未来」を3回のセッションで整理。<br>
                    強み・価値観・これから進む方向を言語化するプログラムです。<br class="hidden md:block">
                    転職する／続ける／働き方を変える——次の一歩を、自分で選べる状態まで伴走します。
                </p>

                {{-- <ul class="text-base md:text-lg text-[#00473e] space-y-2 max-w-2xl mx-auto"> --}}
                    {{-- <li class="flex items-start gap-2"> --}}
                        {{-- <span class="text-[#faae2b] font-bold flex-shrink-0 mt-0.5">●</span> --}}
                        {{-- <span class="leading-relaxed">1on1セッション3回（過去 / 現在 / 未来）</span> --}}
                    {{-- </li> --}}
                    {{-- <li class="flex items-start gap-2"> --}}
                        {{-- <span class="text-[#faae2b] font-bold flex-shrink-0 mt-0.5">●</span> --}}
                        {{-- <span class="leading-relaxed">事前ワークシートで内省を深めてから当日へ</span> --}}
                    {{-- </li> --}}
                    {{-- <li class="flex items-start gap-2"> --}}
                        {{-- <span class="text-[#faae2b] font-bold flex-shrink-0 mt-0.5">●</span> --}}
                        {{-- <span class="leading-relaxed">受講後は「キャリフレPDF」が手元に残る</span> --}}
                    {{-- </li> --}}
                {{-- </ul> --}}

                <div class="flex flex-col sm:flex-row gap-4 pt-2 justify-center">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-6 py-3 rounded-lg font-semibold text-lg accent-bg accent-text shadow-md hover:opacity-90 transition">
                            無料で始める
                        </a>
                    @endif
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="inline-flex justify-center items-center px-6 py-3 rounded-lg font-semibold text-lg bg-white text-[#00473e] shadow-md border border-[#faae2b] hover:bg-[#fff9eb] transition">
                            ログイン
                        </a>
                    @endif
                </div>

                <p class="text-[12px] md:text-[13px] text-dim leading-relaxed">
                    ※各回の前日までに、簡単な準備ワーク（シート記入）をお願いしています。
                </p>
            </div>
        </div>
    </section>

    <!-- =========================
         OUTPUT / あなた専用アウトプット
    ========================== -->
    <section id="output" class="px-6 md:px-8 section-pad bg-white/60">
        <div class="max-w-7xl mx-auto">
            <div class="space-y-6 text-center">
                <div class="text-xs md:text-sm font-semibold inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-[#faae2b] text-[#00473e] shadow-sm w-fit mx-auto">
                    あなた専用アウトプット
                </div>

                <h2 class="text-2xl md:text-3xl font-bold brand-headline leading-tight">
                    最後に手元に残るのは、<br class="hidden md:block">
                    あなたの「ぶれない軸」と「次の90日」。
                </h2>

                <p class="text-lg md:text-xl text-dim leading-relaxed max-w-4xl mx-auto">
                    セッションの内容は、口頭アドバイスだけで終わりません。<br class="hidden md:block">
                    あなたの価値観・強み・これからの指針をまとめた
                    "キャリフレPDF"としてお渡しします。<br class="hidden md:block">
                    悩んだときに読み返せる、あなただけの取扱説明書です。
                </p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8 max-w-7xl mx-auto">
                    <!-- Card 1 -->
                    <div class="card-base p-6 bg-white border border-[#faae2b]">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="text-[#00473e] font-bold text-xl flex-shrink-0">●</span>
                            <h3 class="font-semibold text-[#00473e] text-base md:text-lg leading-tight">
                                あなたが大事にしてきた価値観
                            </h3>
                        </div>
                        <p class="text-dim text-[14px] md:text-[15px] leading-relaxed pl-8">
                            どんな場面で心が動き、何を守ろうとしてきたのかを言葉にします。
                        </p>
                    </div>

                    <!-- Card 2 -->
                    <div class="card-base p-6 bg-white border border-[#faae2b]">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="text-[#00473e] font-bold text-xl flex-shrink-0">●</span>
                            <h3 class="font-semibold text-[#00473e] text-base md:text-lg leading-tight">
                                強みと活かし方
                            </h3>
                        </div>
                        <p class="text-dim text-[14px] md:text-[15px] leading-relaxed pl-8">
                            「今どこで頼りにされているのか」「どんな環境ならもっと活きるのか」を整理します。
                        </p>
                    </div>

                    <!-- Card 3 -->
                    <div class="card-base p-6 bg-white border border-[#faae2b]">
                        <div class="flex items-start gap-3 mb-3">
                            <span class="text-[#00473e] font-bold text-xl flex-shrink-0">●</span>
                            <h3 class="font-semibold text-[#00473e] text-base md:text-lg leading-tight">
                                Will / Can / Must<br>（これからの指針）
                            </h3>
                        </div>
                        <p class="text-dim text-[14px] md:text-[15px] leading-relaxed pl-8">
                            "こう在りたい"を軸に、次の90日で実際に動くアクションプランまで落とし込みます。
                        </p>
                    </div>
                </div>

                <div class="soft-panel p-4 text-[14px] md:text-[15px] leading-relaxed text-[#00473e] max-w-3xl mx-auto">
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
                <div class="card-base p-6 md:p-8 bg-white border border-[#faae2b] relative">
                    <div class="absolute -top-3 -left-3 bg-white shadow rounded-lg border border-[#faae2b] px-3 py-1 text-[11px] md:text-[12px] font-semibold text-[#00473e] flex items-center gap-1">
                        <span class="text-[#faae2b]">◆</span>
                        サンプルイメージ
                    </div>

                    <h3 class="text-xl md:text-2xl font-bold text-[#00473e] leading-snug mb-4">
                        キャリフレ（サマリーPDF）
                    </h3>

                    <div class="space-y-6 text-[14px] md:text-[15px] text-dim leading-relaxed">
                        <div class="sample-leftbar pl-3">
                            <div class="text-[#00473e] font-semibold text-[14px] md:text-[15px] mb-1">あなたの価値観リスト</div>
                        <div class="leading-relaxed">
                            ●「安心できる人間関係」<br>
                            ●「成果より成長の実感」<br>
                            ●「無視されないこと・声が届くこと」
                        </div>
                    </div>

                    <div class="sample-leftbar pl-3">
                        <div class="text-[#00473e] font-semibold text-[14px] md:text-[15px] mb-1">あなたの強み</div>
                        <div class="leading-relaxed">
                            ・相手の気持ちを先に受け止める聞き方ができる<br>
                            ・混乱している現場を落ち着かせる役割を取れる
                        </div>
                    </div>

                    <div class="sample-leftbar pl-3">
                        <div class="text-[#00473e] font-semibold text-[14px] md:text-[15px] mb-1">次の90日アクション</div>
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
         PROGRAM / 3回のセッション
    ========================== -->
    <section id="program" class="px-6 md:px-8 section-pad bg-white/0">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12 md:mb-16">
                <h2 class="text-2xl md:text-3xl font-bold brand-headline leading-tight mb-4">
                    3回のセッションでやること
                </h2>
                <p class="text-lg md:text-xl text-dim leading-relaxed">
                    あなたの「原点」から「これからの意思決定」まで、順番に棚卸します。
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">

                <!-- Session 1 / 過去 -->
                <div class="card-base p-6 flex flex-col bg-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-xs md:text-sm font-semibold badge-step rounded-full px-3 py-1">
                            第1回：過去
                        </div>
                        <div class="text-right text-[11px] md:text-[12px] font-medium text-dim">
                            テーマ：価値観
                        </div>
                    </div>

                    <h3 class="text-lg md:text-xl font-bold text-[#00473e] leading-snug mb-3">
                        「私は何を大切にしてきたのか？」
                    </h3>

                    <p class="text-base md:text-lg text-dim leading-relaxed mb-4">
                        人生のターニングポイントをたどり、
                        何に喜び／怒り／悔しさを感じてきたかを言語化します。
                    </p>

                    <ul class="text-base text-[#1f2e2c] space-y-2 mb-6">
                        <li class="flex items-start gap-2">
                            <span class="text-[#faae2b] font-bold flex-shrink-0 mt-0.5">・</span>
                            <span class="leading-relaxed">人生史（モチベーショングラフ）</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#faae2b] font-bold flex-shrink-0 mt-0.5">・</span>
                            <span class="leading-relaxed">「譲れない価値観」の抽出</span>
                        </li>
                    </ul>

                    <div class="mt-auto text-[12px] md:text-[13px] text-dim leading-relaxed border-t border-gray-200 pt-4">
                        目的：<br>
                        「これは私の軸だ」と胸を張って言える拠りどころを見つける
                    </div>
                </div>

                <!-- Session 2 / 現在 -->
                <div class="card-base p-6 flex flex-col bg-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-xs md:text-sm font-semibold badge-step rounded-full px-3 py-1">
                            第2回：現在
                        </div>
                        <div class="text-right text-[11px] md:text-[12px] font-medium text-dim">
                            テーマ：強みと環境
                        </div>
                    </div>

                    <h3 class="text-lg md:text-xl font-bold text-[#00473e] leading-snug mb-3">
                        「私はなぜ、いまこの仕事をしているのか？」
                    </h3>

                    <p class="text-base md:text-lg text-dim leading-relaxed mb-4">
                        自分の強み・役割・評価されているポイントを棚卸しし、
                        今の環境とのズレや満足度も見える化します。
                    </p>

                    <ul class="text-base text-[#1f2e2c] space-y-2 mb-6">
                        <li class="flex items-start gap-2">
                            <span class="text-[#faae2b] font-bold flex-shrink-0 mt-0.5">・</span>
                            <span class="leading-relaxed">強みの棚卸し（他者から見た価値）</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#faae2b] font-bold flex-shrink-0 mt-0.5">・</span>
                            <span class="leading-relaxed">現職満足度診断</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#faae2b] font-bold flex-shrink-0 mt-0.5">・</span>
                            <span class="leading-relaxed">働く目的を5つの要素に分解し言語化</span>
                        </li>
                    </ul>

                    <div class="mt-auto text-[12px] md:text-[13px] text-dim leading-relaxed border-t border-gray-200 pt-4">
                        目的：<br>
                        「このまま続ける/変えるなら、何を守りたいのか？」をはっきりさせる
                    </div>
                </div>

                <!-- Session 3 / 未来 -->
                <div class="card-base p-6 flex flex-col bg-white">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-xs md:text-sm font-semibold badge-step rounded-full px-3 py-1">
                            第3回：未来
                        </div>
                        <div class="text-right text-[11px] md:text-[12px] font-medium text-dim">
                            テーマ：これから
                        </div>
                    </div>

                    <h3 class="text-lg md:text-xl font-bold text-[#00473e] leading-snug mb-3">
                        「これから、どう在りたい？」
                    </h3>

                    <p class="text-base md:text-lg text-dim leading-relaxed mb-4">
                        Will / Can / Mustフレームで
                        “理想のあり方”と“現実的にやること”をつなげ、
                        90日間のアクションプランまで落とし込みます。
                    </p>

                    <ul class="text-base text-[#1f2e2c] space-y-2 mb-6">
                        <li class="flex items-start gap-2">
                            <span class="text-[#faae2b] font-bold flex-shrink-0 mt-0.5">・</span>
                            <span class="leading-relaxed">WILL / CAN / MUST シート</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#faae2b] font-bold flex-shrink-0 mt-0.5">・</span>
                            <span class="leading-relaxed">キャリアポートフォリオ仮案</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#faae2b] font-bold flex-shrink-0 mt-0.5">・</span>
                            <span class="leading-relaxed">次の90日アクションプラン</span>
                        </li>
                    </ul>

                    <div class="mt-auto text-[12px] md:text-[13px] text-dim leading-relaxed border-t border-gray-200 pt-4">
                        目的：<br>
                        ただ悩む段階を卒業し、「私はこう進む」に言い換える
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- =========================
         VALUES / 私たちが大事にしていること
    ========================== -->
    <section class="px-6 md:px-8 section-pad">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12 md:mb-16">
                <h2 class="text-2xl md:text-3xl font-bold brand-headline leading-tight mb-4">
                    キャリフレが大事にしていること
                </h2>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16">
            <div class="space-y-8">

                <div class="space-y-6 text-base md:text-lg leading-relaxed text-[#1f2e2c]">

                    <div class="p-4 rounded-lg bg-white border border-[#faae2b] shadow-sm">
                        <div class="font-semibold text-[#00473e] mb-2 text-base md:text-lg">1. 答えを与えない</div>
                        <div class="text-dim text-[14px] md:text-[15px] leading-relaxed">
                            私たちの役割は「あなたの中の答え」を見えるようにすること。
                            決めつけたり、特定の選択を押しつけたりはしません。
                        </div>
                    </div>

                    <div class="p-4 rounded-lg bg-white border border-[#faae2b] shadow-sm">
                        <div class="font-semibold text-[#00473e] mb-2 text-base md:text-lg">2. "今のあなた"を否定しない</div>
                        <div class="text-dim text-[14px] md:text-[15px] leading-relaxed">
                            今の環境で耐えてきたことも、守ってきたものも、ちゃんと意味がある。
                            「がまんしてきた自分」を置き去りにしません。
                        </div>
                    </div>

                    <div class="p-4 rounded-lg bg-white border border-[#faae2b] shadow-sm">
                        <div class="font-semibold text-[#00473e] mb-2 text-base md:text-lg">3. 言葉に残す</div>
                        <div class="text-dim text-[14px] md:text-[15px] leading-relaxed">
                            ふわっとした自己理解では終わらせません。
                            あなたの強み・価値観・次の一歩を、あとで読み返せる形（PDF）で手元に残します。
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-base p-6 md:p-8 bg-white border border-[#faae2b]">
                <h3 class="text-xl md:text-2xl font-bold text-[#00473e] leading-snug mb-4">
                    セッション後に残るもの
                </h3>
                <ul class="text-base md:text-lg space-y-5 text-[#1f2e2c] leading-relaxed">
                    <li class="flex flex-col">
                        <span class="font-semibold text-[#00473e]">● あなたの価値観マップ</span>
                        <span class="text-dim text-[14px] md:text-[15px] leading-relaxed">
                            「これだけは手放したくないもの」が明文化されます。
                        </span>
                    </li>
                    <li class="flex flex-col">
                        <span class="font-semibold text-[#00473e]">● 強みの使いどころ</span>
                        <span class="text-dim text-[14px] md:text-[15px] leading-relaxed">
                            今の職場で活かす/別の環境に持ち出す、その両面の視点。
                        </span>
                    </li>
                    <li class="flex flex-col">
                        <span class="font-semibold text-[#00473e]">● 次の90日のアクション</span>
                        <span class="text-dim text-[14px] md:text-[15px] leading-relaxed">
                            いきなり人生を変えるのではなく、「まずこれをやる」まで落とします。
                        </span>
                    </li>
                </ul>

                <div class="mt-6 text-[12px] md:text-[13px] text-dim leading-relaxed border-t border-gray-200 pt-4">
                    つまり、悩み続ける時間を「進んでいる実感がある時間」に置き換えることが目的です。
                </div>
            </div>
        </div>
    </section>

    <!-- =========================
         FINAL CTA
    ========================== -->
    <section class="px-6 md:px-8 section-pad bg-white/60">
        <div class="max-w-7xl mx-auto">
            <div class="max-w-3xl mx-auto">
                <div class="card-base bg-white text-center p-8 md:p-12 border border-[#faae2b]">
            <h2 class="text-xl md:text-2xl font-bold brand-headline leading-snug mb-4">
                あなたのキャリア、次はどこへ進みたいですか？
            </h2>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-6 py-3 rounded-lg font-semibold text-lg accent-bg accent-text shadow-md hover:opacity-90 transition">
                        無料で始める
                    </a>
        @endif

                <a href="#top" class="inline-flex justify-center items-center px-6 py-3 rounded-lg font-semibold text-lg bg-white text-[#00473e] shadow-md border border-[#faae2b] hover:bg-[#fff9eb] transition">
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
