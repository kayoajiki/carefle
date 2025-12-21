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
                    自己理解と内省の旅を始める「キャリフレ」
                </div>

                <!-- キャリくま -->
                <div class="flex justify-center">
                    <div class="w-24 h-24 md:w-32 md:h-32 lg:w-36 lg:h-36 max-w-[144px] max-h-[144px]">
                        <img 
                            src="{{ asset('images/carekuma/carekuma-full.png') }}" 
                            alt="キャリくま" 
                            class="w-full h-full object-contain"
                            loading="eager"
                            style="max-width: 100%; max-height: 100%;"
                            onerror="this.onerror=null; this.src='{{ asset('images/carekuma/carekuma-full.jpg') }}';"
                        />
                    </div>
                </div>

                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold leading-tight brand-headline">
                    自分を知る旅が、ここから始まります。
                </h1>

                <p class="text-lg md:text-xl leading-relaxed text-dim max-w-4xl mx-auto">
                    診断・記録・内省・行動計画で、あなたの「今」を可視化し、「これから」を具体化します。<br class="hidden md:block">
                    自分を知る旅を、今日から始めませんか？
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

    {{-- =========================
         CAREEKUMA COMIC / キャリくまの説明漫画
    ========================== --}}
    {{-- <section id="carekuma-comic" class="px-6 md:px-8 pt-8 pb-12 md:pt-12 md:pb-16 bg-white/0">
        <div class="max-w-4xl mx-auto">
            <div class="card-base p-4 md:p-6 bg-white border border-[#6BB6FF]">
                <img 
                    src="{{ asset('images/carekuma/carekuma-comic.png') }}" 
                    alt="キャリくまの説明漫画 - キャリフレの使い方をキャリくまが紹介します" 
                    class="w-full h-auto rounded-lg"
                    loading="lazy"
                    onerror="this.onerror=null; this.src='{{ asset('images/carekuma/carekuma-comic.jpg') }}';"
                />
            </div>
        </div>
    </section> --}}

    <!-- =========================
         HOW IT WORKS / 使い方の流れ
    ========================== -->
    <section id="how-it-works" class="px-6 md:px-8 section-pad bg-white/60">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12 md:mb-16">
                <h2 class="text-2xl md:text-3xl font-bold brand-headline leading-tight mb-4">
                    診断・記録・内省・行動計画。<br class="hidden md:block">
                    すべてがあなたの手の中に。
                </h2>
                <p class="text-lg md:text-xl text-dim leading-relaxed max-w-3xl mx-auto">
                    自分を知る → 記録する → 内省する → 行動する<br class="hidden md:block">
                    4つのステップで、あなたの自己理解が深まります。
                </p>
            </div>

            <!-- 視覚的なフロー -->
            <div class="flex flex-col md:flex-row items-center justify-center gap-4 md:gap-6 mb-12">
                <!-- Step 1 -->
                <div class="card-base p-6 bg-white border-2 border-[#6BB6FF] text-center flex-1 max-w-xs">
                    <div class="text-[#6BB6FF] text-3xl mb-3">📊</div>
                    <div class="text-xs font-semibold badge-step rounded-full px-3 py-1 mb-3 w-fit mx-auto">STEP 1</div>
                    <h3 class="font-bold text-[#2E5C8A] text-lg mb-2">診断する</h3>
                    <p class="text-dim text-sm mb-2">「今の自分」を可視化</p>
                    <p class="text-dim text-xs">現職満足度診断と自己診断</p>
                </div>

                <!-- 矢印 -->
                <div class="text-[#6BB6FF] text-3xl hidden md:block">→</div>
                <div class="text-[#6BB6FF] text-2xl md:hidden">↓</div>

                <!-- Step 2 -->
                <div class="card-base p-6 bg-white border-2 border-[#6BB6FF] text-center flex-1 max-w-xs">
                    <div class="text-[#6BB6FF] text-3xl mb-3">📝</div>
                    <div class="text-xs font-semibold badge-step rounded-full px-3 py-1 mb-3 w-fit mx-auto">STEP 2</div>
                    <h3 class="font-bold text-[#2E5C8A] text-lg mb-2">記録する</h3>
                    <p class="text-dim text-sm mb-2">「過去と現在」を言語化</p>
                    <p class="text-dim text-xs">日記・人生史・マイルストーン</p>
                </div>

                <!-- 矢印 -->
                <div class="text-[#6BB6FF] text-3xl hidden md:block">→</div>
                <div class="text-[#6BB6FF] text-2xl md:hidden">↓</div>

                <!-- Step 3 -->
                <div class="card-base p-6 bg-white border-2 border-[#6BB6FF] text-center flex-1 max-w-xs">
                    <div class="text-[#6BB6FF] text-3xl mb-3">💭</div>
                    <div class="text-xs font-semibold badge-step rounded-full px-3 py-1 mb-3 w-fit mx-auto">STEP 3</div>
                    <h3 class="font-bold text-[#2E5C8A] text-lg mb-2">内省する</h3>
                    <p class="text-dim text-sm mb-2">深い気づきを得る</p>
                    <p class="text-dim text-xs">AIフィードバックと振り返り</p>
                        </div>

                <!-- 矢印 -->
                <div class="text-[#6BB6FF] text-3xl hidden md:block">→</div>
                <div class="text-[#6BB6FF] text-2xl md:hidden">↓</div>

                <!-- Step 4 -->
                <div class="card-base p-6 bg-white border-2 border-[#6BB6FF] text-center flex-1 max-w-xs">
                    <div class="text-[#6BB6FF] text-3xl mb-3">🎯</div>
                    <div class="text-xs font-semibold badge-step rounded-full px-3 py-1 mb-3 w-fit mx-auto">STEP 4</div>
                    <h3 class="font-bold text-[#2E5C8A] text-lg mb-2">行動する</h3>
                    <p class="text-dim text-sm mb-2">「未来」を具体化</p>
                    <p class="text-dim text-xs">WCMシートとマイルストーン</p>
                        </div>
                    </div>

            <!-- 得られるもの -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8 max-w-5xl mx-auto">
                <div class="card-base p-6 bg-white border border-[#6BB6FF]">
                    <div class="text-[#2E5C8A] font-bold text-xl mb-3">● あなたが大事にしてきた価値観</div>
                    <p class="text-dim text-sm leading-relaxed">
                        どんな場面で心が動き、何を守ろうとしてきたのか。
                    </p>
                </div>

                <div class="card-base p-6 bg-white border border-[#6BB6FF]">
                    <div class="text-[#2E5C8A] font-bold text-xl mb-3">● 強みと活かし方</div>
                    <p class="text-dim text-sm leading-relaxed">
                        「今どこで頼りにされているのか」「どんな環境ならもっと活きるのか」。
                    </p>
                </div>

                <div class="card-base p-6 bg-white border border-[#6BB6FF]">
                    <div class="text-[#2E5C8A] font-bold text-xl mb-3">● Will / Can / Must</div>
                    <p class="text-dim text-sm leading-relaxed">
                        「こう在りたい」を軸に、次の90日で実際に動くアクションプラン。
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- =========================
         WHY CAREFLE / キャリフレの価値
    ========================== -->
    <section id="why-carefle" class="px-6 md:px-8 section-pad bg-white/60">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12 md:mb-16">
                <h2 class="text-2xl md:text-3xl font-bold brand-headline leading-tight mb-4">
                    自分で気づくツールを提供します
                </h2>
                <p class="text-lg md:text-xl text-dim leading-relaxed max-w-3xl mx-auto">
                    答えを与えるのではなく、あなたの中の答えを見えるようにする。
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                <!-- 左: 価値観 -->
                <div class="space-y-6">
                    <div class="card-base p-6 bg-white border border-[#6BB6FF]">
                        <h3 class="font-semibold text-[#2E5C8A] text-lg mb-3">✓ 自分で気づくツール</h3>
                        <p class="text-dim text-sm leading-relaxed">
                            診断・記録・内省・可視化のツールで、あなたの中の答えを見えるようにします。
                        </p>
                    </div>
                    <div class="card-base p-6 bg-white border border-[#6BB6FF]">
                        <h3 class="font-semibold text-[#2E5C8A] text-lg mb-3">✓ "今のあなた"を否定しない</h3>
                        <p class="text-dim text-sm leading-relaxed">
                            今の環境で耐えてきたことも、守ってきたものも、ちゃんと意味がある。
                        </p>
                    </div>
                    <div class="card-base p-6 bg-white border border-[#6BB6FF]">
                        <h3 class="font-semibold text-[#2E5C8A] text-lg mb-3">✓ データとして蓄積・可視化</h3>
                        <p class="text-dim text-sm leading-relaxed">
                            レーダーチャートやタイムラインで、いつでも振り返ることができます。
                        </p>
                </div>
            </div>

                <!-- 右: メッセージ -->
                <div class="card-base p-6 md:p-8 bg-white border border-[#6BB6FF]">
                    <h3 class="text-xl font-bold text-[#2E5C8A] mb-4">
                        日常的な自己理解の習慣が、あなたを変えます
                </h3>
                    <p class="text-dim text-sm leading-relaxed mb-6">
                        悩み続ける時間を「進んでいる実感がある時間」に置き換える——それがキャリフレの目的です。
                    </p>
                    <div class="soft-panel p-4 text-sm leading-relaxed text-[#2E5C8A]">
                        「転職するべき？」ではなく、<br>
                        「私は何を守りたいから、こう動くのか」まで言葉になります。
                    </div>
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
                自分を知る旅を、今日から始めませんか？
            </h2>

            <p class="text-lg md:text-xl text-dim leading-relaxed mb-6 max-w-2xl mx-auto">
                登録後すぐに、診断・記録・内省・行動計画のツールを無料でご利用いただけます。
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
         CONSULTATION / 相談案内
    ========================== -->
    <section class="px-6 md:px-8 py-8 md:py-10 bg-white/0">
        <div class="max-w-7xl mx-auto">
            <div class="max-w-2xl mx-auto">
                <div class="card-base bg-white/80 text-center p-6 md:p-8 border border-[#6BB6FF]/30">
                    <div class="flex items-center justify-center gap-3 mb-3">
                        <svg class="w-5 h-5 text-[#6BB6FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                        </svg>
                        <p class="text-sm md:text-base text-[#1E3A5F]/80 leading-relaxed">
                            キャリアアドバイザーとの<span class="font-semibold text-[#2E5C8A]">相談</span>も受け付けています
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center text-sm">
                        <a href="https://careerpartner.jp/carehugforI" target="_blank" rel="noopener noreferrer" class="text-[#6BB6FF] hover:text-[#4A90E2] hover:underline transition-colors">
                            面談申し込み
                        </a>
                        <span class="hidden sm:inline text-[#1E3A5F]/40">|</span>
                        <a href="https://line.me/R/ti/p/@824flemq?ts=08191453&oat_content=url" target="_blank" rel="noopener noreferrer" class="text-[#6BB6FF] hover:text-[#4A90E2] hover:underline transition-colors">
                            LINE登録はこちら
                        </a>
                    </div>
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
