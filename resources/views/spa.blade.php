<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ReHome v2 - SPA</title>
    @viteReactRefresh
    @vite(['frontend/src/main.tsx'])
</head>
<body>
    <div id="root"></div>
    <script>
        // Pass Laravel configuration to React
        window.Laravel = {
            csrfToken: '{{ csrf_token() }}',
            baseUrl: '{{ config("app.url") }}',
        };
    </script>
</body>
</html>