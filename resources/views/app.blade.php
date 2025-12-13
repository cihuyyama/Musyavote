<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="light"
    style="color-scheme: light;"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Force light background (anti flash dark) --}}
        <style>
            html {
                background-color: hsl(0 0% 100%);
                color-scheme: light;
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/logo.png" sizes="any">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link
            href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600"
            rel="stylesheet"
        />

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>

    <body class="font-sans antialiased bg-background text-foreground">
        @inertia
    </body>
</html>
