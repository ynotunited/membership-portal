<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MemberModel;

class RegistrationController extends BaseController
{
    public function register()
    {
        $error = $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'surname' => \App\Helpers\SecurityHelper::sanitizeString($_POST['surname'] ?? ''),
                'firstname' => \App\Helpers\SecurityHelper::sanitizeString($_POST['firstname'] ?? ''),
                'email' => \App\Helpers\SecurityHelper::sanitizeString($_POST['email'] ?? ''),
                'phone' => \App\Helpers\SecurityHelper::sanitizeString($_POST['phone'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'password_confirm' => $_POST['password_confirm'] ?? ''
            ];
            $file = $_FILES['photo'] ?? null;
            if (!$data['surname'] || !$data['firstname'] || !$data['email'] || !$data['phone'] || !$data['password'] || !$data['password_confirm']) {
                $error = 'All fields are required.';
            } elseif (!\App\Helpers\SecurityHelper::validateEmail($data['email'])) {
                $error = 'Invalid email address format.';
            } elseif (!\App\Helpers\SecurityHelper::validatePhone($data['phone'])) {
                $error = 'Invalid phone number format.';
            } elseif ($data['password'] !== $data['password_confirm']) {
                $error = 'Passwords do not match.';
            } elseif (strlen($data['password']) < 8) {
                $error = 'Password must be at least 8 characters long.';
            } else {
                $model = new MemberModel();
                $result = $model->register($data, $file);
                if ($result['success']) {
                    $success = 'Registration successful! Please check your email to verify your account.';
                } else {
                    $error = $result['message'] ?? 'Failed to register.';
                }
            }
        }
        $this->render('registration/register', ['error' => $error, 'success' => $success]);
    }

    /**
     * Download registration form as PDF for offline use
     */
    public function downloadForm()
    {
        // Generate unique reference number
        $referenceNumber = 'REG-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

        // Create PDF using TCPDF
        $pdf = $this->generateRegistrationFormPDF($referenceNumber);

        // Output PDF for download
        $pdf->Output('registration_form_' . $referenceNumber . '.pdf', 'D');
        exit;
    }

    /**
     * Handle offline form submission (uploaded completed form)
     */
    public function submitOfflineForm()
    {
        $error = $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $referenceNumber = \App\Helpers\SecurityHelper::sanitizeString($_POST['reference_number'] ?? '');
            $uploadedForm = $_FILES['completed_form'] ?? null;

            if (!$referenceNumber || !$uploadedForm) {
                $error = 'Reference number and completed form are required.';
            } elseif (!preg_match('/^REG-\d{8}-[A-Z0-9]{6}$/', $referenceNumber)) {
                $error = 'Invalid reference number format. Must match REG-YYYYMMDD-XXXXXX';
            } else {
                // Use SecurityHelper to validate and upload the file securely
                $allowedExts = ['pdf', 'jpeg', 'jpg', 'png'];
                $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png'];
                $maxSize = 10 * 1024 * 1024; // 10MB
                $dest = __DIR__ . '/../../public/uploads/offline_forms';
                
                $res = \App\Helpers\SecurityHelper::handleSecureUpload($uploadedForm, $allowedExts, $allowedMimes, $maxSize, $dest, 'offline_form_' . $referenceNumber . '_');
                
                if (is_array($res) && isset($res['error'])) {
                    $error = $res['error'];
                } else {
                    $filepath = rtrim($dest, '/\\') . '/' . $res;
                    // Process the offline form submission
                    $result = $this->processOfflineForm($referenceNumber, $res, $filepath, $uploadedForm['size'], mime_content_type($filepath));
                    if ($result['success']) {
                        $success = 'Offline form submitted successfully! We will review and contact you within 24-48 hours.';
                    } else {
                        $error = $result['message'] ?? 'Failed to process offline form.';
                    }
                }
            }
        }

