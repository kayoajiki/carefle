<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <style>
            * {
                font-family: 'Noto Sans JP', ui-sans-serif, system-ui, sans-serif;
            }

            body {
                background-color: #F0F7FF;
                color: #1E3A5F;
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

            .card-base {
                background-color: #ffffff;
                border-radius: 1rem;
                box-shadow: 0 10px 25px -5px rgba(74, 144, 226, 0.1), 0 4px 6px -2px rgba(74, 144, 226, 0.05);
            }
        </style>
    </head>
    <body class="min-h-screen bg-[#F0F7FF] antialiased selection:bg-blue-200/60 selection:text-[#2E5C8A]">
        <div class="flex min-h-screen flex-col items-center justify-center gap-6 px-4 py-8 md:px-10">
            <div class="w-full max-w-md">
                <!-- Logo / Header -->
                <div class="flex flex-col items-center gap-4 mb-8">
                    <a href="{{ route('home') }}" class="flex flex-col items-center gap-2" wire:navigate>
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-2 h-2 rounded-full bg-[#6BB6FF] shadow-sm"></span>
                            <span class="text-xl md:text-2xl font-bold brand-headline">キャリフレ</span>
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
