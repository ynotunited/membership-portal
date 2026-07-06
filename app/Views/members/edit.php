<?php
$title = 'Edit Member';
$pageTitle = 'Edit Member';
$activePage = 'members';
ob_start();
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                <svg class="w-8 h-8 text-primary/50 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                    </path>
                </svg>
                Edit Member
            </h1>
            <p class="mt-2 text-gray-600">Update member information and manage their records</p>
        </div>
        <div class="flex space-x-3">
            <a href="<?= \App\Helpers\Url::appUrl() ?>/members"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg font-medium hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Members
            </a>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (!empty($error)): ?>
    <div class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-red-800"><?= htmlspecialchars($error) ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800"><?= htmlspecialchars($success) ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($member): ?>
    <!-- Debug Information (Remove this after fixing) -->
    <?php if (isset($_GET['debug'])): ?>
        <div class="mb-4 p-4 bg-yellow-100 border border-yellow-300 rounded-lg">
            <h4 class="font-bold text-yellow-800">Debug Information:</h4>
            <div class="text-xs text-yellow-700">
                <strong>Contact Number:</strong> "<?= htmlspecialchars($member['contact_number'] ?? 'NULL') ?>"<br>
                <strong>WhatsApp Number:</strong> "<?= htmlspecialchars($member['whatsapp_number'] ?? 'NULL') ?>"<br>
                <strong>State/District:</strong> "<?= htmlspecialchars($member['state_district'] ?? 'NULL') ?>"<br>
                <strong>Country:</strong> "<?= htmlspecialchars($member['country'] ?? 'NULL') ?>"<br>
                <strong>Email:</strong> "<?= htmlspecialchars($member['email'] ?? 'NULL') ?>"<br>
                <strong>Member ID:</strong> <?= $member['id'] ?? 'NULL' ?><br>
                <strong>Contact Number Length:</strong> <?= strlen($member['contact_number'] ?? '') ?><br>
                <strong>Has Country Code:</strong>
                <?= preg_match('/^\+[0-9]+/', $member['contact_number'] ?? '') ? 'Yes' : 'No' ?><br>
                <strong>Contact Number Raw:</strong> <?= bin2hex($member['contact_number'] ?? '') ?><br>
                <strong>State Raw:</strong> <?= bin2hex($member['state_district'] ?? '') ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Edit Member Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center mb-6">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-primary/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            <h3 class="ml-3 text-lg font-semibold text-gray-900">Edit Member Details</h3>
        </div>

        <form method="post" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Personal Information -->
                <div class="space-y-4">
                    <h4 class="text-sm font-medium text-gray-700 border-b border-gray-200 pb-2">Personal Information</h4>

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title <span
                                class="text-red-500">*</span></label>
                        <select id="title" name="title"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            required>
                            <option value="">Select Title</option>
                            <option value="Mr" <?= ($member['title'] ?? '') === 'Mr' ? 'selected' : '' ?>>Mr</option>
                            <option value="Mrs" <?= ($member['title'] ?? '') === 'Mrs' ? 'selected' : '' ?>>Mrs</option>
                            <option value="Ms" <?= ($member['title'] ?? '') === 'Ms' ? 'selected' : '' ?>>Ms</option>
                            <option value="Dr" <?= ($member['title'] ?? '') === 'Dr' ? 'selected' : '' ?>>Dr</option>
                            <option value="Chief" <?= ($member['title'] ?? '') === 'Chief' ? 'selected' : '' ?>>Chief</option>
                            <option value="Prof" <?= ($member['title'] ?? '') === 'Prof' ? 'selected' : '' ?>>Prof</option>
                            <option value="Engr" <?= ($member['title'] ?? '') === 'Engr' ? 'selected' : '' ?>>Engr</option>
                            <option value="Barr" <?= ($member['title'] ?? '') === 'Barr' ? 'selected' : '' ?>>Barr</option>
                            <option value="Alhaji" <?= ($member['title'] ?? '') === 'Alhaji' ? 'selected' : '' ?>>Alhaji
                            </option>
                            <option value="Pastor" <?= ($member['title'] ?? '') === 'Pastor' ? 'selected' : '' ?>>Pastor
                            </option>
                            <option value="Rev" <?= ($member['title'] ?? '') === 'Rev' ? 'selected' : '' ?>>Rev</option>
                        </select>
                    </div>

                    <div>
                        <label for="surname" class="block text-sm font-medium text-gray-700 mb-2">Surname <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="surname" name="surname"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['surname'] ?? '') ?>" required>
                    </div>

                    <div>
                        <label for="firstname" class="block text-sm font-medium text-gray-700 mb-2">First Name <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="firstname" name="firstname"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['firstname'] ?? '') ?>" required>
                    </div>

                    <div>
                        <label for="othername" class="block text-sm font-medium text-gray-700 mb-2">Other Name</label>
                        <input type="text" id="othername" name="othername"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['othername'] ?? '') ?>">
                    </div>

                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">Gender <span
                                class="text-red-500">*</span></label>
                        <select id="gender" name="gender"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            required>
                            <option value="">Select Gender</option>
                            <option value="Male" <?= ($member['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($member['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="marital_status" class="block text-sm font-medium text-gray-700 mb-2">Marital Status
                            <span class="text-red-500">*</span></label>
                        <select id="marital_status" name="marital_status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            required>
                            <option value="">Select Marital Status</option>
                            <option value="Single" <?= ($member['marital_status'] ?? '') === 'Single' ? 'selected' : '' ?>>
                                Single</option>
                            <option value="Married" <?= ($member['marital_status'] ?? '') === 'Married' ? 'selected' : '' ?>>
                                Married</option>
                            <option value="Divorced" <?= ($member['marital_status'] ?? '') === 'Divorced' ? 'selected' : '' ?>>
                                Divorced</option>
                            <option value="Widowed" <?= ($member['marital_status'] ?? '') === 'Widowed' ? 'selected' : '' ?>>
                                Widowed</option>
                            <option value="Separated" <?= ($member['marital_status'] ?? '') === 'Separated' ? 'selected' : '' ?>>Separated</option>
                        </select>
                    </div>

                    <div>
                        <label for="dob" class="block text-sm font-medium text-gray-700 mb-2">Date of Birth <span
                                class="text-red-500">*</span></label>
                        <input type="date" id="dob" name="dob"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['dob'] ?? '') ?>" required>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="space-y-4">
                    <h4 class="text-sm font-medium text-gray-700 border-b border-gray-200 pb-2">Contact Information</h4>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email <span
                                class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['email'] ?? '') ?>" required>
                    </div>

                    <div>
                        <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-2">Contact Number
                            <span class="text-red-500">*</span></label>
                        <div class="flex">
                            <?php
                            $contactNumber = $member['contact_number'] ?? '';
                            $displayNumber = '';
                            $countryCode = '+234'; // Default
                        
                            if ($contactNumber) {
                                // Debug the raw contact number
                                if (isset($_GET['debug'])) {
                                    echo "<!-- DEBUG: Raw contact_number = '" . htmlspecialchars($contactNumber) . "' -->";
                                }

                                // Use specific country code patterns instead of greedy regex
                                if (strpos($contactNumber, '+234') === 0) {
                                    $countryCode = '+234';
                                    $displayNumber = substr($contactNumber, 4);
                                    if (isset($_GET['debug'])) {
                                        echo "<!-- DEBUG: Matched +234 country code -->";
                                    }
                                } elseif (strpos($contactNumber, '+233') === 0) {
                                    $countryCode = '+233';
                                    $displayNumber = substr($contactNumber, 4);
                                    if (isset($_GET['debug'])) {
                                        echo "<!-- DEBUG: Matched +233 country code -->";
                                    }
                                } elseif (strpos($contactNumber, '+44') === 0) {
                                    $countryCode = '+44';
                                    $displayNumber = substr($contactNumber, 3);
                                    if (isset($_GET['debug'])) {
                                        echo "<!-- DEBUG: Matched +44 country code -->";
                                    }
                                } elseif (strpos($contactNumber, '+1') === 0) {
                                    $countryCode = '+1';
                                    $displayNumber = substr($contactNumber, 2);
                                    if (isset($_GET['debug'])) {
                                        echo "<!-- DEBUG: Matched +1 country code -->";
                                    }
                                } else {
                                    // No known country code, show as is
                                    $displayNumber = $contactNumber;
                                    if (isset($_GET['debug'])) {
                                        echo "<!-- DEBUG: No known country code found -->";
                                    }
                                }
                            }

                            // Debug output
                            if (isset($_GET['debug'])) {
                                echo "<!-- DEBUG: contactNumber='$contactNumber', countryCode='$countryCode', displayNumber='$displayNumber' -->";
                            }
                            ?>
                            <select name="phone_country_code"
                                class="w-20 px-2 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="+234" <?= $countryCode === '+234' ? 'selected' : '' ?>>+234</option>
                                <option value="+1" <?= $countryCode === '+1' ? 'selected' : '' ?>>+1</option>
                                <option value="+44" <?= $countryCode === '+44' ? 'selected' : '' ?>>+44</option>
                                <option value="+233" <?= $countryCode === '+233' ? 'selected' : '' ?>>+233</option>
                            </select>
                            <input type="text" id="contact_number" name="contact_number"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                                value="<?= htmlspecialchars($displayNumber) ?>" required>
                        </div>
                        <?php if (isset($_GET['debug'])): ?>
                            <div class="text-xs text-gray-500 mt-1">
                                Debug: Original contact_number = "<?= htmlspecialchars($member['contact_number'] ?? 'NULL') ?>"
                                <br>Display number = "<?= htmlspecialchars($displayNumber) ?>"
                                <br>Has country code:
                                <?= preg_match('/^\+[0-9]+/', $member['contact_number'] ?? '') ? 'Yes' : 'No' ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-2">WhatsApp
                            Number</label>
                        <div class="flex">
                            <?php
                            $whatsappNumber = $member['whatsapp_number'] ?? '';
                            $displayWhatsapp = '';
                            $whatsappCountryCode = '+234'; // Default
                        
                            if ($whatsappNumber) {
                                // Use specific country code patterns instead of greedy regex
                                if (strpos($whatsappNumber, '+234') === 0) {
                                    $whatsappCountryCode = '+234';
                                    $displayWhatsapp = substr($whatsappNumber, 4);
                                } elseif (strpos($whatsappNumber, '+233') === 0) {
                                    $whatsappCountryCode = '+233';
                                    $displayWhatsapp = substr($whatsappNumber, 4);
                                } elseif (strpos($whatsappNumber, '+44') === 0) {
                                    $whatsappCountryCode = '+44';
                                    $displayWhatsapp = substr($whatsappNumber, 3);
                                } elseif (strpos($whatsappNumber, '+1') === 0) {
                                    $whatsappCountryCode = '+1';
                                    $displayWhatsapp = substr($whatsappNumber, 2);
                                } else {
                                    // No known country code, show as is
                                    $displayWhatsapp = $whatsappNumber;
                                }
                            }

                            // Debug output
                            if (isset($_GET['debug'])) {
                                echo "<!-- DEBUG: whatsappNumber='$whatsappNumber', whatsappCountryCode='$whatsappCountryCode', displayWhatsapp='$displayWhatsapp' -->";
                            }
                            ?>
                            <select name="whatsapp_country_code"
                                class="w-20 px-2 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                                <option value="+234" <?= $whatsappCountryCode === '+234' ? 'selected' : '' ?>>+234</option>
                                <option value="+1" <?= $whatsappCountryCode === '+1' ? 'selected' : '' ?>>+1</option>
                                <option value="+44" <?= $whatsappCountryCode === '+44' ? 'selected' : '' ?>>+44</option>
                                <option value="+233" <?= $whatsappCountryCode === '+233' ? 'selected' : '' ?>>+233</option>
                            </select>
                            <input type="text" id="whatsapp_number" name="whatsapp_number"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                                value="<?= htmlspecialchars($displayWhatsapp) ?>">
                        </div>
                        <?php if (isset($_GET['debug'])): ?>
                            <div class="text-xs text-gray-500 mt-1">
                                Debug: Original whatsapp_number =
                                "<?= htmlspecialchars($member['whatsapp_number'] ?? 'NULL') ?>"
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Residential Details -->
                <div class="space-y-4">
                    <h4 class="text-sm font-medium text-gray-700 border-b border-gray-200 pb-2">Residential Details</h4>

                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-2">Country <span
                                class="text-red-500">*</span></label>
                        <select id="country" name="country"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            required>
                            <option value="">Select Country</option>
                            <option value="Nigeria" <?= ($member['country'] ?? '') === 'Nigeria' ? 'selected' : '' ?>>Nigeria
                            </option>
                            <option value="United States" <?= ($member['country'] ?? '') === 'United States' ? 'selected' : '' ?>>United States</option>
                            <option value="United Kingdom" <?= ($member['country'] ?? '') === 'United Kingdom' ? 'selected' : '' ?>>United Kingdom</option>
                            <option value="Ghana" <?= ($member['country'] ?? '') === 'Ghana' ? 'selected' : '' ?>>Ghana
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="state_district" class="block text-sm font-medium text-gray-700 mb-2">State/District
                            <span class="text-red-500">*</span></label>
                        <select id="state_district" name="state_district"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            required>
                            <option value="">Select State/District</option>
                        </select>
                    </div>

                    <div>
                        <label for="lga" class="block text-sm font-medium text-gray-700 mb-2">LGA <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="lga" name="lga"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['lga'] ?? '') ?>" required>
                    </div>

                    <div>
                        <label for="city_town" class="block text-sm font-medium text-gray-700 mb-2">City/Town <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="city_town" name="city_town"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['city_town'] ?? '') ?>" required>
                    </div>

                    <div>
                        <label for="nearest_bus_stop" class="block text-sm font-medium text-gray-700 mb-2">Nearest Bus
                            Stop/Landmark <span class="text-red-500">*</span></label>
                        <input type="text" id="nearest_bus_stop" name="nearest_bus_stop"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['nearest_bus_stop'] ?? '') ?>" required>
                    </div>

                    <div>
                        <label for="street_name" class="block text-sm font-medium text-gray-700 mb-2">Street Name <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="street_name" name="street_name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['street_name'] ?? '') ?>" required>
                    </div>

                    <div>
                        <label for="house_no" class="block text-sm font-medium text-gray-700 mb-2">House No <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="house_no" name="house_no"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['house_no'] ?? '') ?>" required>
                    </div>
                </div>
            </div>

            <!-- Business Details -->
            <div class="mt-8">
                <h4 class="text-sm font-medium text-gray-700 border-b border-gray-200 pb-2 mb-4">Business Details</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="business_name" class="block text-sm font-medium text-gray-700 mb-2">Name of
                            Business</label>
                        <input type="text" id="business_name" name="business_name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['business_name'] ?? '') ?>">
                    </div>

                    <div>
                        <label for="nature_of_business" class="block text-sm font-medium text-gray-700 mb-2">Nature of
                            Business</label>
                        <input type="text" id="nature_of_business" name="nature_of_business"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['nature_of_business'] ?? '') ?>">
                    </div>

                    <div>
                        <label for="sub_sector" class="block text-sm font-medium text-gray-700 mb-2">Sub Sector</label>
                        <input type="text" id="sub_sector" name="sub_sector"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['sub_sector'] ?? '') ?>">
                    </div>

                    <div>
                        <label for="business_address" class="block text-sm font-medium text-gray-700 mb-2">Business
                            Address</label>
                        <textarea id="business_address" name="business_address" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"><?= htmlspecialchars($member['business_address'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Identity & Membership -->
            <div class="mt-8">
                <h4 class="text-sm font-medium text-gray-700 border-b border-gray-200 pb-2 mb-4">Identity & Membership</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="identity_type" class="block text-sm font-medium text-gray-700 mb-2">Identity Type <span
                                class="text-red-500">*</span></label>
                        <select id="identity_type" name="identity_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            required>
                            <option value="">Select Identity Type</option>
                            <option value="Passport" <?= ($member['identity_type'] ?? '') === 'Passport' ? 'selected' : '' ?>>
                                Passport</option>
                            <option value="Voter's Card" <?= ($member['identity_type'] ?? '') === 'Voter\'s Card' ? 'selected' : '' ?>>Voter's Card</option>
                            <option value="Driver's License" <?= ($member['identity_type'] ?? '') === 'Driver\'s License' ? 'selected' : '' ?>>Driver's License</option>
                            <option value="NIN" <?= ($member['identity_type'] ?? '') === 'NIN' ? 'selected' : '' ?>>NIN
                            </option>
                            <option value="National ID" <?= ($member['identity_type'] ?? '') === 'National ID' ? 'selected' : '' ?>>National ID</option>
                            <option value="International Passport" <?= ($member['identity_type'] ?? '') === 'International Passport' ? 'selected' : '' ?>>International Passport</option>
                        </select>
                    </div>

                    <div>
                        <label for="id_number" class="block text-sm font-medium text-gray-700 mb-2">ID Number <span
                                class="text-red-500">*</span></label>
                        <input type="text" id="id_number" name="id_number"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['id_number'] ?? '') ?>" required>
                    </div>

                    <div>
                        <label for="date_of_issue" class="block text-sm font-medium text-gray-700 mb-2">Date of Issue <span
                                class="text-red-500">*</span></label>
                        <input type="date" id="date_of_issue" name="date_of_issue"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['date_of_issue'] ?? '') ?>" required>
                    </div>

                    <div>
                        <label for="registration_status" class="block text-sm font-medium text-gray-700 mb-2">Registration
                            Status <span class="text-red-500">*</span></label>
                        <select id="registration_status" name="registration_status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            required>
                            <option value="">Select Registration Status</option>
                            <option value="Director" <?= ($member['registration_status'] ?? '') === 'Director' ? 'selected' : '' ?>>Director - ₦1,000,000</option>
                            <option value="Membership" <?= ($member['registration_status'] ?? '') === 'Membership' ? 'selected' : '' ?>>Membership - ₦12,000</option>
                        </select>
                    </div>

                    <div>
                        <label for="chapter" class="block text-sm font-medium text-gray-700 mb-2">Chapter (State) <span
                                class="text-red-500">*</span></label>
                        <select id="chapter" name="chapter"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            required>
                            <option value="">Select Chapter</option>
                            <option value="Lagos" <?= ($member['chapter'] ?? '') === 'Lagos' ? 'selected' : '' ?>>Lagos
                            </option>
                            <option value="Abuja" <?= ($member['chapter'] ?? '') === 'Abuja' ? 'selected' : '' ?>>Abuja
                            </option>
                            <option value="Kano" <?= ($member['chapter'] ?? '') === 'Kano' ? 'selected' : '' ?>>Kano</option>
                        </select>
                    </div>

                    <div>
                        <label for="zone" class="block text-sm font-medium text-gray-700 mb-2">Zone/LGA/Region</label>
                        <input type="text" id="zone" name="zone"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['zone'] ?? '') ?>">
                    </div>

                    <div>
                        <label for="member_type" class="block text-sm font-medium text-gray-700 mb-2">Membership
                            Type</label>
                        <select id="member_type" name="member_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            disabled>
                            <option value="Membership Registration" <?= ($member['member_type'] ?? '') === 'Membership Registration' ? 'selected' : '' ?>>Membership Registration - ₦12,000</option>
                            <option value="Renewal" <?= ($member['member_type'] ?? '') === 'Renewal' ? 'selected' : '' ?>>
                                Renewal - ₦12,000</option>
                        </select>
                        <div class="text-xs text-gray-400 mt-1">Membership type cannot be changed</div>
                    </div>

                    <div>
                        <label for="payment_type" class="block text-sm font-medium text-gray-700 mb-2">Payment Type</label>
                        <select id="payment_type" name="payment_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            disabled>
                            <option value="Online Payment" <?= ($member['payment_type'] ?? '') === 'Online Payment' ? 'selected' : '' ?>>Online Payment</option>
                            <option value="Bank Transfer" <?= ($member['payment_type'] ?? '') === 'Bank Transfer' ? 'selected' : '' ?>>Bank Transfer</option>
                            <option value="Cash" <?= ($member['payment_type'] ?? '') === 'Cash' ? 'selected' : '' ?>>Cash
                            </option>
                        </select>
                        <div class="text-xs text-gray-400 mt-1">Payment type cannot be changed</div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <input type="password" id="password" name="password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            placeholder="Leave blank to keep current password">
                        <div class="text-xs text-gray-400 mt-1">Password must be 8 characters long and include letters,
                            numbers, and special characters</div>
                    </div>
                </div>
            </div>

            <!-- Documents Images -->
            <div class="mt-8">
                <h4 class="text-sm font-medium text-gray-700 border-b border-gray-200 pb-2 mb-4">Documents Images</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nin_card" class="block text-sm font-medium text-gray-700 mb-2"
                            id="identity_card_label"><?= htmlspecialchars($member['identity_type'] ?? 'Identity Card') ?></label>
                        <?php
                        $ninCardRaw = $member['nin_card'] ?? '';
                        $ninCard = (!empty($ninCardRaw) && strtolower($ninCardRaw) !== 'default_nin.jpg') ? htmlspecialchars($ninCardRaw) : 'default_nin.jpg';
                        $ninCardPath = \App\Helpers\Url::base(true) . '/uploads/nin_cards/' . $ninCard;
                        ?>
                        <input type="file" id="nin_card" name="nin_card"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            accept=".jpg,.jpeg,.png">
                        <div class="mt-2">
                            <img id="ninCardPreview" src="<?= $ninCardPath ?>" alt="Identity Card"
                                class="w-20 h-20 object-cover rounded-lg border border-gray-200 <?= $ninCard !== 'default_nin.jpg' ? '' : 'hidden' ?>" />
                        </div>
                    </div>

                    <div>
                        <label for="signature" class="block text-sm font-medium text-gray-700 mb-2">Signature</label>
                        <?php
                        $signatureRaw = $member['signature'] ?? '';
                        $signature = (!empty($signatureRaw) && strtolower($signatureRaw) !== 'default_signature.jpg') ? htmlspecialchars($signatureRaw) : 'default_signature.jpg';
                        $signaturePath = \App\Helpers\Url::base(true) . '/uploads/signatures/' . $signature;
                        ?>
                        <input type="file" id="signature" name="signature"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            accept=".jpg,.jpeg,.png">
                        <div class="mt-2">
                            <img id="signaturePreview" src="<?= $signaturePath ?>" alt="Signature"
                                class="w-20 h-20 object-cover rounded-lg border border-gray-200 <?= $signature !== 'default_signature.jpg' ? '' : 'hidden' ?>" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Information -->
            <div class="mt-8">
                <h4 class="text-sm font-medium text-gray-700 border-b border-gray-200 pb-2 mb-4">Bank Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="account_name" class="block text-sm font-medium text-gray-700 mb-2">Account Name</label>
                        <input type="text" id="account_name" name="account_name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['account_name'] ?? '') ?>">
                    </div>

                    <div>
                        <label for="account_number" class="block text-sm font-medium text-gray-700 mb-2">Account
                            Number</label>
                        <input type="text" id="account_number" name="account_number"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['account_number'] ?? '') ?>">
                    </div>

                    <div>
                        <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">Bank Name</label>
                        <input type="text" id="bank_name" name="bank_name"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                            value="<?= htmlspecialchars($member['bank_name'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Status Information -->
            <div class="mt-8">
                <h4 class="text-sm font-medium text-gray-700 border-b border-gray-200 pb-2 mb-4">Status Information</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="payment_status" class="block text-sm font-medium text-gray-700 mb-2">Payment
                            Status</label>
                        <select id="payment_status" name="payment_status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="Paid" <?= ($member['payment_status'] ?? '') === 'Paid' ? 'selected' : '' ?>>Paid
                            </option>
                            <option value="Unpaid" <?= ($member['payment_status'] ?? '') === 'Unpaid' ? 'selected' : '' ?>>
                                Unpaid</option>
                            <option value="Pending" <?= ($member['payment_status'] ?? '') === 'Pending' ? 'selected' : '' ?>>
                                Pending</option>
                        </select>
                    </div>

                    <div>
                        <label for="annual_dues_status" class="block text-sm font-medium text-gray-700 mb-2">Annual Dues
                            Status</label>
                        <select id="annual_dues_status" name="annual_dues_status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="Paid" <?= ($member['annual_dues_status'] ?? '') === 'Paid' ? 'selected' : '' ?>>Paid
                            </option>
                            <option value="Unpaid" <?= ($member['annual_dues_status'] ?? '') === 'Unpaid' ? 'selected' : '' ?>>
                                Unpaid</option>
                            <option value="Pending" <?= ($member['annual_dues_status'] ?? '') === 'Pending' ? 'selected' : '' ?>>Pending</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex space-x-4">
                <button type="submit"
                    class="inline-flex items-center px-6 py-3 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Update Member
                </button>
                <a href="<?= \App\Helpers\Url::appUrl() ?>/members"
                    class="inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-lg font-medium hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <!-- Annual Dues History -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center mb-6">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                    </path>
                </svg>
            </div>
            <h3 class="ml-3 text-lg font-semibold text-gray-900">Annual Dues History</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($annualDuesHistory)):
                        foreach ($annualDuesHistory as $dues): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₦<?= htmlspecialchars($dues['amount']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= ($dues['status'] === 'Paid') ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                        <?= htmlspecialchars($dues['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($dues['payment_date']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($dues['notes']) ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">No annual dues records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Annual Dues Form -->
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-4">Add New Annual Dues</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="manual_annual_dues_amount" class="block text-sm font-medium text-gray-700 mb-2">Amount
                        (₦)</label>
                    <input type="number" id="manual_annual_dues_amount" name="manual_annual_dues_amount"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                        min="0" step="0.01">
                </div>
                <div>
                    <label for="manual_annual_dues_status"
                        class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select id="manual_annual_dues_status" name="manual_annual_dues_status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="Paid">Paid</option>
                        <option value="Unpaid">Unpaid</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
                <div>
                    <label for="manual_annual_dues_date" class="block text-sm font-medium text-gray-700 mb-2">Payment
                        Date</label>
                    <input type="date" id="manual_annual_dues_date" name="manual_annual_dues_date"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label for="manual_annual_dues_notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <input type="text" id="manual_annual_dues_notes" name="manual_annual_dues_notes"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Annual Dues
                </button>
            </div>
        </div>
    </div>

    <!-- Shares History -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center mb-6">
            <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    </path>
                </svg>
            </div>
            <h3 class="ml-3 text-lg font-semibold text-gray-900">Shares History (Total: <?= (int) ($totalShares ?? 0) ?>)
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Number of Shares</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purchase Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($sharesHistory)):
                        foreach ($sharesHistory as $share): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($share['number_of_shares']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    ₦<?= htmlspecialchars($share['amount']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($share['purchase_date']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?= htmlspecialchars($share['notes']) ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">No shares records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Shares Form -->
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <h4 class="text-sm font-medium text-gray-700 mb-4">Add New Shares</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="manual_shares" class="block text-sm font-medium text-gray-700 mb-2">Number of Shares</label>
                    <input type="number" id="manual_shares" name="manual_shares"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                        min="100" value="" placeholder="Minimum 100">
                    <div class="text-xs text-gray-400 mt-1">Minimum 100 shares required</div>
                </div>
                <div>
                    <label for="manual_share_amount" class="block text-sm font-medium text-gray-700 mb-2">Amount (₦)</label>
                    <input type="number" id="manual_share_amount" name="manual_share_amount"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                        min="0" readonly>
                    <div class="text-xs text-gray-400 mt-1">Calculated automatically (₦100 per share)</div>
                </div>
                <div>
                    <label for="manual_share_date" class="block text-sm font-medium text-gray-700 mb-2">Purchase
                        Date</label>
                    <input type="date" id="manual_share_date" name="manual_share_date"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
                <div>
                    <label for="manual_share_notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <input type="text" id="manual_share_notes" name="manual_share_notes"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" id="addSharesBtn"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Shares
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Auto-calculate amount for shares
            const sharesInput = document.getElementById('manual_shares');
            const amountInput = document.getElementById('manual_share_amount');
            const addSharesBtn = document.getElementById('addSharesBtn');

            if (sharesInput && amountInput && addSharesBtn) {
                sharesInput.addEventListener('input', function () {
                    let shares = parseInt(this.value, 10) || 0;
                    if (shares < 100) {
                        amountInput.value = '';
                        addSharesBtn.disabled = true;
                    } else {
                        amountInput.value = shares * 100;
                        addSharesBtn.disabled = false;
                    }
                });
                // Initial state
                addSharesBtn.disabled = true;
            }

            // Profile photo preview
            const photoInput = document.getElementById('profile_photo');
            const photoPreview = document.getElementById('profilePhotoPreview');

            if (photoInput && photoPreview) {
                photoInput.addEventListener('change', function () {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            photoPreview.src = e.target.result;
                            photoPreview.classList.remove('hidden');
                        };
                        reader.readAsDataURL(this.files[0]);
                    } else {
                        photoPreview.classList.add('hidden');
                    }
                });
            }

            // Auto-populate state/district based on country
            const countrySelect = document.getElementById('country');
            const stateDistrictInput = document.getElementById('state_district');
            const currentState = '<?= htmlspecialchars($member['state_district'] ?? '') ?>';

            // Debug output
            console.log('PHP currentState:', '<?= htmlspecialchars($member['state_district'] ?? 'NULL') ?>');
            console.log('JavaScript currentState:', currentState);
            console.log('Current country:', '<?= htmlspecialchars($member['country'] ?? 'NULL') ?>');

            // Set default state based on country if state is empty
            const defaultStates = {
                'Nigeria': 'Lagos',
                'Ghana': 'Greater Accra',
                'United States': 'California',
                'United Kingdom': 'England'
            };

            const memberCountry = '<?= htmlspecialchars($member['country'] ?? '') ?>';
            const memberState = '<?= htmlspecialchars($member['state_district'] ?? '') ?>';
            const effectiveState = memberState || defaultStates[memberCountry] || '';

            // States data for each country
            const statesData = {
                'Nigeria': [
                    'Abia', 'Adamawa', 'Akwa Ibom', 'Anambra', 'Bauchi', 'Bayelsa', 'Benue', 'Borno',
                    'Cross River', 'Delta', 'Ebonyi', 'Edo', 'Ekiti', 'Enugu', 'Federal Capital Territory',
                    'Gombe', 'Imo', 'Jigawa', 'Kaduna', 'Kano', 'Katsina', 'Kebbi', 'Kogi', 'Kwara',
                    'Lagos', 'Nasarawa', 'Niger', 'Ogun', 'Ondo', 'Osun', 'Oyo', 'Plateau', 'Rivers',
                    'Sokoto', 'Taraba', 'Yobe', 'Zamfara'
                ],
                'United States': [
                    'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut',
                    'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa',
                    'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan',
                    'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire',
                    'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio',
                    'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota',
                    'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia',
                    'Wisconsin', 'Wyoming'
                ],
                'United Kingdom': [
                    'England', 'Scotland', 'Wales', 'Northern Ireland'
                ],
                'Ghana': [
                    'Greater Accra', 'Ashanti', 'Western', 'Eastern', 'Central', 'Volta', 'Northern',
                    'Upper East', 'Upper West', 'Bono', 'Bono East', 'Ahafo', 'Savannah', 'North East',
                    'Western North'
                ]
            };

            if (countrySelect && stateDistrictInput) {
                console.log('Country auto-populate script loaded');
                console.log('Initial country value:', countrySelect.value);
                console.log('Current state value:', currentState);

                // Function to populate states dropdown
                function populateStates(country, selectedState = '') {
                    stateDistrictInput.innerHTML = '<option value="">Select State/District</option>';

                    if (country && statesData[country]) {
                        statesData[country].forEach(state => {
                            const option = document.createElement('option');
                            option.value = state;
                            option.textContent = state;
                            if (state === selectedState) {
                                option.selected = true;
                            }
                            stateDistrictInput.appendChild(option);
                        });
                        console.log(`Populated ${statesData[country].length} states for ${country}, selected: ${selectedState}`);
                    }
                }

                countrySelect.addEventListener('change', function () {
                    const country = this.value;
                    console.log('Country selected:', country);

                    if (country) {
                        populateStates(country);
                    } else {
                        stateDistrictInput.innerHTML = '<option value="">Select State/District</option>';
                    }
                });

                // Initialize states dropdown on page load with current state pre-selected
                if (countrySelect.value && effectiveState) {
                    console.log('Initializing states dropdown on page load with effective state:', effectiveState);
                    populateStates(countrySelect.value, effectiveState);
                } else if (countrySelect.value) {
                    console.log('Initializing states dropdown on page load (no effective state)');
                    populateStates(countrySelect.value);
                }

                // Force immediate initialization
                setTimeout(() => {
                    if (countrySelect.value && effectiveState) {
                        console.log('Forcing state initialization...');
                        populateStates(countrySelect.value, effectiveState);
                    }
                }, 100);
            }

            // Dynamic identity card label
            const identityTypeSelect = document.getElementById('identity_type');
            const identityCardLabel = document.getElementById('identity_card_label');

            if (identityTypeSelect && identityCardLabel) {
                identityTypeSelect.addEventListener('change', function () {
                    const selectedType = this.value;
                    if (selectedType) {
                        identityCardLabel.textContent = selectedType;
                    } else {
                        identityCardLabel.textContent = 'Identity Card';
                    }
                });
            }

            // File preview handlers
            const ninCardInput = document.getElementById('nin_card');
            const ninCardPreview = document.getElementById('ninCardPreview');
            const signatureInput = document.getElementById('signature');
            const signaturePreview = document.getElementById('signaturePreview');

            if (ninCardInput && ninCardPreview) {
                ninCardInput.addEventListener('change', function () {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            ninCardPreview.src = e.target.result;
                            ninCardPreview.style.display = 'block';
                        };
                        reader.readAsDataURL(this.files[0]);
                    } else {
                        ninCardPreview.style.display = 'none';
                    }
                });
            }

            if (signatureInput && signaturePreview) {
                signatureInput.addEventListener('change', function () {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            signaturePreview.src = e.target.result;
                            signaturePreview.style.display = 'block';
                        };
                        reader.readAsDataURL(this.files[0]);
                    } else {
                        signaturePreview.style.display = 'none';
                    }
                });
            }
        });
    </script>

<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/admin.php';
?>