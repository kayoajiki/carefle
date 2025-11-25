<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        <style>
            * { font-family: 'Noto Sans JP', ui-sans-serif, system-ui, sans-serif; }
            body { background-color: #F0F7FF; color: #1E3A5F; }
            .brand-headline { color: #2E5C8A; }
            .text-dim { color: rgba(30, 58, 95, 0.75); }
            .accent-bg { background-color: #6BB6FF; }
            .accent-text { color: #2E5C8A; }
        </style>
    </head>
    <body class="min-h-screen bg-[#F0F7FF] antialiased">
        {{ $slot }}
        @fluxScripts
    </body>
    </html>


