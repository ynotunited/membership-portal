<!-- Profile Content -->
<div class="max-w-6xl mx-auto">
    
    <!-- Profile Header -->
    <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200 mb-6">
        <div class="flex items-center space-x-6">
            <div class="w-24 h-24 rounded-full overflow-hidden bg-gray-200">
                <?php if (!empty($user['photo']) && $user['photo'] !== 'default.jpg'): ?>
                    <img src="<?php echo \App\Helpers\Url::appUrl(); ?>/uploads/member_photos/<?php echo htmlspecialchars($user['photo']); ?>" 
                         alt="Profile Photo" 
                         class="w-full h-full object-cover"
                         onerror="this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-primary/10\'><i class=\'ri-user-line text-primary text-3xl\'></i></div>'">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center bg-primary/10">
                        <i class="ri-user-line text-primary text-3xl"></i>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="flex-1">
                <h2 class="text-2xl font-semibold text-gray-800">
                    <?php echo htmlspecialchars($user['firstname'] . ' ' . $user['surname']); ?>
                </h2>
                <p class="text-gray-600 mt-1">
                    <?php echo htmlspecialchars($user['membership_number'] ?? 'N/A'); ?>
                </p>
                <div class="flex items-center mt-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo ($user['annual_dues_status'] ?? 'unpaid') === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                        <?php echo htmlspecialchars(($user['annual_dues_status'] ?? 'unpaid') === 'paid' ? 'Dues Paid' : 'Dues Pending'); ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Profile Form -->
    <form method="post" enctype="multipart/form-data" class="space-y-6">
        
        <!-- Personal Information -->
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Personal Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Title <span class="text-red-500">*</span></label>
                    <select name="title" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                        <option value="">Select Title</option>
                        <option value="Mr" <?php echo ($user['title'] ?? '') === 'Mr' ? 'selected' : ''; ?>>Mr</option>
                        <option value="Mrs" <?php echo ($user['title'] ?? '') === 'Mrs' ? 'selected' : ''; ?>>Mrs</option>
                        <option value="Ms" <?php echo ($user['title'] ?? '') === 'Ms' ? 'selected' : ''; ?>>Ms</option>
                        <option value="Dr" <?php echo ($user['title'] ?? '') === 'Dr' ? 'selected' : ''; ?>>Dr</option>
                        <option value="Chief" <?php echo ($user['title'] ?? '') === 'Chief' ? 'selected' : ''; ?>>Chief</option>
                        <option value="Prof" <?php echo ($user['title'] ?? '') === 'Prof' ? 'selected' : ''; ?>>Prof</option>
                        <option value="Engr" <?php echo ($user['title'] ?? '') === 'Engr' ? 'selected' : ''; ?>>Engr</option>
                        <option value="Barr" <?php echo ($user['title'] ?? '') === 'Barr' ? 'selected' : ''; ?>>Barr</option>
                        <option value="Alhaji" <?php echo ($user['title'] ?? '') === 'Alhaji' ? 'selected' : ''; ?>>Alhaji</option>
                        <option value="Pastor" <?php echo ($user['title'] ?? '') === 'Pastor' ? 'selected' : ''; ?>>Pastor</option>
                        <option value="Rev" <?php echo ($user['title'] ?? '') === 'Rev' ? 'selected' : ''; ?>>Rev</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Surname <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="surname" 
                           value="<?php echo htmlspecialchars($user['surname'] ?? ''); ?>"
                           required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="firstname" 
                           value="<?php echo htmlspecialchars($user['firstname'] ?? ''); ?>"
                           required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Other Name</label>
                    <input type="text" 
                           name="othername" 
                           value="<?php echo htmlspecialchars($user['othername'] ?? ''); ?>"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gender <span class="text-red-500">*</span></label>
                    <select name="gender" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo ($user['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($user['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Marital Status <span class="text-red-500">*</span></label>
                    <select name="marital_status" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                        <option value="">Select Marital Status</option>
                        <option value="Single" <?php echo ($user['marital_status'] ?? '') === 'Single' ? 'selected' : ''; ?>>Single</option>
                        <option value="Married" <?php echo ($user['marital_status'] ?? '') === 'Married' ? 'selected' : ''; ?>>Married</option>
                        <option value="Divorced" <?php echo ($user['marital_status'] ?? '') === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                        <option value="Widowed" <?php echo ($user['marital_status'] ?? '') === 'Widowed' ? 'selected' : ''; ?>>Widowed</option>
                        <option value="Separated" <?php echo ($user['marital_status'] ?? '') === 'Separated' ? 'selected' : ''; ?>>Separated</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth <span class="text-red-500">*</span></label>
                    <input type="date" 
                           name="dob" 
                           value="<?php echo $user['dob'] ? date('Y-m-d', strtotime($user['dob'])) : ''; ?>"
                           required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
            </div>
        </div>
        
        <!-- Contact Information -->
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Contact Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500">*</span></label>
                    <input type="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>"
                           required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Number <span class="text-red-500">*</span></label>
                    <div class="flex">
                        <?php 
                            $contactNumber = $user['contact_number'] ?? '';
                            $displayNumber = '';
                            $countryCode = '+234'; // Default
                            
                            if ($contactNumber) {
                                if (strpos($contactNumber, '+234') === 0) {
                                    $countryCode = '+234';
                                    $displayNumber = substr($contactNumber, 4);
                                } elseif (strpos($contactNumber, '+233') === 0) {
                                    $countryCode = '+233';
                                    $displayNumber = substr($contactNumber, 4);
                                } elseif (strpos($contactNumber, '+44') === 0) {
                                    $countryCode = '+44';
                                    $displayNumber = substr($contactNumber, 3);
                                } elseif (strpos($contactNumber, '+1') === 0) {
                                    $countryCode = '+1';
                                    $displayNumber = substr($contactNumber, 2);
                                } else {
                                    $displayNumber = $contactNumber;
                                }
                            }
                        ?>
                        <select name="phone_country_code" class="w-20 px-2 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                            <option value="+234" <?php echo $countryCode === '+234' ? 'selected' : ''; ?>>+234</option>
                            <option value="+1" <?php echo $countryCode === '+1' ? 'selected' : ''; ?>>+1</option>
                            <option value="+44" <?php echo $countryCode === '+44' ? 'selected' : ''; ?>>+44</option>
                            <option value="+233" <?php echo $countryCode === '+233' ? 'selected' : ''; ?>>+233</option>
                        </select>
                        <input type="text" 
                               name="contact_number" 
                               value="<?php echo htmlspecialchars($displayNumber); ?>"
                               required
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp Number</label>
                    <div class="flex">
                        <?php 
                            $whatsappNumber = $user['whatsapp_number'] ?? '';
                            $displayWhatsapp = '';
                            $whatsappCountryCode = '+234'; // Default
                            
                            if ($whatsappNumber) {
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
                                    $displayWhatsapp = $whatsappNumber;
                                }
                            }
                        ?>
                        <select name="whatsapp_country_code" class="w-20 px-2 py-2 border border-gray-300 rounded-l-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                            <option value="+234" <?php echo $whatsappCountryCode === '+234' ? 'selected' : ''; ?>>+234</option>
                            <option value="+1" <?php echo $whatsappCountryCode === '+1' ? 'selected' : ''; ?>>+1</option>
                            <option value="+44" <?php echo $whatsappCountryCode === '+44' ? 'selected' : ''; ?>>+44</option>
                            <option value="+233" <?php echo $whatsappCountryCode === '+233' ? 'selected' : ''; ?>>+233</option>
                        </select>
                        <input type="text" 
                               name="whatsapp_number" 
                               value="<?php echo htmlspecialchars($displayWhatsapp); ?>"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Residential Details -->
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Residential Details</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Country <span class="text-red-500">*</span></label>
                    <select name="country" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                        <option value="">Select Country</option>
                        <option value="Nigeria" <?php echo ($user['country'] ?? '') === 'Nigeria' ? 'selected' : ''; ?>>Nigeria</option>
                        <option value="United States" <?php echo ($user['country'] ?? '') === 'United States' ? 'selected' : ''; ?>>United States</option>
                        <option value="United Kingdom" <?php echo ($user['country'] ?? '') === 'United Kingdom' ? 'selected' : ''; ?>>United Kingdom</option>
                        <option value="Ghana" <?php echo ($user['country'] ?? '') === 'Ghana' ? 'selected' : ''; ?>>Ghana</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">State/District <span class="text-red-500">*</span></label>
                    <select name="state_district" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                        <option value="">Select State/District</option>
                        <!-- Add states based on country selection -->
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">LGA <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="lga" 
                           value="<?php echo htmlspecialchars($user['lga'] ?? ''); ?>"
                           required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">City/Town <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="city_town" 
                           value="<?php echo htmlspecialchars($user['city_town'] ?? ''); ?>"
                           required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nearest Bus Stop/Landmark <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="nearest_bus_stop" 
                           value="<?php echo htmlspecialchars($user['nearest_bus_stop'] ?? ''); ?>"
                           required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Street Name <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="street_name" 
                           value="<?php echo htmlspecialchars($user['street_name'] ?? ''); ?>"
                           required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">House No <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="house_no" 
                           value="<?php echo htmlspecialchars($user['house_no'] ?? ''); ?>"
                           required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
            </div>
        </div>
        
        <!-- Business Details -->
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Business Details</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Name of Business</label>
                    <input type="text" 
                           name="business_name" 
                           value="<?php echo htmlspecialchars($user['business_name'] ?? ''); ?>"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nature of Business</label>
                    <input type="text" 
                           name="nature_of_business" 
                           value="<?php echo htmlspecialchars($user['nature_of_business'] ?? ''); ?>"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sub Sector</label>
                    <input type="text" 
                           name="sub_sector" 
                           value="<?php echo htmlspecialchars($user['sub_sector'] ?? ''); ?>"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Business Address</label>
                    <textarea name="business_address" 
                              rows="3"
                              class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm"><?php echo htmlspecialchars($user['business_address'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>
        
        <!-- Identity & Membership -->
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Identity & Membership</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Identity Type <span class="text-red-500">*</span></label>
                    <select name="identity_type" 
                            id="identity_type"
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                        <option value="">Select Identity Type</option>
                        <option value="Passport" <?php echo ($user['identity_type'] ?? '') === 'Passport' ? 'selected' : ''; ?>>Passport</option>
                        <option value="Voter's Card" <?php echo ($user['identity_type'] ?? '') === 'Voter\'s Card' ? 'selected' : ''; ?>>Voter's Card</option>
                        <option value="Driver's License" <?php echo ($user['identity_type'] ?? '') === 'Driver\'s License' ? 'selected' : ''; ?>>Driver's License</option>
                        <option value="NIN" <?php echo ($user['identity_type'] ?? '') === 'NIN' ? 'selected' : ''; ?>>NIN</option>
                        <option value="National ID" <?php echo ($user['identity_type'] ?? '') === 'National ID' ? 'selected' : ''; ?>>National ID</option>
                        <option value="International Passport" <?php echo ($user['identity_type'] ?? '') === 'International Passport' ? 'selected' : ''; ?>>International Passport</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ID Number <span class="text-red-500">*</span></label>
                    <input type="text" 
                           name="id_number" 
                           value="<?php echo htmlspecialchars($user['id_number'] ?? ''); ?>"
                           required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date of Issue <span class="text-red-500">*</span></label>
                    <input type="date" 
                           name="date_of_issue" 
                           value="<?php echo $user['date_of_issue'] ? date('Y-m-d', strtotime($user['date_of_issue'])) : ''; ?>"
                           required
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Chapter (State) <span class="text-red-500">*</span></label>
                    <select name="chapter" 
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                        <option value="">Select Chapter</option>
                        <option value="Lagos" <?php echo ($user['chapter'] ?? '') === 'Lagos' ? 'selected' : ''; ?>>Lagos</option>
                        <option value="Abuja" <?php echo ($user['chapter'] ?? '') === 'Abuja' ? 'selected' : ''; ?>>Abuja</option>
                        <option value="Kano" <?php echo ($user['chapter'] ?? '') === 'Kano' ? 'selected' : ''; ?>>Kano</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Zone/LGA/Region</label>
                    <input type="text" 
                           name="zone" 
                           value="<?php echo htmlspecialchars($user['zone'] ?? ''); ?>"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Profile Photo</label>
                    <input type="file" 
                           name="photo" 
                           accept="image/*"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                    <p class="text-xs text-gray-500 mt-1">Upload a new profile photo (optional)</p>
                </div>
            </div>
        </div>
        
        <!-- Documents Images -->
        <div class="bg-white rounded-lg p-6 shadow-sm border border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-200 pb-2">Documents Images</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nin_card" class="block text-sm font-medium text-gray-700 mb-2" id="identity_card_label">
                        <?php echo htmlspecialchars($user['identity_type'] ?? 'Identity Card'); ?> (Optional)
                    </label>
                    <?php 
                        $ninCardRaw = $user['nin_card'] ?? '';
                        $ninCard = (!empty($ninCardRaw) && strtolower($ninCardRaw) !== 'default_nin.jpg' && $ninCardRaw !== '') ? htmlspecialchars($ninCardRaw) : '';
                        $ninCardPath = $ninCard ? \App\Helpers\Url::appUrl() . '/uploads/nin_cards/' . $ninCard : '';
                    ?>
                    <input type="file" 
                           id="nin_card" 
                           name="nin_card" 
                           accept=".jpg,.jpeg,.png"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                    <p class="text-xs text-gray-500 mt-1">Upload a new identity card image (optional)</p>
                    
                    <?php if ($ninCardPath): ?>
                        <div class="mt-3">
                            <p class="text-xs text-gray-600 mb-2">Current Identity Card:</p>
                            <img id="ninCardPreview" 
                                 src="<?php echo $ninCardPath; ?>" 
                                 alt="Identity Card" 
                                 class="w-32 h-20 object-cover rounded-lg border border-gray-200" />
                        </div>
                    <?php else: ?>
                        <div class="mt-3">
                            <p class="text-xs text-gray-500">No identity card uploaded yet</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <label for="signature" class="block text-sm font-medium text-gray-700 mb-2">Signature (Optional)</label>
                    <?php 
                        $signatureRaw = $user['signature'] ?? '';
                        $signature = (!empty($signatureRaw) && strtolower($signatureRaw) !== 'default_signature.jpg' && $signatureRaw !== '') ? htmlspecialchars($signatureRaw) : '';
                        $signaturePath = $signature ? \App\Helpers\Url::appUrl() . '/uploads/signatures/' . $signature : '';
                    ?>
                    <input type="file" 
                           id="signature" 
                           name="signature" 
                           accept=".jpg,.jpeg,.png"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                    <p class="text-xs text-gray-500 mt-1">Upload a new signature image (optional)</p>
                    
                    <?php if ($signaturePath): ?>
                        <div class="mt-3">
                            <p class="text-xs text-gray-600 mb-2">Current Signature:</p>
                            <img id="signaturePreview" 
                                 src="<?php echo $signaturePath; ?>" 
                                 alt="Signature" 
                                 class="w-32 h-20 object-cover rounded-lg border border-gray-200" />
                        </div>
                    <?php else: ?>
                        <div class="mt-3">
                            <p class="text-xs text-gray-500">No signature uploaded yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" 
                    class="px-6 py-3 bg-secondary text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                Update Profile
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-populate state/district based on country
    const countrySelect = document.querySelector('select[name="country"]');
    const stateDistrictInput = document.querySelector('select[name="state_district"]');
    const currentState = '<?php echo htmlspecialchars($user['state_district'] ?? ''); ?>';

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
            }
        }
        
        countrySelect.addEventListener('change', function() {
            const country = this.value;
            
            if (country) {
                populateStates(country);
            } else {
                stateDistrictInput.innerHTML = '<option value="">Select State/District</option>';
            }
        });
        
        // Initialize states dropdown on page load
        if (countrySelect.value && currentState) {
            populateStates(countrySelect.value, currentState);
        } else if (countrySelect.value) {
            populateStates(countrySelect.value);
        }
    }
    
    // Dynamic identity card label
    const identityTypeSelect = document.getElementById('identity_type');
    const identityCardLabel = document.getElementById('identity_card_label');
    
    if (identityTypeSelect && identityCardLabel) {
        identityTypeSelect.addEventListener('change', function() {
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
        ninCardInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    ninCardPreview.src = e.target.result;
                    ninCardPreview.style.display = 'block';
                    // Update the label to show it's a new upload
                    const label = ninCardPreview.parentElement.querySelector('p');
                    if (label) {
                        label.textContent = 'New Identity Card (will replace current):';
                    }
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                // If no file selected, show the original image if it exists
                const originalSrc = '<?php echo $ninCardPath ?: ""; ?>';
                if (originalSrc) {
                    ninCardPreview.src = originalSrc;
                    ninCardPreview.style.display = 'block';
                    const label = ninCardPreview.parentElement.querySelector('p');
                    if (label) {
                        label.textContent = 'Current Identity Card:';
                    }
                } else {
                    ninCardPreview.style.display = 'none';
                }
            }
        });
    }
    
    if (signatureInput && signaturePreview) {
        signatureInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    signaturePreview.src = e.target.result;
                    signaturePreview.style.display = 'block';
                    // Update the label to show it's a new upload
                    const label = signaturePreview.parentElement.querySelector('p');
                    if (label) {
                        label.textContent = 'New Signature (will replace current):';
                    }
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                // If no file selected, show the original image if it exists
                const originalSrc = '<?php echo $signaturePath ?: ""; ?>';
                if (originalSrc) {
                    signaturePreview.src = originalSrc;
                    signaturePreview.style.display = 'block';
                    const label = signaturePreview.parentElement.querySelector('p');
                    if (label) {
                        label.textContent = 'Current Signature:';
                    }
                } else {
                    signaturePreview.style.display = 'none';
                }
            }
        });
    }
});
</script>