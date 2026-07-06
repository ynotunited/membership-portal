<?php
$title = 'Member Profile';
$pageTitle = 'Member Profile';
$activePage = 'members';
ob_start();
?>

<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                <svg class="w-8 h-8 text-primary/50 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Member Profile
            </h1>
            <p class="mt-2 text-gray-600">View detailed member information</p>
        </div>
        <div class="flex space-x-3">
            <a href="<?= \App\Helpers\Url::appUrl() ?>/members/membership-card?id=<?= $member['id'] ?>" 
               target="_blank"
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V4a2 2 0 114 0v2m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                </svg>
                Membership Card
            </a>
            <a href="<?= \App\Helpers\Url::appUrl() ?>/members/edit?id=<?= $member['id'] ?>" 
               class="inline-flex items-center px-4 py-2 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit Member
            </a>
            <a href="<?= \App\Helpers\Url::appUrl() ?>/members" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg font-medium hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Members
            </a>
        </div>
    </div>
</div>

<?php if ($member): ?>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Profile Card -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="text-center">
                <?php 
                    $photoRaw = $member['photo'] ?? '';
                    $photo = (!empty($photoRaw) && strtolower($photoRaw) !== 'default.jpg') ? htmlspecialchars($photoRaw) : 'default-user.png';
                    $photoPath = \App\Helpers\Url::base(true) . '/uploads/member_photos/' . $photo;
                ?>
                <img src="<?= $photoPath ?>" alt="Profile Photo" class="w-32 h-32 rounded-full mx-auto mb-4 object-cover border-4 border-gray-200">
                <h2 class="text-xl font-bold text-gray-900 mb-2">
                    <?= htmlspecialchars(($member['title'] ?? '') . ' ' . ($member['firstname'] ?? '') . ' ' . ($member['surname'] ?? '')) ?>
                </h2>
                <p class="text-gray-600 mb-4"><?= htmlspecialchars($member['membership_number'] ?? 'N/A') ?></p>
                
                <!-- Status Badges -->
                <div class="flex flex-wrap gap-2 justify-center mb-6">
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full <?= ($member['payment_status'] === 'Paid') ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                        <?= htmlspecialchars($member['payment_status'] ?? 'Pending') ?>
                    </span>
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-primary/10 text-blue-800">
                        <?= htmlspecialchars($member['registration_status'] ?? 'N/A') ?>
                    </span>
                    <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800">
                        <?= htmlspecialchars($member['member_type'] ?? 'N/A') ?>
                    </span>
                </div>
                
                <!-- Quick Info -->
                <div class="space-y-3 text-left">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span class="text-sm text-gray-600"><?= htmlspecialchars($member['email'] ?? 'N/A') ?></span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <span class="text-sm text-gray-600"><?= htmlspecialchars($member['contact_number'] ?? 'N/A') ?></span>
                    </div>
                    <?php if (!empty($member['whatsapp_number'])): ?>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <span class="text-sm text-gray-600"><?= htmlspecialchars($member['whatsapp_number']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="text-sm text-gray-600"><?= htmlspecialchars($member['city_town'] ?? 'N/A') ?>, <?= htmlspecialchars($member['state_district'] ?? 'N/A') ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Member Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Personal Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 text-primary/50 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Personal Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Title</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['title'] ?? 'N/A') ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Full Name</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars(($member['title'] ?? '') . ' ' . ($member['firstname'] ?? '') . ' ' . ($member['surname'] ?? '')) ?></p>
                </div>
                <?php if (!empty($member['othername'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Other Name</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['othername']) ?></p>
                </div>
                <?php endif; ?>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Gender</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['gender'] ?? 'N/A') ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Marital Status</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['marital_status'] ?? 'N/A') ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Date of Birth</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['dob'] ?? 'N/A') ?></p>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                Contact Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Email</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['email']) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Contact Number</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['contact_number']) ?></p>
                </div>
                <?php if (!empty($member['whatsapp_number'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-500">WhatsApp Number</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['whatsapp_number']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Residential Details -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 text-purple-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Residential Details
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500">House No</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['house_no'] ?? '') ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Street Name</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['street_name'] ?? '') ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Nearest Bus Stop/Landmark</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['nearest_bus_stop'] ?? '') ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">City/Town</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['city_town'] ?? '') ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">LGA</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['lga'] ?? '') ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">State/District</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['state_district'] ?? '') ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Country</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['country'] ?? '') ?></p>
                </div>
            </div>
        </div>

        <!-- Business Details -->
        <?php if (!empty($member['business_name']) || !empty($member['nature_of_business'])): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 text-orange-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Business Details
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (!empty($member['business_name'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Business Name</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['business_name']) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($member['nature_of_business'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Nature of Business</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['nature_of_business']) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($member['sub_sector'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Sub Sector</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['sub_sector']) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($member['business_address'])): ?>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-500">Business Address</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['business_address']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Identity & Membership -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 text-indigo-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Identity & Membership
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Identity Type</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['identity_type'] ?? '') ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">ID Number</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['id_number'] ?? '') ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Date of Issue</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['date_of_issue'] ?? '') ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Registration Status</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['registration_status'] ?? '') ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Chapter</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['chapter'] ?? '') ?></p>
                </div>
                <?php if (!empty($member['zone'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Zone/LGA/Region</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['zone']) ?></p>
                </div>
                <?php endif; ?>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Membership Type</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['member_type'] ?? '') ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Payment Type</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['payment_type'] ?? '') ?></p>
                </div>
            </div>
        </div>

        <!-- Bank Information -->
        <?php if (!empty($member['account_name']) || !empty($member['account_number']) || !empty($member['bank_name'])): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                Bank Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php if (!empty($member['account_name'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Account Name</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['account_name']) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($member['account_number'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Account Number</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['account_number']) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($member['bank_name'])): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Bank Name</label>
                    <p class="text-sm text-gray-900"><?= htmlspecialchars($member['bank_name']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Documents -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Documents
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500"><?= htmlspecialchars($member['identity_type'] ?? 'Identity Card') ?></label>
                    <div class="mt-2">
                        <?php 
                            $ninCardRaw = $member['nin_card'] ?? '';
                            $ninCard = (!empty($ninCardRaw) && strtolower($ninCardRaw) !== 'default_nin.jpg') ? htmlspecialchars($ninCardRaw) : 'default_nin.jpg';
                            $ninCardPath = \App\Helpers\Url::base(true) . '/uploads/nin_cards/' . $ninCard;
                        ?>
                        <img src="<?= $ninCardPath ?>" alt="<?= htmlspecialchars($member['identity_type'] ?? 'Identity Card') ?>" 
                             class="w-20 h-20 object-cover rounded-lg border border-gray-200">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-500">Signature</label>
                    <div class="mt-2">
                        <?php 
                            $signatureRaw = $member['signature'] ?? '';
                            $signature = (!empty($signatureRaw) && strtolower($signatureRaw) !== 'default_signature.jpg') ? htmlspecialchars($signatureRaw) : 'default_signature.jpg';
                            $signaturePath = \App\Helpers\Url::base(true) . '/uploads/signatures/' . $signature;
                        ?>
                        <img src="<?= $signaturePath ?>" alt="Signature" 
                             class="w-20 h-20 object-cover rounded-lg border border-gray-200">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="text-center py-8">
        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Member Not Found</h3>
        <p class="text-gray-600">The requested member could not be found.</p>
        <a href="<?= \App\Helpers\Url::appUrl() ?>/members" class="inline-flex items-center px-4 py-2 mt-4 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700">
            Back to Members
        </a>
    </div>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/admin.php';
?> 