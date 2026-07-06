<?php

namespace App\Helpers;

/**
 * Global Error and Exception Handler.
 * Captures all issues, formats them, and sends them to Sentry or local logs.
 */
class ErrorHandler
{
    public static function register()
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleFatalError']);
    }

    public static function handleError($level, $message, $file = '', $line = 0)
    {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    public static function handleException(\Throwable $e)
    {
        self::sendToSentry($e);
        
        $msg = "Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine();
        error_log($msg);

        if (!headers_sent()) {
            http_response_code(500);
            echo "An unexpected error occurred. Our team has been notified.";
        }
    }

    public static function handleFatalError()
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            $e = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            self::handleException($e);
        }
    }

    /**
     * A lightweight Sentry client using raw cURL to avoid Composer dependencies
     */
    private static function sendToSentry(\Throwable $e)
    {
        $dsn = $_ENV['SENTRY_DSN'] ?? null;
        if (!$dsn) {
            return;
        }

        // Parse DSN
        // e.g. https://public_key@o12345.ingest.sentry.io/67890
        if (!preg_match('#^(https?://)([^@]+)@([^/]+)/(\d+)$#', $dsn, $matches)) {
            return;
        }

        $scheme = $matches[1];
        $publicKey = $matches[2];
        $host = $matches[3];
        $projectId = $matches[4];

        $url = "{$scheme}{$host}/api/{$projectId}/store/";

        $event = [
            'event_id' => bin2hex(random_bytes(16)),
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'platform' => 'php',
            'level' => 'error',
            'server_name' => $_SERVER['SERVER_NAME'] ?? 'localhost',
            'exception' => [
                'values' => [
                    [
                        'type' => get_class($e),
                        'value' => $e->getMessage(),
                        'stacktrace' => [
                            'frames' => array_reverse(array_map(function($frame) {
                                return [
                                    'filename' => $frame['file'] ?? '',
                                    'lineno' => $frame['line'] ?? 0,
                                    'function' => $frame['function'] ?? '',
                                ];
                            }, $e->getTrace()))
                        ]
                    ]
                ]
            ],
            'request' => [
                'url' => $_SERVER['REQUEST_URI'] ?? '',
                'method' => $_SERVER['REQUEST_METHOD'] ?? '',
                'env' => ['REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? '']
            ]
        ];

        $header = [
            'Content-Type: application/json',
            "X-Sentry-Auth: Sentry sentry_version=7, sentry_key={$publicKey}, sentry_client=gafconl-php/1.0"
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($event));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Best effort
        curl_exec($ch);
        curl_close($ch);
    }
}
