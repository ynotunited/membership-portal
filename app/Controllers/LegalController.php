<?php

namespace App\Controllers;

/**
 * LegalController — serves public legal/compliance pages.
 * No authentication required — these pages must be publicly accessible.
 */
class LegalController extends BaseController
{
    public function privacyPolicy(): void
    {
        require_once __DIR__ . '/../Views/legal/privacy-policy.php';
    }

    public function termsOfUse(): void
    {
        require_once __DIR__ . '/../Views/legal/terms-of-use.php';
    }

    public function dataCompliance(): void
    {
        require_once __DIR__ . '/../Views/legal/data-compliance.php';
    }

    public function ipInfringement(): void
    {
        require_once __DIR__ . '/../Views/legal/ip-infringement.php';
    }
}
