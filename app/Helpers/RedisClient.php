<?php

namespace App\Helpers;

/**
 * A lightweight Redis client using raw TCP sockets.
 * Used for distributed rate limiting when ext-redis and Predis are unavailable.
 */
class RedisClient
{
    private $socket;

    public function __construct(string $host = '127.0.0.1', int $port = 6379, float $timeout = 2.0)
    {
        $this->socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$this->socket) {
            throw new \RuntimeException("Could not connect to Redis at $host:$port - $errstr");
        }
        stream_set_timeout($this->socket, (int)$timeout);
    }

    public function __destruct()
    {
        if ($this->socket) {
            fclose($this->socket);
        }
    }

    public function executeCommand(array $args)
    {
        $command = '*' . count($args) . "\r\n";
        foreach ($args as $arg) {
            $command .= '$' . strlen($arg) . "\r\n" . $arg . "\r\n";
        }
        
        fwrite($this->socket, $command);
        return $this->readResponse();
    }

    private function readResponse()
    {
        $reply = fgets($this->socket);
        if ($reply === false) {
            return false;
        }

        $reply = trim($reply);
        $type = $reply[0];
        $value = substr($reply, 1);

        switch ($type) {
            case '+': // Simple String
                return $value;
            case '-': // Error
                throw new \RuntimeException("Redis error: $value");
            case ':': // Integer
                return (int)$value;
            case '$': // Bulk String
                if ($value === '-1') return null;
                $len = (int)$value;
                $data = stream_get_contents($this->socket, $len);
                fgets($this->socket); // consume \r\n
                return $data;
            case '*': // Array
                if ($value === '-1') return null;
                $count = (int)$value;
                $data = [];
                for ($i = 0; $i < $count; $i++) {
                    $data[] = $this->readResponse();
                }
                return $data;
            default:
                throw new \RuntimeException("Unknown Redis reply type: $type");
        }
    }

    // Convenience methods
    public function zremrangebyscore(string $key, $min, $max) {
        return $this->executeCommand(['ZREMRANGEBYSCORE', $key, (string)$min, (string)$max]);
    }

    public function zcard(string $key) {
        return $this->executeCommand(['ZCARD', $key]);
    }

    public function zadd(string $key, $score, $member) {
        return $this->executeCommand(['ZADD', $key, (string)$score, (string)$member]);
    }

    public function expire(string $key, int $seconds) {
        return $this->executeCommand(['EXPIRE', $key, (string)$seconds]);
    }

    public function del(string $key) {
        return $this->executeCommand(['DEL', $key]);
    }
}
