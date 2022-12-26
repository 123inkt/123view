<?php
declare(strict_types=1);

$sourceDir = dirname(__DIR__) . '/';

writeln();
writeln('This script will generate a .env.prod.local with freshly generated password, and will ask additional question for other settings.');
writeln('This script will setup a production ready 123view application.');

if (readline("Are you sure you want to continue? [Y/n] ") === "n") {
    return;
}

// check if file already exists
if (file_exists($sourceDir . '.env.prod.local') &&
    readline(".env.prod.local already exists. Are you sure you want to overwrite all settings? [y/N] ") !== "y") {
    return;
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

$senderEmail = readline("What sender e-mail to use for outbound mails? Ex: 'Sherlock Holmes <sherlock@example.com>' ");
if ($senderEmail === false) {
    return;
}
replaceInFile('MAILER_SENDER', $senderEmail, $toFile);

$errorEmail = readline("What e-mail to use for error mails? Ex: 'error@example.com' ");
if ($errorEmail === false) {
    return;
}
replaceInFile('ERROR_MAIL', $errorEmail, $toFile);

$hostname = readline("What hostname will be used for the application? Ex: '123view.example.com' ");
if ($hostname === false) {
    return;
}
replaceInFile('APP_HOSTNAME', $hostname, $toFile);

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
