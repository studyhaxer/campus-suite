<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Campus Suite' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,500;0,600;0,700;1,600&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        ::selection { background: #C9A227; color: #14213D; }
        :focus-visible { outline: 2px solid #C9A227; outline-offset: 2px; }
        .bg-grid {
            background-image:
                linear-gradient(to right, rgba(250,249,246,0.06) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(250,249,246,0.06) 1px, transparent 1px);
            background-size: 32px 32px;
        }
        [wire\:loading] { display: none; }
        @media (prefers-reduced-motion: reduce) {
            * { animation-duration: 0.001ms !important; animation-iteration-count: 1 !important; transition-duration: 0.001ms !important; }
        }
    </style>

    @livewireStyles
</head>
<body class="bg-parchment text-ink font-sans antialiased">

    {{ $slot }}

    @livewireScripts
</body>
</html>