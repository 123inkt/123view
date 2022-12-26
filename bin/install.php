<?php
declare(strict_types=1);

$sourceDir     = dirname(__DIR__) . '/';
$options       = getopt('', ['hostname:', 'sourcedir:']);
$hostname      = $options['hostname'] ?? 'unknown';
$hostSourceDir = $options['sourcedir'] ?? '';

writeln();

// check if file already exists
if (file_exists($sourceDir . '.env.prod.local')) {
    writeln("A `env.prod.local` already exists.");
    if (readline("Are you sure you want to overwrite? [y/N] ") !== "y") {
        return;
    }
}

$fromFile = $sourceDir . '.env.prod.local.dist';
$toFile   = $sourceDir . '.env.prod.local';

writeln();
writeln("Copy $fromFile > $toFile");
copy($fromFile, $toFile);

writeln("Generate secrets for APP_SECRET, DB_PASS, RABBITMQ_PASSWORD, MERCURE_JWT_SECRET");
replaceInFile("APP_SECRET", random_str(32), $toFile);
replaceInFile("DB_PASS", random_str(32), $toFile);
replaceInFile("RABBITMQ_PASSWORD", random_str(32), $toFile);
replaceInFile("MERCURE_JWT_SECRET", random_str(32), $toFile);
writeln("Secrets written to `.env.prod.local`");
writeln();

writeln("What sender e-mail to use for outbound mails? Ex: 'Sherlock Holmes <sherlock@example.com>' ");
$senderEmail = readline();
if ($senderEmail === false) {
    return;
}
replaceInFile('MAILER_SENDER', $senderEmail, $toFile);

writeln();
writeln("What e-mail to use for error mails? Ex: 'error@example.com'");
$errorEmail = readline();
if ($errorEmail === false) {
    return;
}
replaceInFile('ERROR_MAIL', $errorEmail, $toFile);

writeln();
writeln("What hostname will be used for the application? Default: '" . $hostname . "'");
$userHostname = readline();
if ($userHostname === false) {
    return;
}
replaceInFile('APP_HOSTNAME', trim($userHostname) === '' ? $hostname : $userHostname, $toFile);

# ask which ssl option should be used
writeln();
writeln("[1] Get me started with self-signed certificates, i'll replace this later");
writeln("[2] Setup with my own ssl certificates");
writeln();
writeln("Use self-signed ssl certificate or use my own? ");
$sslModeChoice = readline();

if ($sslModeChoice === "1") {
    writeln();
    replaceInFile('SSL_DHPARAM', $hostSourceDir . '/docker/ssl/dhparam.pem', $toFile);
    writeln('SSL_DHPARAM: set to ' . $hostSourceDir . '/docker/ssl/dhparam.pem');
    replaceInFile('SSL_CERTIFICATE', $hostSourceDir . '/docker/ssl/development/development-self-signed.crt', $toFile);
    writeln('SSL_CERTIFICATE: set to ' . $hostSourceDir . '/docker/ssl/development/development-self-signed.crt');
    replaceInFile('SSL_CERTIFICATE_KEY', $hostSourceDir . '/docker/ssl/development/development-self-signed.key', $toFile);
    writeln('SSL_CERTIFICATE_KEY: set to ' . $hostSourceDir . '/docker/ssl/development/development-self-signed.key');
} elseif ($sslModeChoice === "2") {
    writeln();
    writeln("SSL: What's the path the the dhparam.pem: ");
    $dhparam = readline();
    if ($dhparam === false) {
        return;
    }
    replaceInFile('SSL_DHPARAM', $dhparam, $toFile);

    writeln();
    writeln("SSL: What's the path to your ssl certificate (.crt): ");
    $cert = readline();
    if ($cert === false) {
        return;
    }
    replaceInFile('SSL_CERTIFICATE', $cert, $toFile);

    writeln();
    writeln("SSL: What's the path to your ssl certificate key (.key): ");
    $certKey = readline();
    if ($certKey === false) {
        return;
    }
    replaceInFile('SSL_CERTIFICATE_KEY', $certKey, $toFile);
} else {
    writeln("aborted");

    return;
}

writeln();
writeln(
    "Configuration complete. All settings save in `.env.prod.local`. Review if everything is correct and use `bin/start.sh` to start the application"
);

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
