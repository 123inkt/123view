<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeHighlight;

use DR\GitCommitNotification\Service\CodeHighlight\FilenameToLanguageTranslator;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\CodeHighlight\FilenameToLanguageTranslator
 */
class FilenameToLanguageTranslatorTest extends AbstractTestCase
{
    /**
     * @dataProvider dataProvider
     * @covers ::translate
     */
    public function testTranslate(string $filename, ?string $expectedLanguage): void
    {
        $translator     = new FilenameToLanguageTranslator();
        $actualLanguage = $translator->translate($filename);
        static::assertSame($expectedLanguage, $actualLanguage);
    }

    /**
     * @return array<string, array<string|null>>
     */
    public function dataProvider(): array
    {
        return [
            "dockerfile"  => ["Dockerfile", "dockerfile"],
            "css"         => ["file.css", "css"],
            "env"         => ["file.env", "ini"],
            "ini"         => ["file.ini", "ini"],
            "js"          => ["file.js", "javascript"],
            "json"        => ["file.json", "json"],
            "json5"       => ["file.json5", "json"],
            "htaccess"    => [".htaccess", "apache"],
            "less"        => ["file.less", "less"],
            "md"          => ["file.md", "markdown"],
            "php"         => ["file.php", "php"],
            "py"          => ["file.py", "python"],
            "scss"        => ["file.scss", "scss"],
            "sass"        => ["file.sass", "scss"],
            "sh"          => ["file.sh", "bash"],
            "sql"         => ["file.sql", "sql"],
            "ts"          => ["file.ts", "typescript"],
            "twig"        => ["file.twig", "twig"],
            "html"        => ["file.html", "xml"],
            "xml"         => ["file.xml", "xml"],
            "xml.dist"    => ["file.xml.dist", "xml"],
            "xml.local"   => ["file.xml.local", "xml"],
            "xml.example" => ["file.xml.example", "xml"],
            "yaml"        => ["file.yaml", "yaml"],
            "yml"         => ["file.yml", "yaml"],
            "foobar"      => ["file.foobar", null]
        ];
    }
}
