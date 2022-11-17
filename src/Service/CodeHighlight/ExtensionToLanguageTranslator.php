<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeHighlight;

class ExtensionToLanguageTranslator
{
    public function translate(string $extension): ?string
    {
        return match (strtolower($extension)) {
            "css"           => "css",
            "env"           => "ini",
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
