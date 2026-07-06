<?php

namespace App\Config;

class Version
{
    const VERSION = '1.9.5';
    const RELEASE_DATE = '2025-10-22';
    const CODE_NAME = 'Phoenix';
    
    public static function get(): string
    {
        return self::VERSION;
    }
    
    public static function getFull(): string
    {
        return 'v' . self::VERSION . ' (' . self::CODE_NAME . ')';
    }
    
    public static function getReleaseDate(): string
    {
        return self::RELEASE_DATE;
    }
}
