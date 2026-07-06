<?php
$pageTitle    = 'Terms of Use';
$pageCategory = 'Legal';
$pageIcon     = 'ri-file-text-line';
$lastUpdated  = 'July 6, 2025';

$toc = '
<a href="#acceptance"   class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">1. Acceptance</a>
<a href="#eligibility"  class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">2. Eligibility</a>
<a href="#account"      class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">3. Account Security</a>
<a href="#conduct"      class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">4. Acceptable Use</a>
<a href="#payments"     class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">5. Payments & Dues</a>
<a href="#content"      class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">6. User Content</a>
<a href="#ip"           class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">7. Intellectual Property</a>
<a href="#termination"  class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">8. Termination</a>
<a href="#liability"    class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">9. Limitation of Liability</a>
<a href="#governing"    class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">10. Governing Law</a>
<a href="#contact"      class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">11. Contact</a>';

ob_start();
?>
<p>These Terms of Use (<strong>"Terms"</strong>) govern your access to and use of the 24/7 Registration Portal (<strong>"Portal"</strong>) operated by Global Apex Farmers Cooperative Nigeria Limited (<strong>"GAFCONL"</strong>, <strong>"we"</strong>). Please read them carefully before registering.</p>

<h2 id="acceptance">1. Acceptance of Terms</h2>
<p>By creating an account or logging in you confirm that you have read, understood, and agree to be bound by these Terms and our <a href="<?= \App\Helpers\Url::appUrl() ?>/legal/privacy-policy">Privacy Policy</a>. If you do not agree, do not use the Portal.</p>

<h2 id="eligibility">2. Eligibility</h2>
<ul>
    <li>You must be at least 18 years old</li>
    <li>You must be a natural person or authorised representative of a business entity</li>
    <li>You must provide accurate, complete, and current registration information</li>
    <li>Membership in GAFCONL is subject to approval by cooperative officers and payment of applicable fees</li>
</ul>

<h2 id="account">3. Account Security</h2>
<ul>
    <li>You are responsible for maintaining the confidentiality of your login credentials</li>
    <li>You must use a strong password (minimum 8 characters including letters, numbers, and special characters)</li>
    <li>You must notify us immediately at <a href="mailto:info@globalapexfarmers.org.ng">info@globalapexfarmers.org.ng</a> if you suspect unauthorised access</li>
    <li>You may not share your account or credentials with any other person</li>
    <li>We are not liable for losses arising from unauthorised account access where you failed to safeguard your credentials</li>
</ul>

<h2 id="conduct">4. Acceptable Use</h2>
<p>You agree <strong>not</strong> to:</p>
<ul>
    <li>Provide false, misleading, or fraudulent registration information</li>
    <li>Attempt to gain unauthorised access to other members' accounts or data</li>
    <li>Use automated bots, scrapers, or scripts against the Portal</li>
    <li>Upload malicious files, viruses, or exploits</li>
    <li>Engage in harassment, hate speech, or abusive conduct in the community forum</li>
    <li>Impersonate cooperative officers, staff, or other members</li>
    <li>Use the Portal for any unlawful purpose under Nigerian law</li>
    <li>Attempt to reverse-engineer, decompile, or tamper with the Portal's source code</li>
</ul>
<p>Violations may result in immediate account suspension and referral to law enforcement.</p>

<h2 id="payments">5. Payments and Dues</h2>
<ul>
    <li>Annual membership dues are set by the cooperative board and communicated to members in advance</li>
    <li>Payments are processed by third-party gateways (Paystack, Monify, OPay) subject to their own terms</li>
    <li>All payment transactions are recorded in an immutable ledger — records cannot be altered after the fact</li>
    <li>Refunds are subject to the cooperative's refund policy and gateway policies; contact us within 30 days of a disputed charge</li>
    <li>We use idempotency keys to prevent duplicate charges on network retries — if you are charged twice for a single transaction, contact us immediately</li>
    <li>We are not responsible for gateway outages or bank processing delays outside our control</li>
</ul>

<h2 id="content">6. User-Generated Content</h2>
<p>The Portal includes a community forum. By posting content you:</p>
<ul>
    <li>Grant GAFCONL a non-exclusive, royalty-free licence to display that content on the Portal</li>
    <li>Confirm the content does not infringe any third-party intellectual property rights</li>
    <li>Accept that we may remove content that violates these Terms or applicable law</li>
</ul>

<h2 id="ip">7. Intellectual Property</h2>
<p>All Portal software, design, logos, and written content are the property of GAFCONL or its licensors. You may not reproduce, distribute, or create derivative works without written permission. See our full <a href="<?= \App\Helpers\Url::appUrl() ?>/legal/ip-infringement">IP Infringement Policy</a>.</p>

<h2 id="termination">8. Termination</h2>
<p>We may suspend or terminate your account without notice if we reasonably believe you have violated these Terms. You may close your account by contacting us; closure does not delete financial records that we are legally required to retain.</p>

<h2 id="liability">9. Limitation of Liability</h2>
<p>To the maximum extent permitted by Nigerian law, GAFCONL shall not be liable for indirect, incidental, or consequential damages arising from use or inability to use the Portal. Our total liability for direct damages shall not exceed the amount you paid in membership dues in the preceding 12 months.</p>

<h2 id="governing">10. Governing Law</h2>
<p>These Terms are governed by the laws of the Federal Republic of Nigeria. Any dispute shall be resolved by the courts of Lagos State, Nigeria, or through binding arbitration under the Lagos Court of Arbitration rules, at GAFCONL's election.</p>

<h2 id="contact">11. Contact</h2>
<ul>
    <li><strong>Email:</strong> <a href="mailto:info@globalapexfarmers.org.ng">info@globalapexfarmers.org.ng</a></li>
    <li><strong>Organisation:</strong> Global Apex Farmers Cooperative Nigeria Limited</li>
</ul>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
