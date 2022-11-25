<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeHighlight;

class FilenameToLanguageTranslator
{
    public function translate(string $filename): ?string
    {
        if (strcasecmp($filename, 'Dockerfile') === 0) {
            return 'dockerfile';
        }

        $filename  = (string)preg_replace('/\.(example|dist|local)$/', '', $filename);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        return match (strtolower($extension)) {
            "css"           => "css",
            "env", "ini"    => "ini",
            "js"            => "javascript",
            "json", "json5" => "json",
            "htaccess"      => "apache",
            "less"          => "less",
            "md"            => "markdown",
            "php"           => "php",
            "py"            => "python",
            "scss", "sass"  => "scss",
            "sh"            => "bash",
            "sql"           => "sql",
            "ts"            => "typescript",
            "twig"          => "twig",
            "xml", "html"   => "xml",
            "yaml", "yml"   => "yaml",
            default         => null,
        };
    }
}
