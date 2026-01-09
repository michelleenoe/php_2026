<?php
/**
 * Lightweight .env loader (no dependencies).
 * Parses KEY=VALUE lines, ignores comments/blank lines.
 * Only sets vars that are not already present in the environment.
 */
if (!function_exists('loadEnvFile')) {
    function loadEnvFile(string $path): void
    {
        if (!file_exists($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $trimmed = ltrim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }
            if (!str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);
            $name  = trim($name);
            $value = trim($value);
            $value = trim($value, "\"'");

            if ($name === '' || getenv($name) !== false) {
                continue;
            }

            putenv("$name=$value");
            $_ENV[$name]    = $value;
            $_SERVER[$name] = $value;
        }
    }
}
