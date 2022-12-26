<?php
declare(strict_types=1);

$sourceDir = dirname(__DIR__) . '/';
$sslMode   = getopt('', 'ssl:')['ssl'] ?? 'self-signed';

writeln();
writeln('This script will generate a .env.prod.local with freshly generated passwords, and will ask questions for additional settings.');
writeln('This script will setup a production ready 123view application.');
writeln();

if (readline("Are you sure you want to continue? [Y/n] ") === "n") {
    return;
}

// check if file already exists
if (file_exists($sourceDir . '.env.prod.local')) {
    writeln("env.prod.local already exists.");
    if (readline("Are you sure you want to overwrite? [y/N] ") !== "y") {
        return;
    }
}

$fromFile = $sourceDir . '.env.prod.local.dist';
$toFile   = $sourceDir . '.env.prod.local';

writeln("Copy $fromFile > $toFile");
copy($fromFile, $toFile);

writeln("Generate secrets for APP_SECRET, DB_PASS, RABBITMQ_PASSWORD, MERCURE_JWT_SECRET");
replaceInFile("APP_SECRET", random_str(32), $toFile);
replaceInFile("DB_PASS", random_str(32), $toFile);
replaceInFile("RABBITMQ_PASSWORD", random_str(32), $toFile);
replaceInFile("MERCURE_JWT_SECRET", random_str(32), $toFile);
writeln("Secrets written to `$toFile`");
writeln();

writeln("What sender e-mail to use for outbound mails? Ex: 'Sherlock Holmes <sherlock@example.com>' ");
$senderEmail = readline();
if ($senderEmail === false) {
    return;
}
replaceInFile('MAILER_SENDER', $senderEmail, $toFile);

writeln("What e-mail to use for error mails? Ex: 'error@example.com'");
$errorEmail = readline();
if ($errorEmail === false) {
    return;
}
replaceInFile('ERROR_MAIL', $errorEmail, $toFile);

writeln("What hostname will be used for the application? Ex: '123view.example.com'");
$hostname = readline();
if ($hostname === false) {
    return;
}
replaceInFile('APP_HOSTNAME', $hostname, $toFile);

if ($sslMode === 'self-signed') {
    replaceInFile('SSL_DHPARAM', $sourceDir . 'docker/ssl/dhparam.pem', $toFile);
    replaceInFile('SSL_CERTIFICATE', $sourceDir . 'docker/ssl/development/development-self-signed.crt', $toFile);
    replaceInFile('SSL_CERTIFICATE_KEY', $sourceDir . 'docker/ssl/development/development-self-signed.key', $toFile);
} else {
    writeln("SSL: What's the path the the dhparam.pem: ");
    $dhparam = readline();
    if ($dhparam === false) {
        return;
    }
    replaceInFile('SSL_DHPARAM', $dhparam, $toFile);

    writeln("SSL: What's the path to your ssl certificate (.crt): ");
    $cert = readline();
    if ($cert === false) {
        return;
    }
    replaceInFile('SSL_CERTIFICATE', $cert, $toFile);

    writeln("SSL: What's the path to your ssl certificate key (.key): ");
    $certKey = readline();
    if ($certKey === false) {
        return;
    }
    replaceInFile('SSL_CERTIFICATE_KEY', $certKey, $toFile);
}

/** ********************************************************************************************************************************************** **/
/**                                            Utility methods                                                                                     **/
/** ********************************************************************************************************************************************** **/

function random_str(int $length): string
{
    return substr(str_replace('=', '', base64_encode(random_bytes(200))), 0, $length);
}

function writeln(string $line = ''): void
{
    echo $line . "\n";
}

function replaceInFile(string $key, string $value, string $file): void
{
    $contents = file_get_contents($file);
    if ($contents === false) {
        throw new RuntimeException('Failed to open file: ' . $file);
    }

    $contents = str_replace($key . "=", "$key='$value'", $contents);

    $result = file_put_contents($file, $contents);
    if ($result === false) {
        throw new RuntimeException('Failed to write file: ' . $file);
    }
}
