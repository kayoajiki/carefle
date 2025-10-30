<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <style>
            * { font-family: 'Noto Sans JP', ui-sans-serif, system-ui, sans-serif; }
            body { background-color: #f2f7f5; color: #1f2e2c; }
            .brand-headline { color: #00473e; }
            .text-dim { color: rgba(31,46,44,0.75); }
            .accent-bg { background-color: #faae2b; }
            .accent-text { color: #00473e; }
        </style>
    </head>
    <body class="min-h-screen bg-[#f2f7f5] antialiased">
        {{ $slot }}
        @fluxScripts
    </body>
    </html>


