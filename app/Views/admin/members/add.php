<?php
$title = 'Add Member';
$pageTitle = 'Add Member';
$activePage = 'members';
ob_start();
?>

<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                <svg class="w-8 h-8 text-primary/50 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 11c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm0 2c-2.21 0-4 1.79-4 4v1h8v-1c0-2.21-1.79-4-4-4z">
                    </path>
                </svg>
                Add Member
            </h1>
            <p class="mt-2 text-gray-600">Fill in the details below to add a new member</p>
        </div>
        <a href="<?= \App\Helpers\Url::appUrl() ?>/members"
            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg font-medium hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                </path>
            </svg>
            Back to Members List
        </a>
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
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button onclick="this.parentElement.parentElement.parentElement.parentElement.remove()"
                        class="inline-flex rounded-md bg-red-50 p-1.5 text-red-500 hover:bg-red-100">
                        <span class="sr-only">Dismiss</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
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
            <div class="ml-auto pl-3">
                <div class="-mx-1.5 -my-1.5">
                    <button onclick="this.parentElement.parentElement.parentElement.parentElement.remove()"
                        class="inline-flex rounded-md bg-green-50 p-1.5 text-green-500 hover:bg-green-100">
                        <span class="sr-only">Dismiss</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Form Section -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center mb-6">
        <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
        </div>
        <h3 class="ml-3 text-lg font-semibold text-gray-900">Member Registration Form</h3>
    </div>

    <form method="post" enctype="multipart/form-data" class="space-y-6">
        <!-- Personal Information -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Title <span class="text-red-500">*</span>
                </label>
                <select name="title" id="title"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                    <option value="">Select Title</option>
                    <option value="Mr">Mr</option>
                    <option value="Mrs">Mrs</option>
                    <option value="Ms">Ms</option>
                    <option value="Dr">Dr</option>
                    <option value="Chief">Chief</option>
                    <option value="Prof">Prof</option>
                    <option value="Engr">Engr</option>
                    <option value="Barr">Barr</option>
                    <option value="Alhaji">Alhaji</option>
                    <option value="Pastor">Pastor</option>
                    <option value="Rev">Rev</option>
                </select>
                <div class="text-xs text-gray-400 mt-1">Select the member's title</div>
            </div>

            <div>
                <label for="surname" class="block text-sm font-medium text-gray-700 mb-2">
                    Surname <span class="text-red-500">*</span>
                </label>
                <input type="text" name="surname" id="surname"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                <div class="text-xs text-gray-400 mt-1">Enter the member's surname</div>
            </div>

            <div>
                <label for="firstname" class="block text-sm font-medium text-gray-700 mb-2">
                    First Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="firstname" id="firstname"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                <div class="text-xs text-gray-400 mt-1">Enter the member's first name</div>
            </div>

            <div>
                <label for="othername" class="block text-sm font-medium text-gray-700 mb-2">Other Name</label>
                <input type="text" name="othername" id="othername"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                <div class="text-xs text-gray-400 mt-1">Enter middle name or other names (optional)</div>
            </div>

            <div>
                <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">
                    Gender <span class="text-red-500">*</span>
                </label>
                <select name="gender" id="gender"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
                <div class="text-xs text-gray-400 mt-1">Select the member's gender</div>
            </div>

            <div>
                <label for="marital_status" class="block text-sm font-medium text-gray-700 mb-2">
                    Marital Status <span class="text-red-500">*</span>
                </label>
                <select name="marital_status" id="marital_status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                    <option value="">Select Marital Status</option>
                    <option value="Single">Single</option>
                    <option value="Married">Married</option>
                    <option value="Divorced">Divorced</option>
                    <option value="Widowed">Widowed</option>
                    <option value="Separated">Separated</option>
                </select>
                <div class="text-xs text-gray-400 mt-1">Select the member's marital status</div>
            </div>

            <div>
                <label for="dob" class="block text-sm font-medium text-gray-700 mb-2">
                    Date of Birth <span class="text-red-500">*</span>
                </label>
                <input type="date" name="dob" id="dob"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                <div class="text-xs text-gray-400 mt-1">Select the member's date of birth</div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" id="email"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                <div class="text-xs text-gray-400 mt-1">Enter a valid email address</div>
            </div>

            <div>
                <label for="contact_number" class="block text-sm font-medium text-gray-700 mb-2">
                    Contact Number <span class="text-red-500">*</span>
                </label>
                <div class="flex">
                    <select name="phone_country_code"
                        class="w-20 px-2 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="+234">+234</option>
                        <option value="+1">+1</option>
                        <option value="+44">+44</option>
                        <option value="+233">+233</option>
                    </select>
                    <input type="text" name="contact_number" id="contact_number"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="8012345678" required>
                </div>
                <div class="text-xs text-gray-400 mt-1">Enter the member's contact number</div>
            </div>

            <div>
                <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-2">WhatsApp
                    Number</label>
                <div class="flex">
                    <select name="whatsapp_country_code"
                        class="w-20 px-2 py-2 border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="+234">+234</option>
                        <option value="+1">+1</option>
                        <option value="+44">+44</option>
                        <option value="+233">+233</option>
                    </select>
                    <input type="text" name="whatsapp_number" id="whatsapp_number"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-r-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="8012345678">
                </div>
                <div class="text-xs text-gray-400 mt-1">Enter WhatsApp number (optional)</div>
            </div>
        </div>

        <!-- Residential Details -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                    Country <span class="text-red-500">*</span>
                </label>
                <select name="country" id="country"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                    <option value="">Select Country</option>
                    <option value="Nigeria">Nigeria</option>
                    <option value="United States">United States</option>
                    <option value="United Kingdom">United Kingdom</option>
                    <option value="Ghana">Ghana</option>
                </select>
                <div class="text-xs text-gray-400 mt-1">Select the member's country</div>
            </div>

            <div>
                <label for="state_district" class="block text-sm font-medium text-gray-700 mb-2">
                    State/District <span class="text-red-500">*</span>
                </label>
                <select name="state_district" id="state_district"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                    <option value="">Select State/District</option>
                </select>
                <div class="text-xs text-gray-400 mt-1">Select state or district</div>
            </div>

            <div>
                <label for="lga" class="block text-sm font-medium text-gray-700 mb-2">
                    LGA <span class="text-red-500">*</span>
                </label>
                <input type="text" name="lga" id="lga"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                <div class="text-xs text-gray-400 mt-1">Enter LGA</div>
            </div>

            <div>
                <label for="city_town" class="block text-sm font-medium text-gray-700 mb-2">
                    City/Town <span class="text-red-500">*</span>
                </label>
                <input type="text" name="city_town" id="city_town"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                <div class="text-xs text-gray-400 mt-1">Enter city or town</div>
            </div>

            <div>
                <label for="nearest_bus_stop" class="block text-sm font-medium text-gray-700 mb-2">
                    Nearest Bus Stop/Landmark <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nearest_bus_stop" id="nearest_bus_stop"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                <div class="text-xs text-gray-400 mt-1">Enter nearest bus stop or landmark</div>
            </div>

            <div>
                <label for="street_name" class="block text-sm font-medium text-gray-700 mb-2">
                    Street Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="street_name" id="street_name"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                <div class="text-xs text-gray-400 mt-1">Enter street name</div>
            </div>

            <div>
                <label for="house_no" class="block text-sm font-medium text-gray-700 mb-2">
                    House No <span class="text-red-500">*</span>
                </label>
                <input type="text" name="house_no" id="house_no"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                <div class="text-xs text-gray-400 mt-1">Enter house number</div>
            </div>
        </div>

        <!-- Business Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="business_name" class="block text-sm font-medium text-gray-700 mb-2">Name of Business</label>
                <input type="text" name="business_name" id="business_name"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                <div class="text-xs text-gray-400 mt-1">Enter business name (optional)</div>
            </div>

            <div>
                <label for="nature_of_business" class="block text-sm font-medium text-gray-700 mb-2">Nature of
                    Business</label>
                <input type="text" name="nature_of_business" id="nature_of_business"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                <div class="text-xs text-gray-400 mt-1">Enter nature of business (optional)</div>
            </div>

            <div>
                <label for="sub_sector" class="block text-sm font-medium text-gray-700 mb-2">Sub Sector</label>
                <input type="text" name="sub_sector" id="sub_sector"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                <div class="text-xs text-gray-400 mt-1">Enter business sub sector (optional)</div>
            </div>

            <div class="md:col-span-2">
                <label for="business_address" class="block text-sm font-medium text-gray-700 mb-2">Business
                    Address</label>
                <textarea name="business_address" id="business_address" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                <div class="text-xs text-gray-400 mt-1">Enter business address (optional)</div>
            </div>
        </div>

        <!-- Identity & Membership -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="identity_type" class="block text-sm font-medium text-gray-700 mb-2">
                    Identity Type <span class="text-red-500">*</span>
                </label>
                <select name="identity_type" id="identity_type"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                    <option value="">Select Identity Type</option>
                    <option value="Passport">Passport</option>
                    <option value="Voter's Card">Voter's Card</option>
                    <option value="Driver's License">Driver's License</option>
                    <option value="NIN">NIN</option>
                    <option value="National ID">National ID</option>
                    <option value="International Passport">International Passport</option>
                </select>
                <div class="text-xs text-gray-400 mt-1">Select identity document type</div>
            </div>

            <div>
                <label for="id_number" class="block text-sm font-medium text-gray-700 mb-2">
                    ID Number <span class="text-red-500">*</span>
                </label>
                <input type="text" name="id_number" id="id_number"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                <div class="text-xs text-gray-400 mt-1">Enter ID number</div>
            </div>

            <div>
                <label for="date_of_issue" class="block text-sm font-medium text-gray-700 mb-2">
                    Date of Issue <span class="text-red-500">*</span>
                </label>
                <input type="date" name="date_of_issue" id="date_of_issue"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                <div class="text-xs text-gray-400 mt-1">Select date of issue</div>
            </div>

            <div>
                <label for="registration_status" class="block text-sm font-medium text-gray-700 mb-2">
                    Registration Status <span class="text-red-500">*</span>
                </label>
                <select name="registration_status" id="registration_status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                    <option value="">Select Registration Status</option>
                    <option value="Director">Director - ₦1,000,000</option>
                    <option value="Membership">Membership - ₦12,000</option>
                </select>
                <div class="text-xs text-gray-400 mt-1">Select registration status</div>
            </div>

            <div>
                <label for="chapter" class="block text-sm font-medium text-gray-700 mb-2">
                    Chapter (State) <span class="text-red-500">*</span>
                </label>
                <select name="chapter" id="chapter"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                    <option value="">Select Chapter</option>
                    <option value="Lagos">Lagos</option>
                    <option value="Abuja">Abuja</option>
                    <option value="Kano">Kano</option>
                </select>
                <div class="text-xs text-gray-400 mt-1">Select the member's chapter or state</div>
            </div>

            <div>
                <label for="zone" class="block text-sm font-medium text-gray-700 mb-2">Zone/LGA/Region</label>
                <input type="text" name="zone" id="zone"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                <div class="text-xs text-gray-400 mt-1">Enter the member's zone, LGA, or region</div>
            </div>

            <div>
                <label for="member_type" class="block text-sm font-medium text-gray-700 mb-2">
                    Membership Type <span class="text-red-500">*</span>
                </label>
                <select name="member_type" id="member_type"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                    <option value="">Select Membership Type</option>
                    <option value="Membership Registration">Membership Registration - ₦12,000</option>
                    <option value="Renewal">Renewal - ₦12,000</option>
                </select>
                <div class="text-xs text-gray-400 mt-1">Select membership type</div>
            </div>

            <div>
                <label for="payment_type" class="block text-sm font-medium text-gray-700 mb-2">
                    Payment Type <span class="text-red-500">*</span>
                </label>
                <select name="payment_type" id="payment_type"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    required>
                    <option value="Online Payment">Online Payment</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Cash">Cash</option>
                </select>
                <div class="text-xs text-gray-400 mt-1">Select payment type</div>
            </div>
        </div>

        <!-- Documents and Photos -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">Profile Photo</label>
                <input type="file" name="photo" id="profilePhotoInput"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    accept=".jpg,.jpeg,.png">
                <div class="text-xs text-gray-400 mt-1">Upload profile photo (optional)</div>
                <img id="profilePhotoPreview" src="#" alt="Preview"
                    style="display:none;max-width:100px;margin-top:10px;" />
            </div>

            <div>
                <label for="nin_card" class="block text-sm font-medium text-gray-700 mb-2"
                    id="identity_card_label">Identity Card</label>
                <input type="file" name="nin_card" id="nin_card"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    accept=".jpg,.jpeg,.png">
                <div class="text-xs text-gray-400 mt-1" id="identity_card_help">Upload identity card image (optional)
                </div>
            </div>

            <div>
                <label for="signature" class="block text-sm font-medium text-gray-700 mb-2">Signature</label>
                <input type="file" name="signature" id="signature"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                    accept=".jpg,.jpeg,.png">
                <div class="text-xs text-gray-400 mt-1">Upload signature (optional)</div>
            </div>
        </div>

        <!-- Bank Information -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="account_name" class="block text-sm font-medium text-gray-700 mb-2">Account Name</label>
                <input type="text" name="account_name" id="account_name"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                <div class="text-xs text-gray-400 mt-1">Enter the bank account name</div>
            </div>

            <div>
                <label for="account_number" class="block text-sm font-medium text-gray-700 mb-2">Account Number</label>
                <input type="text" name="account_number" id="account_number"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                <div class="text-xs text-gray-400 mt-1">Enter the bank account number</div>
            </div>

            <div>
                <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">Bank Name</label>
                <input type="text" name="bank_name" id="bank_name"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                <div class="text-xs text-gray-400 mt-1">Enter the bank name</div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-4 mt-8">
            <button type="reset"
                class="px-6 py-2 bg-gray-600 text-white rounded-lg font-medium hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors">
                Reset
            </button>
            <button type="submit"
                class="px-6 py-2 bg-secondary text-white rounded-lg font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-colors">
                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Add Member
            </button>
        </div>
    </form>