        $this->render('registration/offline_submission', [
            'error' => $error,
            'success' => $success
        ]);
    }

    /**
     * Generate PDF content for registration form using TCPDF
     */
    private function generateRegistrationFormPDF($referenceNumber)
    {
        // Create new PDF document
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('GAFCONL System');
        $pdf->SetAuthor('GAFCONL');
        $pdf->SetTitle('Membership Registration Form');
        $pdf->SetSubject('GAFCONL Membership Registration');

        // Set default header data
        $pdf->SetHeaderData('', 0, 'GAFCONL MEMBERSHIP REGISTRATION FORM', 'Complete this form and return to our office or upload online');

        // Set header and footer fonts
        $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 10);

        // Reference number section
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(0, 10, 'Reference Number: ' . $referenceNumber . ' | Date: ' . date('F j, Y'), 1, 1, 'L', true);
        $pdf->Ln(5);

        // Instructions
        $pdf->SetFillColor(255, 243, 205);
        $pdf->SetTextColor(133, 100, 4);
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 8, 'INSTRUCTIONS:', 1, 1, 'L', true);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(0, 6, "1. Fill out all fields completely\n2. Attach required documents (ID, photo, signature)\n3. Return this form to our office or scan and upload online\n4. Keep your reference number for tracking", 1, 'L', false);
        $pdf->Ln(5);

        // STEP 1: Personal Information Section
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(59, 130, 246);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, 'STEP 1: PERSONAL INFORMATION', 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Ln(2);

        // Title
        $pdf->Cell(40, 8, 'Title *:', 0, 0);
        $pdf->Cell(0, 8, '[ ] Mr  [ ] Mrs  [ ] Ms  [ ] Dr  [ ] Chief  [ ] Prof  [ ] Engr  [ ] Barr  [ ] Alhaji  [ ] Pastor  [ ] Rev', 0, 1);

        // Surname
        $pdf->Cell(40, 8, 'Surname *:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        // First Name
        $pdf->Cell(40, 8, 'First Name *:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        // Other Name
        $pdf->Cell(40, 8, 'Other Name:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        // Gender
        $pdf->Cell(40, 8, 'Gender *:', 0, 0);
        $pdf->Cell(0, 8, '[ ] Male  [ ] Female', 0, 1);

        // Marital Status
        $pdf->Cell(40, 8, 'Marital Status *:', 0, 0);
        $pdf->Cell(0, 8, '[ ] Single  [ ] Married  [ ] Divorced  [ ] Widowed  [ ] Separated', 0, 1);

        // Date of Birth
        $pdf->Cell(40, 8, 'Date of Birth *:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        $pdf->Ln(5);

        // STEP 2: Contact Information Section
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(59, 130, 246);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, 'STEP 2: CONTACT INFORMATION', 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Ln(2);

        // Email
        $pdf->Cell(40, 8, 'Email Address *:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        // Contact Number
        $pdf->Cell(40, 8, 'Contact Number *:', 0, 0);
        $pdf->Cell(0, 8, 'Country Code: [ ] +234  [ ] +1  [ ] +44  [ ] +233', 0, 1);
        $pdf->Cell(40, 8, '', 0, 0);
        $pdf->Cell(0, 8, 'Number: _________________________________', 0, 1);

        // WhatsApp Number
        $pdf->Cell(40, 8, 'WhatsApp Number:', 0, 0);
        $pdf->Cell(0, 8, 'Country Code: [ ] +234  [ ] +1  [ ] +44  [ ] +233', 0, 1);
        $pdf->Cell(40, 8, '', 0, 0);
        $pdf->Cell(0, 8, 'Number: _________________________________', 0, 1);

        $pdf->Ln(5);

        // STEP 3: Residential Details Section
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(59, 130, 246);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, 'STEP 3: RESIDENTIAL DETAILS', 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Ln(2);

        // Country
        $pdf->Cell(40, 8, 'Country *:', 0, 0);
        $pdf->Cell(0, 8, '[ ] Nigeria  [ ] United States  [ ] United Kingdom  [ ] Ghana', 0, 1);

        // State/District
        $pdf->Cell(40, 8, 'State/District *:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        // LGA
        $pdf->Cell(40, 8, 'LGA *:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        // City/Town
        $pdf->Cell(40, 8, 'City/Town *:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        // Nearest Bus Stop
        $pdf->Cell(40, 8, 'Nearest Bus Stop *:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        // Street Name
        $pdf->Cell(40, 8, 'Street Name *:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        // House No
        $pdf->Cell(40, 8, 'House No *:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        $pdf->Ln(5);

        // STEP 4: Business Details Section
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(59, 130, 246);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, 'STEP 4: BUSINESS DETAILS', 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Ln(2);

        // Business Name
        $pdf->Cell(40, 8, 'Name of Business:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        // Business Address
        $pdf->Cell(40, 8, 'Business Address:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);
        $pdf->Cell(40, 8, '', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        // Nature of Business
        $pdf->Cell(40, 8, 'Nature of Business:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        // Sub Sector
        $pdf->Cell(40, 8, 'Sub Sector:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        // Chapter
        $pdf->Cell(40, 8, 'Chapter (State) *:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        // Zone
        $pdf->Cell(40, 8, 'Zone/LGA/Region:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        $pdf->Ln(5);

        // STEP 5: Identity & Membership Section
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(59, 130, 246);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, 'STEP 5: IDENTITY & MEMBERSHIP', 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Ln(2);

        // Identity Type
        $pdf->Cell(40, 8, 'Identity Type *:', 0, 0);
        $pdf->Cell(0, 8, '[ ] Passport  [ ] Voter\'s Card  [ ] Driver\'s License  [ ] NIN', 0, 1);
        $pdf->Cell(40, 8, '', 0, 0);
        $pdf->Cell(0, 8, '[ ] National ID  [ ] International Passport', 0, 1);

        // ID Number
        $pdf->Cell(40, 8, 'ID Number *:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        // Date of Issue
        $pdf->Cell(40, 8, 'Date of Issue *:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        // Registration Status
        $pdf->Cell(40, 8, 'Registration Status *:', 0, 0);
        $pdf->Cell(0, 8, '[ ] Director (₦1,000,000)  [ ] Membership (₦12,000)', 0, 1);

        // Membership Type
        $pdf->Cell(40, 8, 'Membership Type *:', 0, 0);
        $pdf->Cell(0, 8, '[ ] Membership Registration (₦12,000)  [ ] Renewal (₦12,000)', 0, 1);

        // Payment Type
        $pdf->Cell(40, 8, 'Payment Type *:', 0, 0);
        $pdf->Cell(0, 8, '[ ] Online Payment', 0, 1);

        $pdf->Ln(5);

        // STEP 6: Documents & Security Section
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(59, 130, 246);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, 'STEP 6: DOCUMENTS & SECURITY', 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Ln(2);

        // Profile Photo
        $pdf->Cell(40, 8, 'Profile Photo:', 0, 0);
        $pdf->Cell(0, 8, '[ ] Attached', 0, 1);
        $pdf->Cell(40, 8, '', 0, 0);
        $pdf->Cell(0, 8, 'Photo should be: Clear, recent, 2x2 inches, good quality', 0, 1);

        // Identity Card
        $pdf->Cell(40, 8, 'Identity Card:', 0, 0);
        $pdf->Cell(0, 8, '[ ] Attached', 0, 1);

        // Signature
        $pdf->Cell(40, 8, 'Signature:', 0, 0);
        $pdf->Cell(0, 8, '[ ] Attached', 0, 1);

        // Bank Details
        $pdf->Cell(40, 8, 'Account Name:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        $pdf->Cell(40, 8, 'Account Number:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        $pdf->Cell(40, 8, 'Bank Name:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        // Password
        $pdf->Cell(40, 8, 'Password *:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);
        $pdf->Cell(40, 8, '', 0, 0);
        $pdf->Cell(0, 8, '(Strong password: 8+ chars, letters, numbers, special chars)', 0, 1);

        // Confirm Password
        $pdf->Cell(40, 8, 'Confirm Password *:', 0, 0);
        $pdf->Cell(0, 8, '_________________________________', 0, 1);

        $pdf->Ln(5);

        // Declaration Section
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(59, 130, 246);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, 'DECLARATION', 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Ln(2);

        $pdf->Cell(8, 8, '[ ]', 0, 0);
        $pdf->Cell(0, 8, 'I declare that all information provided is true and accurate to the best of my knowledge', 0, 1);

        $pdf->Cell(8, 8, '[ ]', 0, 0);
        $pdf->Cell(0, 8, 'I agree to the terms and conditions of GAFCONL membership', 0, 1);

        $pdf->Ln(5);

        // Required Documents Section
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(59, 130, 246);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, 'REQUIRED DOCUMENTS', 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Ln(2);

        $pdf->MultiCell(0, 6, "Please attach the following documents:\n• Valid government-issued ID (Passport, Driver's License, National ID, NIN, Voter's Card)\n• Recent passport photograph (2x2 inches)\n• Signature image\n• Proof of address (Utility bill, Bank statement)\n• Business registration documents (if applicable)", 0, 'L', false);

        $pdf->Ln(5);

        // Submission Section
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(59, 130, 246);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, 'SUBMISSION', 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Ln(2);

        $pdf->Cell(40, 8, 'Reference Number:', 0, 0);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 8, $referenceNumber, 0, 1);
        $pdf->SetFont('helvetica', '', 10);

        $pdf->Cell(40, 8, 'Submission Date:', 0, 0);
        $pdf->Cell(0, 8, '_________________', 0, 1);

        $pdf->Cell(40, 8, 'Staff Signature:', 0, 0);
        $pdf->Cell(0, 8, '_________________', 0, 1);

        return $pdf;
    }

    /**
     * Process offline form submission
     */
    private function processOfflineForm($referenceNumber, $filename, $filepath, $fileSize, $fileType)
    {
        try {
            // Store submission record in database
            $submissionModel = new \App\Models\OfflineFormSubmissionModel();

            $submissionData = [
                'reference_number' => $referenceNumber,
                'filename' => $filename,
                'filepath' => $filepath,
                'file_size' => $fileSize,
                'file_type' => $fileType,
                'submitted_at' => date('Y-m-d H:i:s'),
                'status' => 'pending_review'
            ];

            $result = $submissionModel->createSubmission($submissionData);

            if ($result['success']) {
                // Send email notification to admin (optional)
                $this->sendAdminNotification($referenceNumber, $filename);

                return ['success' => true, 'message' => 'Form submitted successfully.'];
            } else {
                // If database save failed, delete the uploaded file
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
                return ['success' => false, 'message' => 'Failed to save submission record.'];
            }

        } catch (\Exception $e) {
            // Clean up uploaded file if error occurs
            if (isset($filepath) && file_exists($filepath)) {
                unlink($filepath);
            }

            return ['success' => false, 'message' => 'Error processing form: ' . $e->getMessage()];
        }
    }

    /**
     * Send notification to admin about new offline submission
     */
    private function sendAdminNotification($referenceNumber, $filename)
    {
        try {
            // Get admin email from settings or use default
            $adminEmail = $_ENV['ADMIN_EMAIL'] ?? 'admin@gafconl.com';

            $subject = 'New Offline Form Submission - ' . $referenceNumber;
            $message = "
                A new offline registration form has been submitted.
                
                Reference Number: {$referenceNumber}
                Filename: {$filename}
                Submitted: " . date('F j, Y \a\t g:i A') . "
                
                Please review the submission in the admin panel.
                
                Best regards,
                GAFCONL System
            ";

            $headers = 'From: noreply@gafconl.com' . "\r\n" .
                'Reply-To: noreply@gafconl.com' . "\r\n" .
                'X-Mailer: PHP/' . phpversion();

            // Send email (you can enhance this with a proper email library)
            mail($adminEmail, $subject, $message, $headers);

        } catch (\Exception $e) {
            // Log error but don't fail the submission
            error_log('Failed to send admin notification: ' . $e->getMessage());
        }
    }

    public function verifyEmail()
    {
        $success = $error = '';
        $code = $_GET['code'] ?? '';
        if ($code) {
            $model = new MemberModel();
            if ($model->verifyEmail($code)) {
                $success = 'Email verified successfully! You can now log in.';
            } else {
                $error = 'Invalid or expired verification link.';
            }
        } else {
            $error = 'No verification code provided.';
        }
        $this->render('registration/verify_email', ['error' => $error, 'success' => $success]);
    }

    public function requestReset()
    {
        $error = $success = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            if (!$email) {
                $error = 'Email is required.';
            } else {
                $model = new MemberModel();
                if ($model->sendPasswordReset($email)) {
                    $success = 'Password reset link sent! Check your email.';
                } else {
                    $error = 'Email not found or failed to send reset link.';
                }
            }
        }
        $this->render('registration/request_reset', ['error' => $error, 'success' => $success]);
    }

    public function resetPassword()
    {
        $error = $success = '';
        $token = $_GET['token'] ?? '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['token'] ?? '';
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';
            if (!$password || !$password_confirm) {
                $error = 'All fields are required.';
            } elseif ($password !== $password_confirm) {
                $error = 'Passwords do not match.';
            } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[!@#$%^&*]/', $password)) {
                $error = 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.';
            } else {
                $model = new MemberModel();
                if ($model->resetPassword($token, $password)) {
                    $success = 'Password reset successful! You can now log in.';
                } else {
                    $error = 'Invalid or expired reset link.';
                }
            }
        }
        $this->render('registration/reset_password', ['error' => $error, 'success' => $success, 'token' => $token]);
    }
}