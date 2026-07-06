<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>24/7 Registration Portal - Login & Register</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= \App\Helpers\Url::appUrl() ?>/uploads/gafconl-favicon.png">

    <!-- Tailwind CSS -->
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
                    },
                    borderRadius: {
                        'none': '0px',
                        'sm': '4px',
                        DEFAULT: '8px',
                        'md': '12px',
                        'lg': '16px',
                        'xl': '20px',
                        '2xl': '24px',
                        '3xl': '32px',
                        'full': '9999px',
                        'button': '8px'
                    },
                    fontFamily: {
                        'sans': ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Remix Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">

    <!-- Three.js for ShaderGradient -->
    <script src="https://cdn.jsdelivr.net/npm/three@0.155.0/build/three.min.js"></script>

    <!-- Country-State-Chapter Logic -->
    <script src="<?= \App\Helpers\Url::appUrl() ?>/js/country-state-chapter.js"></script>

    <!-- Custom Styles -->
    <style>
        :where([class^="ri-"])::before {
            content: "\f3c2";
        }

        .password-strength-weak {
            color: #ef4444;
        }

        .password-strength-medium {
            color: #f59e0b;
        }

        .password-strength-strong {
            color: #10b981;
        }

        .form-transition {
            transition: all 0.3s ease-in-out;
        }

        #shader-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .content-wrapper {
            position: relative;
            z-index: 1;
        }
    </style>
</head>

