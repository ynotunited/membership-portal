<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= \App\Helpers\Url::appUrl() ?>/uploads/gafconl-favicon.png">

    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
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
    <style>
        .form-input:focus {
            border-color: #408100;
            box-shadow: 0 0 0 3px rgba(64, 129, 0, 0.1);
        }
    </style>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-center text-gray-900">Reset Your Password</h2>
        <form class="space-y-6" method="post" action="<?php echo \App\Helpers\Url::appUrl(); ?>/reset-password">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                <input type="password" name="password" id="password" required
                    class="form-input w-full px-4 py-3 mt-1 border border-gray-300 rounded-lg focus:outline-none"
                    placeholder="Enter new password">
            </div>
            <div>
                <label for="confirmPassword" class="block text-sm font-medium text-gray-700">Confirm New
                    Password</label>
                <input type="password" name="confirmPassword" id="confirmPassword" required
                    class="form-input w-full px-4 py-3 mt-1 border border-gray-300 rounded-lg focus:outline-none"
                    placeholder="Confirm new password">
            </div>
            <button type="submit"
                class="w-full py-3 px-4 font-semibold text-white bg-primary rounded-lg hover:bg-secondary focus:outline-none">Reset
                Password</button>
        </form>
    </div>
</body>

</html>