</div>

<script>
    const countryStates = {
        "Nigeria": [
            "Abia", "Adamawa", "Akwa Ibom", "Anambra", "Bauchi", "Bayelsa", "Benue", "Borno", "Cross River", "Delta", "Ebonyi",
            "Edo", "Ekiti", "Enugu", "Gombe", "Imo", "Jigawa", "Kaduna", "Kano", "Katsina", "Kebbi", "Kogi", "Kwara", "Lagos",
            "Nasarawa", "Niger", "Ogun", "Ondo", "Osun", "Oyo", "Plateau", "Rivers", "Sokoto", "Taraba", "Yobe", "Zamfara",
            "Federal Capital Territory"
        ],
        "United States": [
            "Alabama", "Alaska", "Arizona", "Arkansas", "California", "Colorado", "Connecticut", "Delaware", "Florida",
            "Georgia", "Hawaii", "Idaho", "Illinois", "Indiana", "Iowa", "Kansas", "Kentucky", "Louisiana", "Maine",
            "Maryland", "Massachusetts", "Michigan", "Minnesota", "Mississippi", "Missouri", "Montana", "Nebraska",
            "Nevada", "New Hampshire", "New Jersey", "New Mexico", "New York", "North Carolina", "North Dakota", "Ohio",
            "Oklahoma", "Oregon", "Pennsylvania", "Rhode Island", "South Carolina", "South Dakota", "Tennessee", "Texas",
            "Utah", "Vermont", "Virginia", "Washington", "West Virginia", "Wisconsin", "Wyoming", "Washington, D.C."
        ],
        "United Kingdom": [
            "Greater London", "West Midlands", "West Yorkshire", "Greater Manchester", "Merseyside", "South Yorkshire",
            "Kent", "Surrey", "Essex", "Hampshire", "Lancashire", "Devon", "Cornwall", "Berkshire", "Cheshire",
            "Dorset", "Norfolk", "Suffolk", "Hertfordshire", "Cambridgeshire", "Bedfordshire", "Somerset",
            "Northamptonshire", "Oxfordshire", "Warwickshire", "Nottinghamshire", "Lincolnshire", "Shropshire",
            "Staffordshire", "Derbyshire", "Gloucestershire", "Herefordshire", "Worcestershire", "Isle of Wight",
            "Rutland", "East Sussex", "West Sussex", "Buckinghamshire", "Middlesex"
        ],
        "Ghana": [
            "Greater Accra", "Ashanti", "Western", "Eastern", "Central", "Northern", "Upper East", "Upper West",
            "Western North", "Bono", "Bono East", "Ahafo", "Oti", "Volta", "Western Region"
        ]
    };

    document.querySelector('select[name="country"]').addEventListener('change', function () {
        const country = this.value;
        const stateSelect = document.querySelector('select[name="state_district"]');
        stateSelect.innerHTML = '<option value="">Select State/District</option>';
        if (countryStates[country]) {
            countryStates[country].forEach(function (state) {
                const opt = document.createElement('option');
                opt.value = state;
                opt.textContent = state;
                stateSelect.appendChild(opt);
            });
        }
    });

    // Client-side NIN validation
    const addMemberForm = document.querySelector('form');
    addMemberForm.addEventListener('submit', function (e) {
        const nin = addMemberForm.querySelector('input[name="nin_number"]').value.trim();
        if (nin && !/^\d{11}$/.test(nin)) {
            alert('NIN number must be exactly 11 digits.');
            e.preventDefault();
            return false;
        }
    });

    // Profile photo preview
    const photoInput = document.getElementById('profilePhotoInput');
    const photoPreview = document.getElementById('profilePhotoPreview');
    photoInput.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                photoPreview.src = e.target.result;
                photoPreview.style.display = 'block';
            };
            reader.readAsDataURL(this.files[0]);
        } else {
            photoPreview.style.display = 'none';
        }
    });

    // Dynamic identity card label
    const identityTypeSelect = document.getElementById('identity_type');
    const identityCardLabel = document.getElementById('identity_card_label');
    const identityCardHelp = document.getElementById('identity_card_help');

    identityTypeSelect.addEventListener('change', function () {
        const selectedType = this.value;
        if (selectedType) {
            identityCardLabel.textContent = selectedType;
            identityCardHelp.textContent = `Upload ${selectedType} image (optional)`;
        } else {
            identityCardLabel.textContent = 'Identity Card';
            identityCardHelp.textContent = 'Upload identity card image (optional)';
        }
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layouts/admin.php';
?>