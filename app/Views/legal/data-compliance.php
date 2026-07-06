<?php
$pageTitle    = 'Data & Compliance';
$pageCategory = 'Legal';
$pageIcon     = 'ri-database-2-line';
$lastUpdated  = 'July 6, 2025';

$toc = '
<a href="#framework"   class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">1. Legal Framework</a>
<a href="#lawful"      class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">2. Lawful Basis</a>
<a href="#categories"  class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">3. Data Categories</a>
<a href="#transfers"   class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">4. Cross-Border Transfers</a>
<a href="#breaches"    class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">5. Breach Response</a>
<a href="#dpia"        class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">6. Impact Assessments</a>
<a href="#technical"   class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">7. Technical Controls</a>
<a href="#processors"  class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">8. Third-Party Processors</a>
<a href="#audit"       class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">9. Audit &amp; Logging</a>
<a href="#contact"     class="block toc-link text-gray-600 hover:text-primary py-1 transition-colors">10. Contact DPO</a>';

ob_start();
?>
<p>This page describes how Global Apex Farmers Cooperative Nigeria Limited (<strong>"GAFCONL"</strong>) meets its obligations under applicable data protection legislation and cooperative financial regulations.</p>

<h2 id="framework">1. Legal Framework</h2>
<p>Our data practices are governed by:</p>
<ul>
    <li><strong>Nigeria Data Protection Regulation 2019 (NDPR)</strong> — issued by NITDA</li>
    <li><strong>Nigeria Data Protection Act 2023 (NDPA)</strong> — the primary statute</li>
    <li><strong>Companies and Allied Matters Act (CAMA) 2020</strong> — record-keeping obligations</li>
    <li><strong>Central Bank of Nigeria (CBN) guidelines</strong> on cooperative financial records</li>
    <li><strong>Federal Competition and Consumer Protection Act (FCCPA) 2019</strong></li>
</ul>

<h2 id="lawful">2. Lawful Basis for Processing</h2>
<ul>
    <li><strong>Contract performance:</strong> processing necessary to provide cooperative membership services (dues, shares, thrift)</li>
    <li><strong>Legal obligation:</strong> maintaining financial records as required by law</li>
    <li><strong>Legitimate interests:</strong> security monitoring, fraud prevention, audit trails</li>
    <li><strong>Consent:</strong> sending non-transactional communications (you may withdraw at any time)</li>
</ul>

<h2 id="categories">3. Data Categories and Sensitivity</h2>
<ul>
    <li><strong>Standard personal data:</strong> name, address, contact details — lawful basis: contract</li>
    <li><strong>Government ID numbers (NIN):</strong> treated as sensitive; encrypted at rest, access-controlled — lawful basis: legal obligation for cooperative KYC</li>
    <li><strong>Financial data:</strong> bank account details, payment history — retained 7 years — lawful basis: legal obligation</li>
    <li><strong>Biometric-adjacent:</strong> photographs and signatures — used only for ID card and registration purposes — lawful basis: contract + consent</li>
</ul>

<h2 id="transfers">4. Cross-Border Data Transfers</h2>
<p>Your data is primarily stored on servers within Nigeria. When using payment gateways, transaction data may be processed on servers located outside Nigeria. We ensure that:</p>
<ul>
    <li>All gateway partners are NITDA-registered or operate under equivalent data protection standards</li>
    <li>Data transfer agreements are in place with all international processors</li>
    <li>No sensitive identity documents are transferred to payment processors — only the minimum necessary fields (email, amount, reference)</li>
</ul>

<h2 id="breaches">5. Data Breach Response</h2>
<p>In the event of a data breach we will:</p>
<ul>
    <li>Contain the breach within <strong>1 hour</strong> of detection</li>
    <li>Notify NITDA within <strong>72 hours</strong> as required by NDPA Section 40</li>
    <li>Notify affected data subjects within <strong>7 days</strong> if the breach poses a high risk to their rights</li>
    <li>Maintain a breach register documenting all incidents, their scope, and remediation steps</li>
</ul>
<p>To report a suspected breach: <a href="mailto:info@globalapexfarmers.org.ng">info@globalapexfarmers.org.ng</a> — mark subject <strong>"Security Incident"</strong>.</p>

<h2 id="dpia">6. Data Protection Impact Assessments</h2>
<p>We conduct DPIAs before deploying any new feature that involves large-scale processing of sensitive data, including:</p>
<ul>
    <li>New identity verification integrations</li>
    <li>Expansion of AI or automated decision-making features</li>
    <li>Changes to payment data flows</li>
</ul>

<h2 id="technical">7. Technical and Organisational Controls</h2>
<ul>
    <li><strong>Encryption:</strong> TLS 1.2+ in transit; bcrypt for passwords (cost 12); sensitive files served only via authenticated routes</li>
    <li><strong>Access control:</strong> role-based permissions; principle of least privilege; no shared credentials</li>
    <li><strong>Session security:</strong> 30-minute idle timeout; 8-hour absolute timeout; HTTP-only, SameSite=Lax cookies</li>
    <li><strong>Payment integrity:</strong> immutable append-only ledger; idempotency keys prevent duplicate charges; HMAC-signed webhooks</li>
    <li><strong>Vulnerability management:</strong> regular code reviews; dependency audits; .env excluded from version control</li>
    <li><strong>Backup:</strong> automated daily backups with encrypted storage; retention per regulatory schedule</li>
</ul>

<h2 id="processors">8. Third-Party Processors</h2>
<ul>
    <li><strong>Paystack</strong> — payment processing; see <a href="https://paystack.com/privacy" target="_blank" rel="noopener">Paystack Privacy Policy</a></li>
    <li><strong>Monnify (Interswitch)</strong> — payment processing</li>
    <li><strong>OPay</strong> — payment processing</li>
    <li><strong>Hostinger SMTP</strong> — transactional email delivery</li>
    <li><strong>OpenAI / HuggingFace</strong> — AI farming assistant; queries do not contain personal data beyond session context</li>
</ul>
<p>All processors are bound by data processing agreements. We do not authorise processors to use your data for their own purposes.</p>

<h2 id="audit">9. Audit Trail and Logging</h2>
<p>The Portal maintains immutable audit logs for:</p>
<ul>
    <li>Every authentication attempt (success and failure) with timestamp and IP</li>
    <li>All payment state transitions in the ledger (intent → gateway_init → captured/failed)</li>
    <li>All webhook events (raw body retained; duplicate events detected by event ID)</li>
    <li>Admin actions on member records</li>
    <li>CSRF failures and rate-limit events</li>
    <li>IDOR attempts detected by ownership checks</li>
</ul>
<p>Audit logs are stored in the database (<code>audit_logs</code>, <code>payment_ledger</code>) and rotated log files under <code>/logs/security/</code>. They are never modified or deleted within their retention window.</p>

<h2 id="contact">10. Contact the Data Protection Officer</h2>
<p>For data rights requests, DPIA enquiries, or compliance concerns:</p>
<ul>
    <li><strong>Email:</strong> <a href="mailto:info@globalapexfarmers.org.ng">info@globalapexfarmers.org.ng</a></li>
    <li><strong>Subject line:</strong> "Data Compliance Enquiry"</li>
    <li><strong>Response time:</strong> 5 business days for general enquiries; 30 days for formal data rights requests</li>
</ul>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
