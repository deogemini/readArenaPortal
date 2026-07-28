<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReadArena API Documentation</title>
    <link rel="stylesheet" href="{{ asset('vendor/swagger-api/swagger-ui/dist/swagger-ui.css') }}">
    <style>
        body { margin: 0; background: #f8fafc; }
        .swagger-ui .topbar { display: none; }
    </style>
</head>
<body>
<div id="swagger-ui"></div>
<script src="{{ asset('vendor/swagger-api/swagger-ui/dist/swagger-ui-bundle.js') }}"></script>
<script src="{{ asset('vendor/swagger-api/swagger-ui/dist/swagger-ui-standalone-preset.js') }}"></script>
<script>
    window.onload = function () {
        SwaggerUIBundle({
            url: '{{ url('/docs/api-docs.json') }}',
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [SwaggerUIBundle.presets.apis, SwaggerUIStandalonePreset],
            layout: 'BaseLayout',
        });
    };
</script>
</body>
</html>