<body class="min-h-screen" style="background-color: #ffffff;">
    <!-- Animated Shader Gradient Background -->
    <canvas id="shader-canvas"></canvas>

    <div class="min-h-screen flex items-center justify-center p-8 content-wrapper">
        <div class="w-full max-w-md">

            <!-- Logo -->
            <div class="mb-8 text-center">
                <img src="<?php echo \App\Helpers\Url::appUrl(); ?>/uploads/gafconl_white.png" alt="24/7 Logo"
                    class="w-48 sm:w-64 md:max-w-xs mx-auto mb-4"
                    style="filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));">
                <p class="text-gray-800 text-sm font-medium">YOUR SECURED REGISTRATION PORTAL</p>
            </div>

            <!-- Main Form Container -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 border border-gray-100">

                <!-- Tab Navigation -->
                <div class="flex mb-8">
                    <button id="loginTab"
                        class="flex-1 py-3 px-6 text-center font-medium rounded-lg transition-colors duration-200 bg-primary text-white">
                        Login
                    </button>
                    <button id="registerTab"
                        class="flex-1 py-3 px-6 text-center font-medium rounded-lg transition-colors duration-200 text-gray-600 hover:text-primary ml-2">
                        Register
                    </button>
                </div>

                <!-- Error Messages -->
                <?php if (!empty($error)): ?>
                    <div class="mb-6 text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <div id="loginForm" class="form-transition">
                    <form class="space-y-6" method="post" action="<?php echo \App\Helpers\Url::appUrl(); ?>/login">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email or Mobile Number</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                        <i class="ri-mail-line text-sm"></i>
                                    </div>
                                </div>
                                <input type="text" name="contact_number" required
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                    placeholder="Enter mobile number">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Enter mobile with country code (e.g +2348000000000)
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                        <i class="ri-lock-line text-sm"></i>
                                    </div>
                                </div>
                                <input type="password" id="loginPassword" name="password" required
                                    class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                    placeholder="Enter your password">
                                <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    onclick="togglePassword('loginPassword')">
                                    <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                        <i class="ri-eye-line text-sm"></i>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <div class="relative">
                                    <input type="checkbox" class="sr-only" id="rememberMe">
                                    <div class="w-5 h-5 border-2 border-gray-300 rounded bg-white cursor-pointer transition-colors duration-200"
                                        onclick="toggleCheckbox('rememberMe')">
                                        <div class="w-full h-full flex items-center justify-center">
                                            <i
                                                class="ri-check-line text-xs text-white opacity-0 transition-opacity duration-200"></i>
                                        </div>
                                    </div>
                                </div>
                                <span class="ml-2 text-sm text-gray-600">Remember me</span>
                            </label>
                            <a href="#" id="forgotPasswordLink"
                                class="text-sm text-primary hover:text-secondary transition-colors duration-200">
                                Forgot Password?
                            </a>
                        </div>

                        <button type="submit"
                            class="w-full bg-primary hover:bg-secondary text-white font-medium py-3 px-4 !rounded-button transition-colors duration-200 whitespace-nowrap">
                            Sign In
                        </button>
                    </form>
                </div>
                <!-- Registration Form -->
                <div id="registerForm" class="form-transition hidden">
                    <form class="space-y-6" method="post" action="<?php echo \App\Helpers\Url::appUrl(); ?>/register"
                        enctype="multipart/form-data">

                        <!-- Progress Indicator -->
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-medium text-primary" id="step-indicator">Step 1 of 6</span>
                                <span class="text-xs text-gray-500" id="step-title">Personal Information</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-primary h-2 rounded-full transition-all duration-300" id="progress-bar"
                                    style="width: 16.67%"></div>
                            </div>
                        </div>

                        <!-- Step 1: Personal Information -->
                        <div class="step-content space-y-4" id="step-1">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-user-settings-line text-sm"></i>
                                        </div>
                                    </div>
                                    <select id="title" name="title" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                        <option value="">Select your title</option>
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
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Surname *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-user-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="text" id="surname" name="surname" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Enter your surname">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-user-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="text" id="firstname" name="firstname" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Enter your first name">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Other Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-user-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="text" id="othername" name="othername"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Enter your other name (optional)">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-user-3-line text-sm"></i>
                                        </div>
                                    </div>
                                    <select id="gender" name="gender" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                        <option value="">Select your gender</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Marital Status *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-heart-line text-sm"></i>
                                        </div>
                                    </div>
                                    <select id="marital_status" name="marital_status" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                        <option value="">Select your marital status</option>
                                        <option value="Single">Single</option>
                                        <option value="Married">Married</option>
                                        <option value="Divorced">Divorced</option>
                                        <option value="Widowed">Widowed</option>
                                        <option value="Separated">Separated</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-calendar-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="date" id="dob" name="dob" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Contact Information -->
                        <div class="step-content space-y-4 hidden" id="step-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-mail-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="email" id="registerEmail" name="email" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Enter your email address">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Number *</label>
                                <div class="flex space-x-2">
                                    <select
                                        class="w-20 px-2 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        name="phone_country_code" required>
                                        <option value="+234">+234</option>
                                        <option value="+1">+1</option>
                                        <option value="+44">+44</option>
                                        <option value="+233">+233</option>
                                    </select>
                                    <div class="relative flex-1">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                                <i class="ri-phone-line text-sm"></i>
                                            </div>
                                        </div>
                                        <input type="text" id="contact_number_reg" name="contact_number" required
                                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                            placeholder="8012345678">
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">WhatsApp Number</label>
                                <div class="flex space-x-2">
                                    <select
                                        class="w-20 px-2 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        name="whatsapp_country_code">
                                        <option value="+234">+234</option>
                                        <option value="+1">+1</option>
                                        <option value="+44">+44</option>
                                        <option value="+233">+233</option>
                                    </select>
                                    <div class="relative flex-1">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                                <i class="ri-whatsapp-line text-sm"></i>
                                            </div>
                                        </div>
                                        <input type="text" id="whatsapp_number" name="whatsapp_number"
                                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                            placeholder="8012345678">
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Enter your WhatsApp number (optional)</p>
                            </div>
                        </div>

                        <!-- Step 3: Residential Details -->
                        <div class="step-content space-y-4 hidden" id="step-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Country *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-global-line text-sm"></i>
                                        </div>
                                    </div>
                                    <select id="country" name="country" required onchange="updateStateOptions()"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                        <option value="">Select your country</option>
                                        <option value="Nigeria">Nigeria</option>
                                        <option value="Ghana">Ghana</option>
                                        <option value="United Kingdom">United Kingdom</option>
                                        <option value="United States">United States</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">State/District *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-map-2-line text-sm"></i>
                                        </div>
                                    </div>
                                    <select id="state_district" name="state_district" required
                                        onchange="updateChapter()"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                        <option value="">First select your country</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">LGA *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-government-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="text" id="lga" name="lga" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Enter your LGA">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">City/Town *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-building-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="text" id="city_town" name="city_town" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Enter your city or town">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nearest Bus Stop/Landmark
                                    *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-map-pin-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="text" id="nearest_bus_stop" name="nearest_bus_stop" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Enter nearest bus stop or landmark">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Street Name *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-road-map-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="text" id="street_name" name="street_name" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Enter your street name">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">House No *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-home-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="text" id="house_no" name="house_no" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Enter your house number">
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Business Details -->
                        <div class="step-content space-y-4 hidden" id="step-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Name of Business</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-store-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="text" id="business_name" name="business_name"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Enter your business name (optional)">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Business Address</label>
                                <div class="relative">
                                    <div class="absolute top-3 left-0 pl-3 flex items-start pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-map-pin-line text-sm"></i>
                                        </div>
                                    </div>
                                    <textarea id="business_address" name="business_address"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        rows="3" placeholder="Enter your business address (optional)"></textarea>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nature of Business</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-briefcase-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="text" id="nature_of_business" name="nature_of_business"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Enter nature of your business (optional)">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sub Sector</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-folder-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="text" id="sub_sector" name="sub_sector"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Enter business sub sector (optional)">
                                </div>
                            </div>
                        </div>

                        <!-- Step 5: Identity & Membership -->
                        <div class="step-content space-y-4 hidden" id="step-5">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Identity Type *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-id-card-line text-sm"></i>
                                        </div>
                                    </div>
                                    <select id="identity_type" name="identity_type" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                        <option value="">Select your identity type</option>
                                        <option value="Passport">Passport</option>
                                        <option value="Voter's Card">Voter's Card</option>
                                        <option value="Driver's License">Driver's License</option>
                                        <option value="NIN">NIN</option>
                                        <option value="National ID">National ID</option>
                                        <option value="International Passport">International Passport</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">ID Number *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-id-card-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="text" id="id_number" name="id_number" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Enter your ID number">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Issue *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-calendar-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="date" id="date_of_issue" name="date_of_issue" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Membership Type *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-vip-crown-line text-sm"></i>
                                        </div>
                                    </div>
                                    <select id="member_type" name="member_type" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                        <option value="">Select membership type</option>
                                        <option value="Membership Registration">Membership Registration - ₦12,000
                                        </option>
                                        <option value="Renewal">Renewal - ₦12,000</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Chapter (Based on your
                                    State/District) *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-building-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="text" id="chapter" name="chapter" required readonly
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 text-sm cursor-not-allowed"
                                        placeholder="Will be set based on your residential state">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Your chapter is automatically set based on your
                                    residential state/district</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Zone/Region *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-map-2-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="text" id="zone" name="zone" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Enter your zone or region within the chapter">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Specify your zone/region within the selected
                                    chapter</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Type</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-bank-card-line text-sm"></i>
                                        </div>
                                    </div>
                                    <select id="payment_type" name="payment_type" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                        <option value="Online Payment">Online Payment</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Step 6: Documents & Security -->
                        <div class="step-content space-y-4 hidden" id="step-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Profile Photo</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-image-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="file" id="photo" name="photo" accept="image/*"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Upload your profile photo (optional)</p>
                            </div>

                            <div>
                                <label for="nin_card" class="block text-sm font-medium text-gray-700 mb-2"
                                    id="identity_card_label">Identity Card</label>
                                <input type="file" id="nin_card" name="nin_card"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
                                    accept=".jpg,.jpeg,.png">
                                <div class="text-xs text-gray-400 mt-1" id="identity_card_help">Upload identity card
                                    image (optional)</div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Signature</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-edit-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="file" id="signature" name="signature" accept="image/*"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Upload your signature (optional)</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Account Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-user-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="text" id="account_name" name="account_name"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Enter your bank account name (optional)">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Account Number</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-bank-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="text" id="account_number" name="account_number"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Enter your bank account number (optional)">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bank Name</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-building-2-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="text" id="bank_name" name="bank_name"
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Enter your bank name (optional)">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-lock-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="password" id="registerPassword" name="password" required
                                        class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Create a strong password"
                                        oninput="checkPasswordStrength(this.value)">
                                    <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                        onclick="togglePassword('registerPassword')">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-eye-line text-sm"></i>
                                        </div>
                                    </button>
                                </div>
                                <div id="passwordStrength" class="mt-2 text-xs"></div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                            <i class="ri-lock-line text-sm"></i>
                                        </div>
                                    </div>
                                    <input type="password" id="confirmPassword" name="confirmPassword" required
                                        class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                        placeholder="Confirm your password">
                                </div>
                            </div>

                            <div class="flex items-start">
                                <div class="relative mt-1">
                                    <input type="checkbox" class="sr-only" id="termsAccept" required>
                                    <div class="w-5 h-5 border-2 border-gray-300 rounded bg-white cursor-pointer transition-colors duration-200"
                                        onclick="toggleCheckbox('termsAccept')">
                                        <div class="w-full h-full flex items-center justify-center">
                                            <i
                                                class="ri-check-line text-xs text-white opacity-0 transition-opacity duration-200"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-gray-600">
                                        I agree to the <a href="<?= \App\Helpers\Url::appUrl() ?>/legal/terms-of-use"
                                            target="_blank"
                                            class="text-primary hover:text-secondary transition-colors duration-200">Terms
                                            of Service</a>
                                        and <a href="<?= \App\Helpers\Url::appUrl() ?>/legal/privacy-policy"
                                            target="_blank"
                                            class="text-primary hover:text-secondary transition-colors duration-200">Privacy
                                            Policy</a>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="flex gap-3 pt-4">
                            <button type="button" id="prevBtn"
                                class="flex-1 py-3 px-4 text-gray-600 bg-gray-200 rounded-lg hover:bg-gray-300 font-medium transition-colors duration-200 hidden">
                                Previous
                            </button>

                            <button type="button" id="nextBtn"
                                class="flex-1 py-3 px-4 bg-primary text-white rounded-lg hover:bg-secondary font-medium transition-colors duration-200">
                                Next
                            </button>

                            <button type="submit" id="submitBtn"
                                class="w-full py-3 px-4 bg-green-500 text-white rounded-lg hover:bg-green-600 font-medium transition-colors duration-200 hidden">
                                Create Account
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Forgot Password Form -->
                <div id="forgotPasswordForm" class="form-transition hidden">
                    <form class="space-y-6" method="post"
                        action="<?php echo \App\Helpers\Url::appUrl(); ?>/request-reset">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <div class="w-5 h-5 flex items-center justify-center text-gray-400">
                                        <i class="ri-mail-line text-sm"></i>
                                    </div>
                                </div>
                                <input type="email" name="email" required
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                    placeholder="Enter your email">
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full bg-primary hover:bg-secondary text-white font-medium py-3 px-4 !rounded-button transition-colors duration-200 whitespace-nowrap">
                            Send Reset Link
                        </button>
                    </form>
                </div>

                <!-- Help Section -->
                <div class="mt-8 text-center">
                    <p class="text-sm text-gray-600">
                        Need help? <a href="#"
                            class="text-primary hover:text-secondary transition-colors duration-200">Contact Support</a>
                    </p>

                    <!-- Offline Form Download Section -->
                    <div class="mt-6 p-4 bg-primary/5 border border-primary/20 rounded-lg">
                        <h4 class="text-sm font-medium text-blue-900 mb-2">
                            <i class="ri-download-line mr-2"></i>
                            Remote Area Access
                        </h4>
                        <p class="text-xs text-blue-800 mb-3">
                            Members in areas with limited internet can download forms offline
                        </p>
                        <div class="space-y-2">
                            <a href="<?= \App\Helpers\Url::appUrl() ?>/registration/download-form"
                                class="inline-flex items-center text-sm text-primary hover:text-secondary transition-colors">
                                <i class="ri-file-pdf-line mr-2"></i>
                                Download Registration Form (PDF)
                            </a>
                            <br>
                            <a href="<?= \App\Helpers\Url::appUrl() ?>/registration/offline-submission"
                                class="inline-flex items-center text-sm text-primary hover:text-secondary transition-colors">
                                <i class="ri-upload-line mr-2"></i>
                                Submit Completed Form
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-xs text-white">
                <p>© <?= date('Y') ?> 24/7 Registration Portal. All rights reserved |
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/legal/privacy-policy"  class="hover:text-primary-light transition-colors duration-200">Privacy</a> •
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/legal/terms-of-use"    class="hover:text-primary-light transition-colors duration-200">Terms</a> •
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/legal/data-compliance" class="hover:text-primary-light transition-colors duration-200">Compliance</a> •
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/legal/ip-infringement" class="hover:text-primary-light transition-colors duration-200">IP Policy</a>
                </p>
            </div>
        </div>
    </div>
    </div>
    <!-- JavaScript -->
    <script id="tabSwitcher">
        document.addEventListener('DOMContentLoaded', function () {
            const loginTab = document.getElementById('loginTab');
            const registerTab = document.getElementById('registerTab');
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            const forgotPasswordLink = document.getElementById('forgotPasswordLink');

            // Tab switching functionality
            loginTab.addEventListener('click', function () {
                loginTab.classList.add('bg-primary', 'text-white');
                loginTab.classList.remove('text-gray-600');
                registerTab.classList.remove('bg-primary', 'text-white');
                registerTab.classList.add('text-gray-600');
                loginForm.classList.remove('hidden');
                registerForm.classList.add('hidden');
                forgotPasswordForm.classList.add('hidden');
            });

            registerTab.addEventListener('click', function () {
                registerTab.classList.add('bg-primary', 'text-white');
                registerTab.classList.remove('text-gray-600');
                loginTab.classList.remove('bg-primary', 'text-white');
                loginTab.classList.add('text-gray-600');
                registerForm.classList.remove('hidden');
                loginForm.classList.add('hidden');
                forgotPasswordForm.classList.add('hidden');
            });

            // Forgot password link
            forgotPasswordLink.addEventListener('click', function (e) {
                e.preventDefault();
                forgotPasswordForm.classList.remove('hidden');
                loginForm.classList.add('hidden');
                registerForm.classList.add('hidden');

                // Reset tab states
                loginTab.classList.remove('bg-primary', 'text-white');
                loginTab.classList.add('text-gray-600');
                registerTab.classList.remove('bg-primary', 'text-white');
                registerTab.classList.add('text-gray-600');
            });

            // Multi-step form functionality
            let currentStep = 1;
            const totalSteps = 6;
            const stepTitles = [
                'Personal Information',
                'Contact Information',
                'Residential Details',
                'Business Details',
                'Identity & Membership',
                'Documents & Security'
            ];

            const nextBtn = document.getElementById('nextBtn');
            const prevBtn = document.getElementById('prevBtn');
            const submitBtn = document.getElementById('submitBtn');
            const stepIndicator = document.getElementById('step-indicator');
            const stepTitle = document.getElementById('step-title');
            const progressBar = document.getElementById('progress-bar');

            // States by country data
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

            function showStep(step) {
                // Hide all steps
                for (let i = 1; i <= totalSteps; i++) {
                    const stepElement = document.getElementById(`step-${i}`);
                    if (stepElement) {
                        stepElement.classList.add('hidden');
                    }
                }

                // Show current step
                const currentStepElement = document.getElementById(`step-${step}`);
                if (currentStepElement) {
                    currentStepElement.classList.remove('hidden');
                }

                // Update progress
                stepIndicator.textContent = `Step ${step} of ${totalSteps}`;
                stepTitle.textContent = stepTitles[step - 1];
                progressBar.style.width = `${((step / totalSteps) * 100)}%`;

                // Show/hide navigation buttons
                prevBtn.classList.toggle('hidden', step === 1);
                nextBtn.classList.toggle('hidden', step === totalSteps);
                submitBtn.classList.toggle('hidden', step !== totalSteps);
            }

            function validateStep(step) {
                const stepElement = document.getElementById(`step-${step}`);
                const requiredFields = stepElement.querySelectorAll('input[required], select[required], textarea[required]');
                let isValid = true;

                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        showError(field.id, 'This field is required');
                        isValid = false;
                    }
                });

                // Additional validation for specific steps
                if (step === 2) {
                    const email = document.getElementById('registerEmail').value;
                    if (email && !isValidEmail(email)) {
                        showError('registerEmail', 'Please enter a valid email address');
                        isValid = false;
                    }
                }

                if (step === 6) {
                    const password = document.getElementById('registerPassword').value;
                    const confirmPassword = document.getElementById('confirmPassword').value;
                    const ninNumber = document.getElementById('id_number').value;

                    if (password && !isValidPassword(password)) {
                        showError('registerPassword', 'Password must be 8+ characters with letters, numbers, and special characters');
                        isValid = false;
                    }

                    if (password && confirmPassword && password !== confirmPassword) {
                        showError('confirmPassword', 'Passwords do not match');
                        isValid = false;
                    }

                    if (ninNumber && !/^\d{11}$/.test(ninNumber)) {
                        showError('id_number', 'NIN must be exactly 11 digits');
                        isValid = false;
                    }
                }

                return isValid;
            }

            function isValidEmail(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            }

            function isValidPassword(password) {
                return /^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/.test(password);
            }

            function showError(fieldId, message) {
                const field = document.getElementById(fieldId);
                if (!field) return;

                const existingError = document.getElementById(fieldId + 'Error');
                if (existingError) existingError.remove();

                const errorDiv = document.createElement('div');
                errorDiv.id = fieldId + 'Error';
                errorDiv.className = 'text-red-500 text-xs mt-1';
                errorDiv.textContent = message;
                field.parentNode.appendChild(errorDiv);
                setTimeout(() => errorDiv.remove(), 5000);

                // Add error styling to field
                field.classList.add('border-red-500');
                setTimeout(() => field.classList.remove('border-red-500'), 5000);
            }

            // Multi-step navigation
            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    if (validateStep(currentStep)) {
                        currentStep++;
                        showStep(currentStep);
                    }
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    currentStep--;
                    showStep(currentStep);
                });
            }

            // Country/State dropdown functionality
            const countrySelect = document.getElementById('country');
            const stateDistrictSelect = document.getElementById('state_district');

            if (countrySelect && stateDistrictSelect) {
                countrySelect.addEventListener('change', function () {
                    const country = this.value;
                    stateDistrictSelect.innerHTML = '<option value="">Select State/District</option>';

                    if (countryStates[country]) {
                        countryStates[country].forEach(state => {
                            const option = document.createElement('option');
                            option.value = state;
                            option.textContent = state;
                            stateDistrictSelect.appendChild(option);
                        });
                    }
                });
            }

            // Final form submission validation
            if (registerForm) {
                registerForm.addEventListener('submit', (e) => {
                    if (!validateStep(6)) {
                        e.preventDefault();
                        return;
                    }

                    // Show loading state
                    if (submitBtn) {
                        submitBtn.textContent = 'Creating Account...';
                        submitBtn.disabled = true;
                    }
                });
            }

            // Initialize first step
            showStep(1);
        });
    </script>

    <script id="passwordToggle">
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'ri-eye-off-line text-sm';
            } else {
                input.type = 'password';
                icon.className = 'ri-eye-line text-sm';
            }
        }
    </script>

    <script id="checkboxToggle">
        function toggleCheckbox(checkboxId) {
            const checkbox = document.getElementById(checkboxId);
            const container = checkbox.nextElementSibling;
            const icon = container.querySelector('i');

            checkbox.checked = !checkbox.checked;

            if (checkbox.checked) {
                container.classList.add('bg-primary', 'border-primary');
                container.classList.remove('border-gray-300');
                icon.classList.remove('opacity-0');
            } else {
                container.classList.remove('bg-primary', 'border-primary');
                container.classList.add('border-gray-300');
                icon.classList.add('opacity-0');
            }
        }
    </script>

    <script id="passwordStrength">
        function checkPasswordStrength(password) {
            const strengthDiv = document.getElementById('passwordStrength');
            let strength = 0;
            let feedback = [];

            if (password.length >= 8) strength++;
            else feedback.push('At least 8 characters');

            if (/[a-z]/.test(password)) strength++;
            else feedback.push('One lowercase letter');

            if (/[A-Z]/.test(password)) strength++;
            else feedback.push('One uppercase letter');

            if (/[0-9]/.test(password)) strength++;
            else feedback.push('One number');

            if (/[^A-Za-z0-9]/.test(password)) strength++;
            else feedback.push('One special character');

            let strengthText = '';
            let strengthClass = '';

            if (strength < 2) {
                strengthText = 'Weak';
                strengthClass = 'password-strength-weak';
            } else if (strength < 4) {
                strengthText = 'Medium';
                strengthClass = 'password-strength-medium';
            } else {
                strengthText = 'Strong';
                strengthClass = 'password-strength-strong';
            }

            if (password.length > 0) {
                strengthDiv.innerHTML = `<div class="flex items-center justify-between"><span class="${strengthClass}">Password strength: ${strengthText}</span></div>${feedback.length > 0 ? `<div class="text-gray-500 mt-1">Missing: ${feedback.join(', ')}</div>` : ''}`;
            } else {
                strengthDiv.innerHTML = '';
            }
        }
    </script>

    <script id="identityCardLabel">
        document.addEventListener('DOMContentLoaded', function () {
            const identityTypeSelect = document.getElementById('identity_type');
            const identityCardLabel = document.getElementById('identity_card_label');
            const identityCardHelp = document.getElementById('identity_card_help');

            if (identityTypeSelect && identityCardLabel && identityCardHelp) {
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
            }
        });
    </script>

    <!-- ShaderGradient Animation Script -->
    <script>
        // Initialize ShaderGradient on page load
        if (typeof THREE !== 'undefined') {
            const canvas = document.getElementById('shader-canvas');
            if (canvas) {
                const scene = new THREE.Scene();
                const camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 1000);
                camera.position.set(-1.4, 0, 3.6);
                camera.lookAt(0, 0, 0);

                const renderer = new THREE.WebGLRenderer({ canvas: canvas, antialias: true, alpha: true });
                renderer.setSize(window.innerWidth, window.innerHeight);
                renderer.setPixelRatio(window.devicePixelRatio);

                // Shader material based on ShaderGradient configuration
                const vertexShader = `
                    varying vec2 vUv;
                    varying vec3 vPosition;
                    uniform float uTime;
                    
                    void main() {
                        vUv = uv;
                        vPosition = position;
                        gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
                    }
                `;

                const fragmentShader = `
                    uniform float uTime;
                    uniform vec3 uColor1;
                    uniform vec3 uColor2;
                    uniform vec3 uColor3;
                    uniform float uDensity;
                    uniform float uFrequency;
                    uniform float uSpeed;
                    uniform float uStrength;
                    varying vec2 vUv;
                    varying vec3 vPosition;
                    
                    // Noise function
                    vec3 mod289(vec3 x) { return x - floor(x * (1.0 / 289.0)) * 289.0; }
                    vec4 mod289(vec4 x) { return x - floor(x * (1.0 / 289.0)) * 289.0; }
                    vec4 permute(vec4 x) { return mod289(((x*34.0)+1.0)*x); }
                    vec4 taylorInvSqrt(vec4 r) { return 1.79284291400159 - 0.85373472095314 * r; }
                    
                    float snoise(vec3 v) {
                        const vec2 C = vec2(1.0/6.0, 1.0/3.0);
                        const vec4 D = vec4(0.0, 0.5, 1.0, 2.0);
                        vec3 i  = floor(v + dot(v, C.yyy));
                        vec3 x0 = v - i + dot(i, C.xxx);
                        vec3 g = step(x0.yzx, x0.xyz);
                        vec3 l = 1.0 - g;
                        vec3 i1 = min(g.xyz, l.zxy);
                        vec3 i2 = max(g.xyz, l.zxy);
                        vec3 x1 = x0 - i1 + C.xxx;
                        vec3 x2 = x0 - i2 + C.yyy;
                        vec3 x3 = x0 - D.yyy;
                        i = mod289(i);
                        vec4 p = permute(permute(permute(i.z + vec4(0.0, i1.z, i2.z, 1.0)) + i.y + vec4(0.0, i1.y, i2.y, 1.0)) + i.x + vec4(0.0, i1.x, i2.x, 1.0));
                        float n_ = 0.142857142857;
                        vec3 ns = n_ * D.wyz - D.xzx;
                        vec4 j = p - 49.0 * floor(p * ns.z * ns.z);
                        vec4 x_ = floor(j * ns.z);
                        vec4 y_ = floor(j - 7.0 * x_);
                        vec4 x = x_ *ns.x + ns.yyyy;
                        vec4 y = y_ *ns.x + ns.yyyy;
                        vec4 h = 1.0 - abs(x) - abs(y);
                        vec4 b0 = vec4(x.xy, y.xy);
                        vec4 b1 = vec4(x.zw, y.zw);
                        vec4 s0 = floor(b0)*2.0 + 1.0;
                        vec4 s1 = floor(b1)*2.0 + 1.0;
                        vec4 sh = -step(h, vec4(0.0));
                        vec4 a0 = b0.xzyw + s0.xzyw*sh.xxyy;
                        vec4 a1 = b1.xzyw + s1.xzyw*sh.zzww;
                        vec3 p0 = vec3(a0.xy, h.x);
                        vec3 p1 = vec3(a0.zw, h.y);
                        vec3 p2 = vec3(a1.xy, h.z);
                        vec3 p3 = vec3(a1.zw, h.w);
                        vec4 norm = taylorInvSqrt(vec4(dot(p0,p0), dot(p1,p1), dot(p2,p2), dot(p3,p3)));
                        p0 *= norm.x;
                        p1 *= norm.y;
                        p2 *= norm.z;
                        p3 *= norm.w;
                        vec4 m = max(0.6 - vec4(dot(x0,x0), dot(x1,x1), dot(x2,x2), dot(x3,x3)), 0.0);
                        m = m * m;
                        return 42.0 * dot(m*m, vec4(dot(p0,x0), dot(p1,x1), dot(p2,x2), dot(p3,x3)));
                    }
                    
                    void main() {
                        vec2 uv = vUv;
                        float time = uTime * uSpeed;
                        
                        // Create animated noise
                        float noise1 = snoise(vec3(uv * uFrequency, time * 0.5)) * uStrength;
                        float noise2 = snoise(vec3(uv * uFrequency * 0.5 + 100.0, time * 0.3)) * uStrength * 0.5;
                        float noise = (noise1 + noise2) * uDensity;
                        
                        // Mix colors based on noise and position
                        vec3 color = mix(uColor1, uColor2, uv.x + noise * 0.3);
                        color = mix(color, uColor3, uv.y + noise * 0.2);
                        
                        gl_FragColor = vec4(color, 1.0);
                    }
                `;

                const geometry = new THREE.PlaneGeometry(10, 10, 32, 32);
                const material = new THREE.ShaderMaterial({
                    vertexShader: vertexShader,
                    fragmentShader: fragmentShader,
                    uniforms: {
                        uTime: { value: 0 },
                        uColor1: { value: new THREE.Color('#ff5005') },
                        uColor2: { value: new THREE.Color('#dbba95') },
                        uColor3: { value: new THREE.Color('#d0bce1') },
                        uDensity: { value: 1.3 },
                        uFrequency: { value: 5.5 },
                        uSpeed: { value: 0.4 },
                        uStrength: { value: 4.0 }
                    }
                });

                const plane = new THREE.Mesh(geometry, material);
                plane.rotation.set(0, 10 * Math.PI / 180, 50 * Math.PI / 180);
                scene.add(plane);

                // Animation loop
                function animate() {
                    requestAnimationFrame(animate);
                    material.uniforms.uTime.value += 0.01;
                    renderer.render(scene, camera);
                }
                animate();

                // Handle window resize
                window.addEventListener('resize', () => {
                    const width = window.innerWidth;
                    const height = window.innerHeight;
                    camera.aspect = width / height;
                    camera.updateProjectionMatrix();
                    renderer.setSize(width, height);
                });
            }
        }
    </script>
</body>

</html>