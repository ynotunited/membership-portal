<?php
$pageTitle    = 'Privacy Policy';
$pageCategory = 'Legal';
$pageIcon     = 'ri-shield-user-line';
$lastUpdated  = 'July 6, 2025';

$toc = '
<a href="#collection"   class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">1. Information We Collect</a>
<a href="#use"          class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">2. How We Use It</a>
<a href="#sharing"      class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">3. Sharing</a>
<a href="#retention"    class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">4. Retention</a>
<a href="#security"     class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">5. Security</a>
<a href="#rights"       class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">6. Your Rights</a>
<a href="#cookies"      class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">7. Cookies</a>
<a href="#children"     class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">8. Children</a>
<a href="#changes"      class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">9. Changes</a>
<a href="#contact"      class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">10. Contact</a>';

ob_start();
?>
<p>Global Apex Farmers Cooperative Nigeria Limited (<strong>"GAFCONL"</strong>, <strong>"we"</strong>, <strong>"our"</strong>) operates the 24/7 Registration Portal (<strong>"Portal"</strong>). This Privacy Policy explains how we collect, use, and protect personal information you provide when using the Portal. By registering or logging in, you agree to this policy.</p>

<h2 id="collection">1. Information We Collect</h2>
<h3>1.1 Information you give us directly</h3>
<ul>
    <li>Full name, title, date of birth, gender, marital status</li>
    <li>Contact details: email address, phone number, WhatsApp number</li>
    <li>Residential address, LGA, state, country</li>
    <li>Business information: business name, address, nature of business, sub-sector</li>
    <li>Identity documents: NIN, passport, driver's licence (number and scan)</li>
    <li>Bank account details used for cooperative payments</li>
    <li>Photographs, signatures, and identity card images</li>
    <li>Password (stored as a bcrypt hash — we never store plaintext passwords)</li>
</ul>
<h3>1.2 Information collected automatically</h3>
<ul>
    <li>IP address and approximate location at the time of each login</li>
    <li>Browser user-agent and device type</li>
    <li>Session activity timestamps</li>
    <li>Payment transaction references and gateway responses (not full card data)</li>
</ul>
<h3>1.3 Information from third parties</h3>
<ul>
    <li>Payment status confirmations from Paystack, Monify, and OPay</li>
</ul>

<h2 id="use">2. How We Use Your Information</h2>
<ul>
    <li><strong>Membership management:</strong> issuing membership numbers, tracking dues, shares and thrift savings</li>
    <li><strong>Payment processing:</strong> initiating and verifying transactions with our payment partners</li>
    <li><strong>Communications:</strong> sending membership confirmations, payment receipts, event notices, and important cooperative announcements</li>
    <li><strong>Security:</strong> detecting fraud, rate-limiting login attempts, and auditing access</li>
    <li><strong>Legal compliance:</strong> meeting obligations under Nigerian data protection law (NDPR 2019, NDPA 2023)</li>
    <li><strong>Analytics:</strong> internal reporting on membership growth and financial performance — no data is sold or used for advertising</li>
</ul>

<h2 id="sharing">3. Sharing Your Information</h2>
<p>We do not sell, rent, or trade your personal data. We share it only:</p>
<ul>
    <li><strong>With payment processors</strong> (Paystack, Monify, OPay) to facilitate transactions — governed by their own privacy policies</li>
    <li><strong>With authorised cooperative officers</strong> who have a legitimate need (e.g. financial secretary verifying dues)</li>
    <li><strong>As required by law</strong> — regulatory bodies, court orders, or law enforcement with proper legal authority</li>
    <li><strong>With email service providers</strong> for transactional mail delivery only</li>
</ul>

<h2 id="retention">4. Data Retention</h2>
<ul>
    <li>Active member records are retained for the duration of membership plus <strong>7 years</strong> after termination, as required for financial record-keeping under Nigerian law</li>
    <li>Payment ledger entries are <strong>never deleted</strong> (immutable audit trail)</li>
    <li>Security logs (login attempts, audit events) are retained for <strong>90 days</strong></li>
    <li>Password reset tokens expire within <strong>1 hour</strong></li>
    <li>Session data expires after <strong>8 hours</strong> of absolute inactivity or <strong>30 minutes</strong> of idle time</li>
</ul>

<h2 id="security">5. Security Measures</h2>
<ul>
    <li>Passwords hashed with bcrypt (cost factor 12)</li>
    <li>All connections encrypted with TLS 1.2+</li>
    <li>Role-based access control — only authorised staff see sensitive data</li>
    <li>Rate limiting on login, registration, and payment endpoints</li>
    <li>CSRF tokens on all state-changing forms</li>
    <li>Immutable payment ledger prevents retroactive tampering</li>
    <li>Webhook signatures validated via HMAC-SHA512 before processing</li>
</ul>
<p>Despite these measures, no system is 100% secure. If you believe your account has been compromised, contact us immediately at <a href="mailto:info@globalapexfarmers.org.ng">info@globalapexfarmers.org.ng</a>.</p>

<h2 id="rights">6. Your Rights</h2>
<p>Under the Nigeria Data Protection Act 2023 (NDPA) you have the right to:</p>
<ul>
    <li><strong>Access</strong> — request a copy of the personal data we hold about you</li>
    <li><strong>Correction</strong> — update inaccurate or incomplete data via your profile page</li>
    <li><strong>Deletion</strong> — request erasure where we have no legal obligation to retain it</li>
    <li><strong>Objection</strong> — object to processing for non-essential purposes</li>
    <li><strong>Portability</strong> — receive your data in a machine-readable format</li>
    <li><strong>Withdraw consent</strong> — where processing is based on consent, you may withdraw at any time</li>
</ul>
<p>To exercise any of these rights, email <a href="mailto:info@globalapexfarmers.org.ng">info@globalapexfarmers.org.ng</a> with subject "Data Rights Request". We will respond within 30 days.</p>

<h2 id="cookies">7. Cookies</h2>
<p>We use only essential session cookies to maintain your login state. We do not use advertising, tracking, or third-party analytics cookies. You may disable cookies in your browser, but the Portal will not function without the session cookie.</p>

<h2 id="children">8. Children's Privacy</h2>
<p>The Portal is intended for adults 18 and over. We do not knowingly collect data from anyone under 18. If you believe a minor has registered, contact us immediately and we will remove the account.</p>

<h2 id="changes">9. Changes to This Policy</h2>
<p>We may update this policy to reflect changes in law or our practices. Material changes will be notified by email at least 14 days before they take effect. Continued use of the Portal after the effective date constitutes acceptance of the revised policy.</p>

<h2 id="contact">10. Contact Us</h2>
<ul>
    <li><strong>Organisation:</strong> Global Apex Farmers Cooperative Nigeria Limited</li>
    <li><strong>Email:</strong> <a href="mailto:info@globalapexfarmers.org.ng">info@globalapexfarmers.org.ng</a></li>
    <li><strong>Website:</strong> <a href="https://globalapexfarmers.org.ng">globalapexfarmers.org.ng</a></li>
</ul>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
