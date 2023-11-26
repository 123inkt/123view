<?php
declare(strict_types=1);

$url   = 'https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types';
$lines = explode("\n", file_get_contents($url));
$mimes = ['md' => 'text/markdown'];

foreach ($lines as $line) {
    // skipped commented out
    if (str_starts_with($line, '#') || preg_match('/^(\S+)\s+(.+)$/', $line, $matches) !== 1) {
        continue;
    }

    // add to list
    foreach (explode(' ', $matches[2]) as $extension) {
        $mimes[strtolower($extension)] = strtolower($matches[1]);
    }
}

ksort($mimes, SORT_NATURAL);

$output = "<?php\n\n";
$output .= "declare(strict_types=1);\n\n";
$output .= "// parsed from: https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types\n";
$output .= "return " . var_export($mimes, true) . ";\n";

file_put_contents(__DIR__ . '/mime-types.php', $output);
