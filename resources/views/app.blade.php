<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system or saved dark mode preference and apply it immediately --}}
        <script>
            (function() {
                try {
                    const stored = localStorage.getItem('appearance');
                    if (stored === 'dark') {
                        document.documentElement.classList.add('dark');
                    } else if (stored === 'light') {
                        document.documentElement.classList.remove('dark');
                    } else {
                        const appearance = '{{ $appearance ?? "system" }}';
                        if (appearance === 'dark' || (appearance === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                            document.documentElement.classList.add('dark');
                        } else {
                            document.documentElement.classList.remove('dark');
                        }
                    }
                } catch (e) {}
            })();
        </script>

        {{-- Inline style to set the HTML background color and lock viewport height --}}
        <style>
            html, body {
                height: 100%;
                margin: 0;
                padding: 0;
                background-color: #F8FAFC;
            }
            html.dark, html.dark body {
                background-color: #0B1120;
                color: #F1F5F9;
            }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
