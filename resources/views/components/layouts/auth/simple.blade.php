<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <style>
            * {
                font-family: 'Noto Sans JP', ui-sans-serif, system-ui, sans-serif;
            }

            body {
                background-color: #f2f7f5;
                color: #1f2e2c;
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

            .card-base {
                background-color: #ffffff;
                border-radius: 1rem;
                box-shadow: 0 24px 48px -12px rgba(0,0,0,0.15);
            }
        </style>
    </head>
    <body class="min-h-screen bg-[#f2f7f5] antialiased selection:bg-yellow-200/60 selection:text-[#00473e]">
        <div class="flex min-h-screen flex-col items-center justify-center gap-6 px-4 py-8 md:px-10">
            <div class="w-full max-w-md">
                <!-- Logo / Header -->
                <div class="flex flex-col items-center gap-4 mb-8">
                    <a href="{{ route('home') }}" class="flex flex-col items-center gap-2" wire:navigate>
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-2 h-2 rounded-full bg-[#faae2b] shadow-sm"></span>
                            <span class="text-xl md:text-2xl font-bold brand-headline">キャリアカルテ</span>
                        </div>
                    </a>
                </div>

                <!-- Form Card -->
                <div class="card-base p-8 flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
