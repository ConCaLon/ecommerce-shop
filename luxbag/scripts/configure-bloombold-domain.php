<?php

// Run from cPanel Terminal after pulling the application code.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

if (count($argv) > 2 || (isset($argv[1]) && $argv[1] !== '--dry-run')) {
    fwrite(STDERR, "Usage: php scripts/configure-bloombold-domain.php [--dry-run]\n");
    exit(1);
}

$envPath = dirname(__DIR__).'/.env';
if (! is_file($envPath) || ! is_readable($envPath)) {
    fwrite(STDERR, "The application .env file is missing or unreadable.\n");
    exit(1);
}

$original = file_get_contents($envPath);
if ($original === false) {
    fwrite(STDERR, "Unable to read .env.\n");
    exit(1);
}

$settings = [
    'APP_URL' => 'https://bloombold.shop',
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'SESSION_DOMAIN' => 'null',
    'SESSION_SECURE_COOKIE' => 'true',
    'AUTH_VERIFICATION_EXPIRE' => '60',
];

$newline = str_contains($original, "\r\n") ? "\r\n" : "\n";
$updated = $original;
foreach ($settings as $key => $value) {
    $pattern = '/^[\t ]*(?:export[\t ]+)?'.preg_quote($key, '/').'[\t ]*=.*$/m';
    $replacement = $key.'='.$value;
    if (preg_match($pattern, $updated)) {
        $updated = preg_replace_callback($pattern, static function (array $match) use ($replacement): string {
            return $replacement.(str_ends_with($match[0], "\r") ? "\r" : '');
        }, $updated);
    } else {
        if ($updated !== '' && ! str_ends_with($updated, "\n")) {
            $updated .= $newline;
        }
        $updated .= $replacement.$newline;
    }
}

foreach ($settings as $key => $value) {
    echo $key.'='.$value.PHP_EOL;
}

if (in_array('--dry-run', $argv, true)) {
    echo "Dry run: .env has not been changed. Database, APP_KEY and mail credentials will be preserved.\n";
    exit;
}

if ($updated === $original) {
    echo "The production domain settings are already configured.\n";
    exit;
}

// Create the temporary file in the same directory so the final rename is atomic.
$temporaryPath = tempnam(dirname($envPath), '.env-update-');
if ($temporaryPath === false) {
    fwrite(STDERR, "Unable to create a temporary configuration file.\n");
    exit(1);
}

$permissions = fileperms($envPath);
if ($permissions !== false && ! chmod($temporaryPath, $permissions & 0777)) {
    unlink($temporaryPath);
    fwrite(STDERR, "Unable to preserve .env permissions. Configuration was not changed.\n");
    exit(1);
}

if (file_put_contents($temporaryPath, $updated, LOCK_EX) !== strlen($updated)) {
    unlink($temporaryPath);
    fwrite(STDERR, "Unable to write the new configuration. Original .env was not changed.\n");
    exit(1);
}

if (! rename($temporaryPath, $envPath)) {
    unlink($temporaryPath);
    fwrite(STDERR, "Unable to replace .env. Original configuration was not changed.\n");
    exit(1);
}

echo "Production domain configured. Database, APP_KEY and mail credentials were preserved.\n";
echo "Run: php artisan config:clear\n";
echo "Run: php artisan route:clear\n";
echo "Run: php artisan view:clear\n";
echo "Run: php artisan config:cache\n";
