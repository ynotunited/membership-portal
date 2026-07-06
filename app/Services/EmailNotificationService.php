<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailNotificationService
{
    private $mailer;
    private $fromEmail;
    private $fromName;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->fromEmail = getenv('MAIL_FROM_ADDRESS') ?: 'noreply@247portal.com';
        $this->fromName = getenv('MAIL_FROM_NAME') ?: '24/7 Registration Portal';

        $this->configureMailer();
    }

    private function configureMailer()
    {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = getenv('SMTP_USER');
            $this->mailer->Password = getenv('SMTP_PASS');
            $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mailer->Port = getenv('SMTP_PORT') ?: 587;

            // Sender
            $this->mailer->setFrom($this->fromEmail, $this->fromName);
            $this->mailer->isHTML(true);
        } catch (Exception $e) {
            error_log("Mailer configuration error: {$e->getMessage()}");
        }
    }

    /**
     * Send registration confirmation email
     */
    public function sendRegistrationConfirmation($memberData)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($memberData['email'], $memberData['firstname'] . ' ' . $memberData['surname']);

            $this->mailer->Subject = 'Welcome to 24/7 Registration Portal!';
            $this->mailer->Body = $this->getRegistrationTemplate($memberData);
            $this->mailer->AltBody = $this->getRegistrationTextTemplate($memberData);

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Registration email error: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Send payment confirmation email
     */
    public function sendPaymentConfirmation($memberData, $paymentData)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($memberData['email'], $memberData['firstname'] . ' ' . $memberData['surname']);

            $this->mailer->Subject = 'Payment Confirmation - 24/7 Registration Portal';
            $this->mailer->Body = $this->getPaymentTemplate($memberData, $paymentData);
            $this->mailer->AltBody = $this->getPaymentTextTemplate($memberData, $paymentData);

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Payment email error: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Send dues reminder email
     */
    public function sendDuesReminder($memberData, $dueAmount, $dueDate)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($memberData['email'], $memberData['firstname'] . ' ' . $memberData['surname']);

            $this->mailer->Subject = 'Annual Dues Reminder - 24/7 Registration Portal';
            $this->mailer->Body = $this->getDuesReminderTemplate($memberData, $dueAmount, $dueDate);
            $this->mailer->AltBody = $this->getDuesReminderTextTemplate($memberData, $dueAmount, $dueDate);

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Dues reminder email error: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Send event notification email
     */
    public function sendEventNotification($memberData, $eventData)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($memberData['email'], $memberData['firstname'] . ' ' . $memberData['surname']);

            $this->mailer->Subject = 'Event Notification: ' . $eventData['title'];
            $this->mailer->Body = $this->getEventTemplate($memberData, $eventData);
            $this->mailer->AltBody = $this->getEventTextTemplate($memberData, $eventData);

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Event notification email error: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Send bulk email to multiple members
     */
    public function sendBulkEmail($recipients, $subject, $message)
    {
        $successCount = 0;
        $failCount = 0;

        foreach ($recipients as $recipient) {
            try {
                $this->mailer->clearAddresses();
                $this->mailer->addAddress($recipient['email'], $recipient['name']);

                $this->mailer->Subject = $subject;
                $this->mailer->Body = $this->getBulkEmailTemplate($recipient, $message);
                $this->mailer->AltBody = strip_tags($message);

                if ($this->mailer->send()) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            } catch (Exception $e) {
                error_log("Bulk email error for {$recipient['email']}: {$e->getMessage()}");
                $failCount++;
            }
        }

        return [
            'success' => $successCount,
            'failed' => $failCount,
            'total' => count($recipients)
        ];
    }

    // ============================================
    // EMAIL TEMPLATES
    // ============================================

    private function getRegistrationTemplate($data)
    {
        $appUrl = \App\Helpers\Url::appUrl();
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #408100 0%, #BB1F1F 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
        .button { display: inline-block; background: #408100; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; margin: 20px 0; }
        .info-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #408100; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to 24/7 Registration Portal!</h1>
        </div>
        <div class="content">
            <p>Dear {$data['firstname']} {$data['surname']},</p>
            
            <p>Congratulations! Your registration has been successfully completed.</p>
            
            <div class="info-box">
                <h3>Your Membership Details:</h3>
                <p><strong>Membership Number:</strong> {$data['membership_number']}</p>
                <p><strong>Email:</strong> {$data['email']}</p>
                <p><strong>Chapter:</strong> {$data['chapter']}</p>
                <p><strong>Zone:</strong> {$data['zone']}</p>
            </p>
            </div>
            
            <p>You can now log in to your dashboard to access all features:</p>
            
            <center>
                <a href="{$appUrl}/login" class="button">Login to Dashboard</a>
            </center>
            
            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>Complete your profile information</li>
                <li>Upload required documents</li>
                <li>Make your first payment</li>
                <li>Explore available features</li>
            </ul>
            
            <p>If you have any questions, please don't hesitate to contact our support team.</p>
            
            <p>Best regards,<br>
            <strong>24/7 Registration Portal Team</strong></p>
        </div>
        <div class="footer">
            <p>&copy; 2025 24/7 Registration Portal. All rights reserved.</p>
            <p>This is an automated email. Please do not reply to this message.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getRegistrationTextTemplate($data)
    {
        $appUrl = \App\Helpers\Url::appUrl();
        return <<<TEXT
Welcome to 24/7 Registration Portal!

Dear {$data['firstname']} {$data['surname']},

Congratulations! Your registration has been successfully completed.

Your Membership Details:
- Membership Number: {$data['membership_number']}
- Email: {$data['email']}
- Chapter: {$data['chapter']}
- Zone: {$data['zone']}

You can now log in to your dashboard: {$appUrl}/login

Next Steps:
- Complete your profile information
- Upload required documents
- Make your first payment
- Explore available features

Best regards,
24/7 Registration Portal Team

© 2025 24/7 Registration Portal. All rights reserved.
TEXT;
    }

    private function getPaymentTemplate($memberData, $paymentData)
    {
        $appUrl = \App\Helpers\Url::appUrl();
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #28a745 0%, #218838 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
        .receipt { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .receipt-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .total { font-size: 20px; font-weight: bold; color: #28a745; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Payment Confirmed</h1>
        </div>
        <div class="content">
            <p>Dear {$memberData['firstname']} {$memberData['surname']},</p>
            
            <p>Your payment has been successfully processed!</p>
            
            <div class="receipt">
                <h3>Payment Receipt</h3>
                <div class="receipt-row">
                    <span>Transaction ID:</span>
                    <strong>{$paymentData['transaction_id']}</strong>
                </div>
                <div class="receipt-row">
                    <span>Amount Paid:</span>
                    <strong>₦{$paymentData['amount']}</strong>
                </div>
                <div class="receipt-row">
                    <span>Payment Type:</span>
                    <strong>{$paymentData['type']}</strong>
                </div>
                <div class="receipt-row">
                    <span>Date:</span>
                    <strong>{$paymentData['date']}</strong>
                </div>
                <div class="receipt-row total">
                    <span>Status:</span>
                    <span style="color: #28a745;">PAID</span>
                </div>
            </div>
            
            <p>Thank you for your payment. Your account has been updated accordingly.</p>
            
            <p>Best regards,<br>
            <strong>24/7 Registration Portal Team</strong></p>
        </div>
        <div class="footer">
            <p>&copy; 2025 24/7 Registration Portal. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getPaymentTextTemplate($memberData, $paymentData)
    {
        return <<<TEXT
Payment Confirmed

Dear {$memberData['firstname']} {$memberData['surname']},

Your payment has been successfully processed!

Payment Receipt:
- Transaction ID: {$paymentData['transaction_id']}
- Amount Paid: ₦{$paymentData['amount']}
- Payment Type: {$paymentData['type']}
- Date: {$paymentData['date']}
- Status: PAID

Thank you for your payment.

Best regards,
24/7 Registration Portal Team
TEXT;
    }

    private function getDuesReminderTemplate($memberData, $dueAmount, $dueDate)
    {
        $appUrl = \App\Helpers\Url::appUrl();
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
        .alert-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; border-radius: 8px; margin: 20px 0; }
        .button { display: inline-block; background: #ffc107; color: #333; padding: 12px 30px; text-decoration: none; border-radius: 8px; margin: 20px 0; font-weight: bold; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⏰ Annual Dues Reminder</h1>
        </div>
        <div class="content">
            <p>Dear {$memberData['firstname']} {$memberData['surname']},</p>
            
            <div class="alert-box">
                <h3>Payment Reminder</h3>
                <p><strong>Amount Due:</strong> ₦{$dueAmount}</p>
                <p><strong>Due Date:</strong> {$dueDate}</p>
                <p><strong>Membership Number:</strong> {$memberData['membership_number']}</p>
            </div>
            
            <p>Your annual dues payment is due soon. Please make your payment to continue enjoying all membership benefits.</p>
            
            <center>
                <a href="{$appUrl}/member/dues" class="button">Pay Now</a>
            </center>
            
            <p>If you've already made this payment, please disregard this reminder.</p>
            
            <p>Best regards,<br>
            <strong>24/7 Registration Portal Team</strong></p>
        </div>
        <div class="footer">
            <p>&copy; 2025 24/7 Registration Portal. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getDuesReminderTextTemplate($memberData, $dueAmount, $dueDate)
    {
        $appUrl = \App\Helpers\Url::appUrl();
        return <<<TEXT
Annual Dues Reminder

Dear {$memberData['firstname']} {$memberData['surname']},

Payment Reminder:
- Amount Due: ₦{$dueAmount}
- Due Date: {$dueDate}
- Membership Number: {$memberData['membership_number']}

Your annual dues payment is due soon. Please make your payment to continue enjoying all membership benefits.

Pay now: {$appUrl}/member/dues

If you've already made this payment, please disregard this reminder.

Best regards,
24/7 Registration Portal Team
TEXT;
    }

    private function getEventTemplate($memberData, $eventData)
    {
        $appUrl = \App\Helpers\Url::appUrl();
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
        .event-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #007bff; }
        .button { display: inline-block; background: #007bff; color: white; padding: 12px 30px; text-decoration: none; border-radius: 8px; margin: 20px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📅 Event Notification</h1>
        </div>
        <div class="content">
            <p>Dear {$memberData['firstname']} {$memberData['surname']},</p>
            
            <div class="event-box">
                <h2>{$eventData['title']}</h2>
                <p><strong>Date:</strong> {$eventData['date']}</p>
                <p><strong>Time:</strong> {$eventData['time']}</p>
                <p><strong>Location:</strong> {$eventData['location']}</p>
                <p><strong>Description:</strong></p>
                <p>{$eventData['description']}</p>
            </div>
            
            <center>
                <a href="{$appUrl}/member/events" class="button">View Event Details</a>
            </center>
            
            <p>We look forward to seeing you there!</p>
            
            <p>Best regards,<br>
            <strong>24/7 Registration Portal Team</strong></p>
        </div>
        <div class="footer">
            <p>&copy; 2025 24/7 Registration Portal. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getEventTextTemplate($memberData, $eventData)
    {
        $appUrl = \App\Helpers\Url::appUrl();
        return <<<TEXT
Event Notification

Dear {$memberData['firstname']} {$memberData['surname']},

{$eventData['title']}

Date: {$eventData['date']}
Time: {$eventData['time']}
Location: {$eventData['location']}

Description:
{$eventData['description']}

View event details: {$appUrl}/member/events

We look forward to seeing you there!

Best regards,
24/7 Registration Portal Team
TEXT;
    }

    private function getBulkEmailTemplate($recipient, $message)
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #408100 0%, #BB1F1F 100%); color: white; padding: 30px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>24/7 Registration Portal</h1>
        </div>
        <div class="content">
            <p>Dear {$recipient['name']},</p>
            
            {$message}
            
            <p>Best regards,<br>
            <strong>24/7 Registration Portal Team</strong></p>
        </div>
        <div class="footer">
            <p>&copy; 2025 24/7 Registration Portal. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
