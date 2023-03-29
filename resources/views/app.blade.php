<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="This is a demo site of real time chat project.">
    <meta property="og:title" content="Real time chat app">
    <meta property="og:description" content="This is a demo site of real time chat project.">
    <meta property="og:url" content="https://chatdemo.site">
    <meta property="og:image" content="https://chatdemo.site/img/chat.png">
    <meta property="twitter:title" content="Real time chat app">
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:image" content="https://chatdemo.site/img/chat.png">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <link href="/favicon_io/apple-touch-icon.png" rel="apple-touch-icon" sizes="180x180">
    <link type="image/png" href="/favicon_io/favicon-32x32.png" rel="icon" sizes="32x32">
    <link type="image/png" href="/favicon_io/favicon-16x16.png" rel="icon" sizes="16x16">
    <link href="/favicon_io/site.webmanifest" rel="manifest">

    <!-- Fonts -->
    <link href="https://fonts.bunny.net/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
