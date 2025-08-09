<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeHighlight;

class FilenameToLanguageTranslator
{
    public function translate(string $filename): ?string
    {
        if (strcasecmp(basename($filename), 'Dockerfile') === 0) {
            return 'dockerfile';
        }

        // remove .example, .dist and .local extensions
        $filename = (string)preg_replace('/\.(example|dist|local)$/', '', $filename);

        return match (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            "css"                => "css",
            "env", "ini"         => "ini",
            "js"                 => "javascript",
            "json", "json5"      => "json",
            "htaccess"           => "apache",
            "less"               => "less",
            "md"                 => "markdown",
            "php"                => "php",
            "py"                 => "python",
            "scss", "sass"       => "scss",
            "sh"                 => "bash",
            "sql"                => "sql",
            "ts"                 => "typescript",
            "twig"               => "twig",
            "xml", "html", "xlf" => "xml",
            "yaml", "yml"        => "yaml",
            default              => null,
        };
    }
}
