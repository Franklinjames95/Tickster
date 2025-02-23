<?php

namespace Lib;

class EnvReader {

    private static $env = [];

    /**
     * Load and parse the .env file.
     *
     * @param string $filePath Path to the .env file.
     */
    public static function load($filePath){
        if (!file_exists($filePath)){
            throw new \Exception("The .env file was not found at {$filePath}");
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line){
            $line = trim($line);

            if(empty($line) || strpos($line, '#') === 0) continue;            

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            self::$env[$key] = self::sanitizeValue($value);
        }
    }

    /**
     * Get an environment variable by key.
     *
     * @param string $key Environment variable key.
     * @param mixed $default Default value if the key doesn't exist.
     * @return mixed The value of the environment variable or the default value.
     */
    public static function get($key, $default = null){
        return self::$env[$key] ?? $default;
    }

    /**
     * Sanitize the value to remove quotes or extra spaces.
     *
     * @param string $value The value to sanitize.
     * @return string The sanitized value.
     */
    private static function sanitizeValue($value){
        $value = trim($value);
        if((substr($value, 0, 1) === '"' && substr($value, -1) === '"') || 
            (substr($value, 0, 1) === "'" && substr($value, -1) === "'")){
            $value = substr($value, 1, -1);
        }
        return $value;
    }
}
