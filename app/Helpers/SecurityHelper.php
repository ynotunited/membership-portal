<?php

namespace App\Helpers;

use App\Helpers\SecurityLogger;

class SecurityHelper
{
    /**
     * Sanitize string input to prevent XSS / script injection.
     */
    public static function sanitizeString(string $input): string
    {
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);
        // Convert special characters to HTML entities
        return htmlspecialchars(trim($input), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Recursively sanitize input array.
     */
    public static function sanitizeArray(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $cleanKey = self::sanitizeString((string)$key);
            if (is_array($value)) {
                $sanitized[$cleanKey] = self::sanitizeArray($value);
            } else {
                $sanitized[$cleanKey] = self::sanitizeString((string)$value);
            }
        }
        return $sanitized;
    }

    /**
     * Validate an email address.
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate a phone number (7-15 digits, optional leading plus sign).
     */
    public static function validatePhone(string $phone): bool
    {
        return preg_match('/^\+?[0-9]{7,15}$/', trim($phone)) === 1;
    }

    /**
     * Validate positive numeric value (amount / shares).
     */
    public static function validateNumeric($value, bool $allowFloat = false): bool
    {
        if ($allowFloat) {
            return is_numeric($value) && floatval($value) >= 0;
        }
        return ctype_digit(strval($value)) && intval($value) >= 0;
    }

    /**
     * Validate a date string (YYYY-MM-DD format).
     */
    public static function validateDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Validate alphanumeric characters with spaces.
     */
    public static function validateAlphanumeric(string $value): bool
    {
        return preg_match('/^[a-zA-Z0-9\s\-_\.]+$/', $value) === 1;
    }

    /**
     * Handle and strictly validate file uploads.
     * Returns the generated safe filename on success, or an array with ['error' => 'reason'] on failure.
     */
    public static function handleSecureUpload(
        ?array $file,
        array $allowedExtensions,
        array $allowedMimeTypes,
        int $maxSize,
        string $destinationDir,
        string $prefix = 'file_'
    ) {
        if (!$file || !isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['error' => 'No file uploaded or file object is invalid.'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['error' => 'File upload error code: ' . $file['error']];
        }

        // Validate size
        if ($file['size'] > $maxSize) {
            SecurityLogger::event('WARNING', 'security.upload_too_large', [
                'name' => $file['name'],
                'size' => $file['size'],
                'max_size' => $maxSize
            ]);
            return ['error' => 'File size exceeds maximum limit of ' . ($maxSize / 1024 / 1024) . ' MB.'];
        }

        // Validate extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) {
            SecurityLogger::event('ALERT', 'security.invalid_extension', [
                'name' => $file['name'],
                'extension' => $ext
            ]);
            return ['error' => 'Invalid file extension. Allowed: ' . implode(', ', $allowedExtensions)];
        }

        // Validate MIME type
        if (!file_exists($file['tmp_name'])) {
            return ['error' => 'Uploaded temporary file does not exist.'];
        }
        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowedMimeTypes)) {
            SecurityLogger::event('ALERT', 'security.invalid_mime', [
                'name' => $file['name'],
                'mime' => $mime
            ]);
            return ['error' => 'Invalid file content type. Allowed: ' . implode(', ', $allowedMimeTypes)];
        }

        // Create directory if not exists
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        // Generate safe unique filename
        $safeName = $prefix . bin2hex(random_bytes(16)) . '.' . $ext;
        $targetPath = rtrim($destinationDir, '/\\') . '/' . $safeName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $safeName;
        }

        return ['error' => 'Failed to move uploaded file.'];
    }
}
