<?php
namespace App\Helpers;

class Url
{
    public static function base($withHost = false)
    {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
        $base = rtrim(dirname($scriptName), '/');
        if ($base === '/') $base = '';
        if ($withHost) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            return $protocol . '://' . $host . $base;
        }
        return $base;
    }

    public static function appUrl()
    {
        if (isset($_ENV['APP_URL'])) {
            return rtrim($_ENV['APP_URL'], '/');
        }
        
        // Fallback: construct URL from current request
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
        $base = rtrim(dirname($scriptName), '/');
        if ($base === '/') $base = '';
        
        return $protocol . '://' . $host . $base;
    }
} 