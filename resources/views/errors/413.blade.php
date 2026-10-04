<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload too large | ReadArena</title>
    <style>
        body { margin: 0; font: 16px/1.6 system-ui, sans-serif; background: #f4ebd8; color: #24150d; }
        main { max-width: 38rem; margin: 12vh auto; padding: 2rem; border: 1px solid #d8c9ad; border-radius: 1.5rem; background: #fbf6ea; }
        .eyebrow { color: #b98a2c; font-size: .8rem; font-weight: 700; letter-spacing: .2em; text-transform: uppercase; }
        h1 { margin: .5rem 0; font-family: Georgia, serif; font-size: 2rem; }
        a { display: inline-block; margin-top: 1rem; padding: .75rem 1.25rem; border-radius: 999px; background: #1b0d05; color: #f4ebd8; text-decoration: none; }
    </style>
</head>
<body class="min-h-screen bg-[#F4EBD8] px-6 py-16 text-[#24150D]">
    <main>
        <p class="eyebrow">Upload limit reached</p>
        <h1>This upload is too large.</h1>
        <p>{{ $message }}</p>
        <a href="/admin/books">Return to book uploads</a>
    </main>
</body>
</html>
