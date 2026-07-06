<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= \App\Helpers\Url::appUrl() ?>/uploads/gafconl-favicon.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#408100',
                        secondary: '#BB1F1F',
                        tertiary: '#02037B',
                        'primary-light': '#86D400'
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="text-center">
        <div class="mb-8">
            <h1 class="text-9xl font-bold text-gray-300">404</h1>
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">Page Not Found</h2>
            <p class="text-gray-600 mb-8">The page you're looking for doesn't exist.</p>
        </div>

        <div class="space-x-4">
            <a href="/"
                class="inline-block bg-primary text-white px-6 py-3 rounded-lg hover:bg-secondary transition-colors">
                Go Home
            </a>
            <a href="/dashboard"
                class="inline-block bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700 transition-colors">
                Dashboard
            </a>
        </div>
    </div>
</body>

</html>

</html>