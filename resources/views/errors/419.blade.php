<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Session Expired — Rando Jeans</title>
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-ink text-bone font-sans antialiased min-h-screen flex items-center justify-center px-6">
    <div class="text-center max-w-md">
        <p class="font-mono text-9xl text-copper leading-none">419</p>
        <div class="my-8 border-t border-dashed border-neutral-700"></div>
        <h1 class="font-display text-3xl mb-4">Your session timed out</h1>
        <p class="text-neutral-400 mb-10">
            You've been idle a while — for your security, please refresh and try again.
        </p>
        <a href="/" class="inline-block bg-copper hover:bg-copper-light text-ink font-medium px-8 py-4 transition">
            Refresh & Continue
        </a>
    </div>
</body>
</html>