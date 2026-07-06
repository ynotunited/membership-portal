<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> – GAFCONL 24/7 Registration Portal</title>
    <link rel="icon" type="image/png" href="<?= \App\Helpers\Url::appUrl() ?>/uploads/gafconl-favicon.png">
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { primary: '#408100', secondary: '#BB1F1F', tertiary: '#02037B' }, fontFamily: { 'sans': ['Inter','ui-sans-serif','system-ui','-apple-system','sans-serif'] } } }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
        .prose h2 { font-size:1.25rem; font-weight:700; color:#1a202c; margin-top:2rem; margin-bottom:.75rem; padding-bottom:.5rem; border-bottom:2px solid #408100; }
        .prose h3 { font-size:1.05rem; font-weight:600; color:#2d3748; margin-top:1.5rem; margin-bottom:.5rem; }
        .prose p  { color:#4a5568; line-height:1.8; margin-bottom:1rem; }
        .prose ul { list-style:disc; padding-left:1.5rem; color:#4a5568; margin-bottom:1rem; }
        .prose ul li { margin-bottom:.4rem; line-height:1.7; }
        .prose a  { color:#408100; text-decoration:underline; }
        .prose strong { color:#2d3748; }
        .toc-link:hover { color:#408100; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

<!-- Top nav -->
<nav class="bg-white border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="<?= \App\Helpers\Url::appUrl() ?>/login" class="flex items-center gap-3">
            <img src="<?= \App\Helpers\Url::appUrl() ?>/uploads/gafconl_white.png"
                 onerror="this.style.display='none'"
                 alt="GAFCONL" class="h-8">
            <span class="font-semibold text-primary text-sm hidden sm:block">24/7 Registration Portal</span>
        </a>
        <a href="<?= \App\Helpers\Url::appUrl() ?>/login"
           class="flex items-center gap-1 text-sm text-gray-600 hover:text-primary transition-colors">
            <i class="ri-arrow-left-line"></i> Back to Login
        </a>
    </div>
</nav>

<!-- Page header -->
<header class="bg-gradient-to-r from-primary to-green-700 text-white py-12 px-4">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center gap-3 mb-3">
            <i class="<?= htmlspecialchars($pageIcon) ?> text-3xl text-white/80"></i>
            <span class="text-sm uppercase tracking-widest text-white/70 font-medium"><?= htmlspecialchars($pageCategory) ?></span>
        </div>
        <h1 class="text-3xl font-bold mb-2"><?= htmlspecialchars($pageTitle) ?></h1>
        <p class="text-white/80 text-sm">Last updated: <?= htmlspecialchars($lastUpdated) ?></p>
    </div>
</header>

<!-- Content -->
<main class="max-w-5xl mx-auto px-4 py-10 grid grid-cols-1 lg:grid-cols-4 gap-8">
    <!-- TOC sidebar -->
    <aside class="hidden lg:block col-span-1">
        <div class="sticky top-20 bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs uppercase tracking-widest text-gray-400 font-semibold mb-3">Contents</p>
            <nav class="space-y-1 text-sm" id="toc">
                <?= $toc ?? '' ?>
            </nav>
        </div>
        <!-- Legal links -->
        <div class="mt-4 bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs uppercase tracking-widest text-gray-400 font-semibold mb-3">Legal</p>
            <nav class="space-y-2 text-sm">
                <a href="<?= \App\Helpers\Url::appUrl() ?>/legal/privacy-policy"    class="block toc-link text-gray-600 hover:text-primary transition-colors">Privacy Policy</a>
                <a href="<?= \App\Helpers\Url::appUrl() ?>/legal/terms-of-use"      class="block toc-link text-gray-600 hover:text-primary transition-colors">Terms of Use</a>
                <a href="<?= \App\Helpers\Url::appUrl() ?>/legal/data-compliance"   class="block toc-link text-gray-600 hover:text-primary transition-colors">Data & Compliance</a>
                <a href="<?= \App\Helpers\Url::appUrl() ?>/legal/ip-infringement"   class="block toc-link text-gray-600 hover:text-primary transition-colors">IP Infringement</a>
            </nav>
        </div>
    </aside>

    <!-- Main prose -->
    <article class="col-span-1 lg:col-span-3 bg-white rounded-xl border border-gray-200 p-8 prose">
        <?= $content ?>
    </article>
</main>

<!-- Footer -->
<footer class="border-t border-gray-200 bg-white mt-8 py-6 text-center text-xs text-gray-400">
    <div class="max-w-5xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
        <p>© <?= date('Y') ?> Global Apex Farmers Cooperative Nigeria Ltd. All rights reserved.</p>
        <div class="flex gap-4">
            <a href="<?= \App\Helpers\Url::appUrl() ?>/legal/privacy-policy"  class="hover:text-primary transition-colors">Privacy</a>
            <a href="<?= \App\Helpers\Url::appUrl() ?>/legal/terms-of-use"    class="hover:text-primary transition-colors">Terms</a>
            <a href="<?= \App\Helpers\Url::appUrl() ?>/legal/data-compliance" class="hover:text-primary transition-colors">Compliance</a>
            <a href="<?= \App\Helpers\Url::appUrl() ?>/legal/ip-infringement" class="hover:text-primary transition-colors">IP Policy</a>
        </div>
    </div>
</footer>

</body>
</html>
