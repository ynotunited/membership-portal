<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Offline Form - GAFCONL</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= \App\Helpers\Url::appUrl() ?>/uploads/gafconl-icon.png">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#1e40af'
                    }
                }
            }
        }
    </script>
    
    <!-- Remix Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
</head>
<body class="min-h-screen bg-gray-50">
    <div class="min-h-screen flex">
        
        <!-- Left Side - Branding -->
        <div class="hidden lg:flex lg:w-2/5 relative overflow-hidden" 
             style="background-image: url('<?php echo \App\Helpers\Url::appUrl(); ?>/uploads/751a734e99a5dacc5b453444cb3390c2.jpg'); background-size: cover; background-position: center;">
            
            <div class="absolute inset-0 bg-gradient-to-br from-primary/20 to-secondary/30"></div>
            
            <div class="relative z-10 flex flex-col justify-center items-start p-12 text-white">
                <div class="mb-8">
                    <!-- GAFCONL Logo -->
                    <img src="<?php echo \App\Helpers\Url::appUrl(); ?>/uploads/gafconl_white-447x87.png" 
                         alt="GAFCONL Logo" 
                         class="max-w-xs mb-6">
                    
                    <h1 class="text-3xl font-bold mb-4">Submit Offline Form</h1>
                    <p class="text-xl font-light leading-relaxed">
                        Upload your completed registration form and we'll process it within 24-48 hours.
                    </p>
                </div>
                
                <div class="space-y-4 text-sm opacity-90">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 flex items-center justify-center">
                            <i class="ri-upload-line"></i>
                        </div>
                        <span>Easy form upload</span>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 flex items-center justify-center">
                            <i class="ri-time-line"></i>
                        </div>
                        <span>24-48 hour processing</span>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 flex items-center justify-center">
                            <i class="ri-notification-line"></i>
                        </div>
                        <span>Email notifications</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Side - Form -->
        <div class="flex-1 flex items-center justify-center p-8">
            <div class="w-full max-w-md">
                
                <!-- Back to Login Link -->
                <div class="text-center mb-8">
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/login" 
                       class="text-primary hover:text-secondary transition-colors">
                        <i class="ri-arrow-left-line mr-2"></i>
                        Back to Login
                    </a>
                </div>
                
                <!-- Header -->
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Submit Completed Form</h2>
                    <p class="text-gray-600">Upload your filled registration form for processing</p>
                </div>
                
                <!-- Alert Messages -->
                <?php if (!empty($error)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                        <div class="flex items-center">
                            <i class="ri-error-warning-line mr-2"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                        <div class="flex items-center">
                            <i class="ri-check-line mr-2"></i>
                            <?= htmlspecialchars($success) ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Upload Form -->
                <form method="post" enctype="multipart/form-data" class="space-y-6">
                    
                    <!-- Reference Number -->
                    <div>
                        <label for="reference_number" class="block text-sm font-medium text-gray-700 mb-2">
                            Reference Number *
                        </label>
                        <input type="text" 
                               id="reference_number" 
                               name="reference_number" 
                               required
                               placeholder="e.g., REG-20241201-ABC123"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                        <p class="text-sm text-gray-500 mt-1">
                            Enter the reference number from your downloaded form
                        </p>
                    </div>
                    
                    <!-- File Upload -->
                    <div>
                        <label for="completed_form" class="block text-sm font-medium text-gray-700 mb-2">
                            Completed Form *
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary transition-colors">
                            <input type="file" 
                                   id="completed_form" 
                                   name="completed_form" 
                                   required
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   class="hidden"
                                   onchange="updateFileName(this)">
                            
                            <div class="space-y-2">
                                <i class="ri-upload-line text-3xl text-gray-400"></i>
                                <div>
                                    <label for="completed_form" class="cursor-pointer">
                                        <span class="text-primary hover:text-secondary font-medium">
                                            Click to upload
                                        </span>
                                        <span class="text-gray-500"> or drag and drop</span>
                                    </label>
                                </div>
                                <p class="text-sm text-gray-500">
                                    PDF, JPEG, or PNG files accepted
                                </p>
                            </div>
                        </div>
                        <div id="file-info" class="hidden mt-2 p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between">
                                <span id="file-name" class="text-sm text-gray-700"></span>
                                <button type="button" onclick="clearFile()" class="text-red-500 hover:text-red-700">
                                    <i class="ri-close-line"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Instructions -->
                    <div class="bg-primary/5 border border-primary/20 rounded-lg p-4">
                        <h4 class="font-medium text-blue-900 mb-2">
                            <i class="ri-information-line mr-2"></i>
                            Instructions
                        </h4>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li>• Ensure your form is completely filled out</li>
                            <li>• Attach all required documents</li>
                            <li>• Take clear photos or scan the form</li>
                            <li>• We'll contact you within 24-48 hours</li>
                        </ul>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" 
                            class="w-full bg-primary hover:bg-secondary text-white font-medium py-3 px-4 rounded-lg transition-colors focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        <i class="ri-upload-line mr-2"></i>
                        Submit Form
                    </button>
                </form>
                
                <!-- Download Form Link -->
                <div class="text-center mt-6">
                    <p class="text-gray-600 mb-2">Don't have the form yet?</p>
                    <a href="<?= \App\Helpers\Url::appUrl() ?>/registration/download-form" 
                       class="inline-flex items-center text-primary hover:text-secondary transition-colors">
                        <i class="ri-download-line mr-2"></i>
                        Download Registration Form
                    </a>
                </div>
                
                <!-- Contact Information -->
                <div class="text-center mt-8 pt-6 border-t border-gray-200">
                    <p class="text-sm text-gray-500 mb-2">Need help? Contact us:</p>
                    <div class="space-y-1 text-sm text-gray-600">
                        <p><i class="ri-phone-line mr-2"></i>+234 801 234 5678</p>
                        <p><i class="ri-mail-line mr-2"></i>support@gafconl.com</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function updateFileName(input) {
            const file = input.files[0];
            if (file) {
                document.getElementById('file-name').textContent = file.name;
                document.getElementById('file-info').classList.remove('hidden');
            }
        }
        
        function clearFile() {
            document.getElementById('completed_form').value = '';
            document.getElementById('file-info').classList.add('hidden');
        }
        
        // Drag and drop functionality
        const dropZone = document.querySelector('.border-dashed');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight(e) {
            dropZone.classList.add('border-primary', 'bg-primary/5');
        }
        
        function unhighlight(e) {
            dropZone.classList.remove('border-primary', 'bg-primary/5');
        }
        
        dropZone.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                document.getElementById('completed_form').files = files;
                updateFileName(document.getElementById('completed_form'));
            }
        }
    </script>
</body>
</html> 